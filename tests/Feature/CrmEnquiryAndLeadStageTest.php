<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CrmEnquiryAndLeadStageTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Tenant $tenant;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'CRM Test Gym',
            'slug' => 'crmgym',
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
            'name' => 'CRM Manager',
            'email' => 'crm_manager@gym.com',
            'password' => bcrypt('password123'),
            'role' => 'gym_manager',
            'is_owner' => false,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_crm_enquiries_page_loads_and_direct_enquiry_can_be_stored(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.crm.enquiries'));
        $response->assertStatus(200);
        $response->assertSee('Add Enquiry / Walk-In');

        $storeResponse = $this->actingAs($this->user)->post(route('app.crm.enquiries.store'), [
            'name' => 'Walk In Prospect',
            'phone' => '9888877771',
            'email' => 'walkin@example.com',
            'source' => 'walk_in',
            'stage' => 'NEW_LEAD',
            'estimated_value' => 5000,
            'remarks' => 'Direct walk-in looking for personal training package',
        ]);

        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('leads', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Walk In Prospect',
            'phone' => '9888877771',
            'source' => 'walk_in',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);
    }

    public function test_lead_stage_update_syncs_status_and_metrics_across_crm(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pipeline Prospect',
            'phone' => '9888877772',
            'source' => 'instagram',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        // 1. Move to Demo Booked
        $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'DEMO_BOOKED',
        ]);
        $lead->refresh();
        $this->assertEquals('DEMO_BOOKED', $lead->stage);
        $this->assertEquals('DEMO_BOOKED', $lead->status);
        $this->assertEquals('Demo Booked', $lead->formatted_stage);

        // 2. Move to Proposal Sent
        $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'PROPOSAL_SENT',
        ]);
        $lead->refresh();
        $this->assertEquals('PROPOSAL_SENT', $lead->stage);
        $this->assertEquals('PROPOSAL_SENT', $lead->status);
        $this->assertEquals('Proposal Sent', $lead->formatted_stage);

        // 3. Move to Trial
        $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'TRIAL',
        ]);
        $lead->refresh();
        $this->assertEquals('TRIAL', $lead->stage);
        $this->assertEquals('TRIAL_SCHEDULED', $lead->status);
        $this->assertEquals('Trial', $lead->formatted_stage);

        // 4. Move to Paid / Converted with amount
        $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'PAID',
            'paid_amount' => 12000,
        ]);
        $lead->refresh();
        $this->assertEquals('PAID', $lead->stage);
        $this->assertEquals('CONVERTED', $lead->status);
        $this->assertEquals('Paid', $lead->formatted_stage);
        $this->assertEquals(12000, (float) $lead->estimated_value);
    }

    public function test_trials_dropdown_only_shows_demo_booked_leads(): void
    {
        $demoLead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Demo Ready Prospect',
            'phone' => '9888877773',
            'source' => 'facebook',
            'stage' => 'DEMO_BOOKED',
            'status' => 'DEMO_BOOKED',
        ]);

        $paidLead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Already Paid Member',
            'phone' => '9888877774',
            'source' => 'walk_in',
            'stage' => 'PAID',
            'status' => 'CONVERTED',
        ]);

        $contactedLead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Just Contacted Prospect',
            'phone' => '9888877775',
            'source' => 'website',
            'stage' => 'CONTACTED',
            'status' => 'CONTACTED',
        ]);

        $response = $this->actingAs($this->user)->get(route('app.crm.trials'));
        $response->assertStatus(200);
        $response->assertSee('Demo Ready Prospect');
        $response->assertDontSee('Already Paid Member (9888877774) - Paid');
        $response->assertDontSee('Just Contacted Prospect (9888877775) - Contacted');
    }

    public function test_marking_lead_as_paid_automatically_converts_to_member_and_records_payment(): void
    {
        $lead = Lead::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Aditi Sharma',
            'phone' => '9876543210',
            'email' => 'aditi@example.com',
            'source' => 'instagram',
            'stage' => 'TRIAL',
            'status' => 'TRIAL_SCHEDULED',
            'estimated_value' => 7500,
        ]);

        $response = $this->actingAs($this->user)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'PAID',
            'paid_amount' => 7500,
        ]);

        $response->assertRedirect();

        // 1. Verify Member was created
        $this->assertDatabaseHas('members', [
            'tenant_id' => $this->tenant->id,
            'phone' => '9876543210',
            'email' => 'aditi@example.com',
            'status' => 'ACTIVE',
        ]);

        // 2. Verify Member Payment was created with 7500
        $this->assertDatabaseHas('member_payments', [
            'tenant_id' => $this->tenant->id,
            'amount' => 7500,
        ]);
    }
}
