<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberManagementAndEditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Tenant $tenant;

    protected Branch $branch;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Fitness World '.uniqid(),
            'slug' => 'fitworld-'.uniqid(),
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_main' => true,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Branch Manager',
            'email' => 'manager.'.uniqid().'@fitworld.com',
            'password' => bcrypt('password123'),
            'role' => 'gym_manager',
            'is_owner' => false,
            'status' => 'ACTIVE',
        ]);

        $this->member = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_code' => 'TEST'.rand(100000, 999999),
            'first_name' => 'Rohan',
            'last_name' => 'Verma',
            'phone' => '9988776655',
            'email' => 'rohan.'.uniqid().'@example.com',
            'gender' => 'male',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
            'notes' => 'Test member notes with special quotes & symbols',
        ]);
    }

    public function test_members_index_page_loads_safely_with_members_data(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.members.index'));
        $response->assertStatus(200);
        $response->assertSee('window.__MEMBERS_DATA__', false);
        $response->assertSee('Rohan Verma');
    }

    public function test_member_profile_page_loads_with_edit_modal(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.members.show', $this->member->id));
        $response->assertStatus(200);
        $response->assertSee('Edit Member Details');
        $response->assertSee($this->member->member_code);
    }

    public function test_updating_member_details_saves_correctly(): void
    {
        $response = $this->actingAs($this->user)->post(route('app.members.update', $this->member->id), [
            'first_name' => 'Rohan Kumar',
            'last_name' => 'Verma',
            'phone' => '9988776655',
            'alternate_phone' => '9988776600',
            'email' => 'rohan.updated@example.com',
            'gender' => 'male',
            'blood_group' => 'O+',
            'city' => 'Ahmedabad',
            'address' => '402 Sunset Heights',
            'status' => 'ACTIVE',
            'notes' => 'Updated member notes successfully',
        ]);

        $response->assertRedirect();
        $this->member->refresh();

        $this->assertEquals('Rohan Kumar', $this->member->first_name);
        $this->assertEquals('rohan.updated@example.com', $this->member->email);
        $this->assertEquals('Ahmedabad', $this->member->metadata['city']);
        $this->assertEquals('O+', $this->member->metadata['blood_group']);
    }

    public function test_direct_member_edit_url_redirects_to_show_with_edit_flag(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.members.edit', $this->member->id));
        $response->assertRedirect(route('app.members.show', ['id' => $this->member->id, 'edit' => 1]));

        $followResponse = $this->actingAs($this->user)->get(route('app.members.show', ['id' => $this->member->id, 'edit' => 1]));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('showEditModal: true', false);
    }

    public function test_non_super_admin_cannot_access_super_admin_dashboard(): void
    {
        // Gym Manager gets 403 Forbidden
        $response = $this->actingAs($this->user)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Regular Member/Staff gets 403 Forbidden
        $staffUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Receptionist Staff',
            'email' => 'reception.'.uniqid().'@fitworld.com',
            'password' => bcrypt('password123'),
            'role' => 'receptionist',
            'status' => 'ACTIVE',
        ]);
        $staffResponse = $this->actingAs($staffUser)->get(route('admin.dashboard'));
        $staffResponse->assertStatus(403);
    }

    public function test_super_admin_can_access_super_admin_dashboard(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@gymconsole.test',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Platform Administrator Controls');
    }
}
