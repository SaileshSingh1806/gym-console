<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadTrial;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class CrmTest extends TestCase
{
    protected User $user;

    protected Tenant $tenant;

    protected ?Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Fitness CRM Test Gym',
            'slug' => 'crmtest',
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->first() ?? Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_main' => true,
        ]);

        $this->user = User::where('tenant_id', $this->tenant->id)->first() ?? User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'CRM Admin',
            'email' => 'crmadmin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_crm_dashboard_renders(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.crm.dashboard'));

        $response->assertOk();
        $response->assertSee('CRM Dashboard');
        $response->assertSee('Pipeline Overview');
        $response->assertSee('Lead Trend (30 days)');
        $response->assertSee('Lead Sources');
    }

    public function test_can_create_lead_and_filter_by_stage(): void
    {
        $response = $this->actingAs($this->user)->post(route('app.leads.store'), [
            'name' => 'Aditya Verma',
            'phone' => '9988776655',
            'email' => 'aditya@example.com',
            'source' => 'instagram',
            'stage' => 'DEMO_BOOKED',
            'remarks' => 'Interested in 1-year transformation',
            'estimated_value' => 15000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leads', [
            'name' => 'Aditya Verma',
            'phone' => '9988776655',
            'stage' => 'DEMO_BOOKED',
        ]);

        // Access leads page with stage filter
        $listResponse = $this->actingAs($this->user)->get(route('app.leads.index', ['stage' => 'DEMO_BOOKED']));
        $listResponse->assertOk();
        $listResponse->assertSee('Aditya Verma');
    }

    public function test_can_update_lead_stage(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Manish Gupta',
            'phone' => '9876501234',
            'source' => 'walk_in',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        $response = $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'PAID',
            'paid_amount' => 12500,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'stage' => 'PAID',
            'estimated_value' => 12500.00,
        ]);
    }

    public function test_can_book_and_complete_demo_trial(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Rohan Sen',
            'phone' => '9123456780',
            'source' => 'website',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        // Book Demo
        $response = $this->actingAs($this->user)->post(route('app.crm.trials.store'), [
            'lead_id' => $lead->id,
            'trial_date' => now()->addDay()->toDateString(),
            'trial_time' => '11:00 AM',
            'notes' => 'Free full gym pass test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lead_trials', [
            'lead_id' => $lead->id,
            'trial_time' => '11:00 AM',
            'status' => 'upcoming',
        ]);

        $trial = LeadTrial::where('lead_id', $lead->id)->first();

        // Mark completed
        $updateResp = $this->actingAs($this->user)->post(route('app.crm.trials.status', $trial->id), [
            'status' => 'completed',
        ]);

        $updateResp->assertRedirect();
        $this->assertDatabaseHas('lead_trials', [
            'id' => $trial->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_export_leads_csv(): void
    {
        Lead::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'CSV Prospect',
            'phone' => '9998887776',
            'source' => 'google',
            'stage' => 'CONTACTED',
            'status' => 'CONTACTED',
        ]);

        $response = $this->actingAs($this->user)->get(route('app.leads.index', ['export' => 'csv']));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_crm_subpages_render(): void
    {
        $this->actingAs($this->user)->get(route('app.crm.enquiries'))->assertOk();
        $this->actingAs($this->user)->get(route('app.crm.conversions'))->assertOk();
        $this->actingAs($this->user)->get(route('app.crm.reports'))->assertOk();
    }

    public function test_crm_reports_with_filters_and_export(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.crm.reports', [
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee('CRM Reports');
        $response->assertSee('Leads by Source');
        $response->assertSee('Leads by Stage');
        $response->assertSee('Conversion Funnel');
        $response->assertSee('Lead &amp; Conversion Trend', false);
        $response->assertSee('Leads by Branch');
        $response->assertSee('Team Performance');

        // CSV export
        $csvResponse = $this->actingAs($this->user)->get(route('app.crm.reports', ['export' => 'csv']));
        $csvResponse->assertOk();
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
