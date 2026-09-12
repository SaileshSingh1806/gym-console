<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class ClassesPageTest extends TestCase
{
    public function test_classes_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/app/classes');

        $response->assertStatus(200);
        $response->assertSee('Group Classes & Timetables');
        $response->assertSee('Add Class');
    }
}
