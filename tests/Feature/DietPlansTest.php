<?php

namespace Tests\Feature;

use App\Models\DietMeal;
use App\Models\DietPlan;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class DietPlansTest extends TestCase
{
    public function test_diet_plans_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/app/diets');

        $response->assertSee('Diet');
        $response->assertSee('Create Diet Plan');
    }

    public function test_can_seed_starter_diet_templates(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->post('/app/diets/seed-starter');

        $response->assertRedirect();
        $this->assertDatabaseHas('diet_plans', [
            'tenant_id' => $tenant->id,
            'is_template' => 1,
        ]);

        $pageResponse = $this->actingAs($user)->get('/app/diets');
        $pageResponse->assertSee('Share on WhatsApp');

        // Clean up test seeded records
        DietMeal::query()->delete();
        DietPlan::query()->delete();
    }

    public function test_can_create_custom_diet_plan_with_meals(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);
        $member = Member::where('tenant_id', $tenant->id)->first();

        $postData = [
            'title' => 'Test High Protein Plan 2000',
            'member_id' => $member?->id,
            'is_template' => 0,
            'daily_calories' => 2000,
            'protein_grams' => 160,
            'carbs_grams' => 200,
            'fat_grams' => 60,
            'guidelines' => 'Drink 4 liters of water daily.',
            'meals' => [
                [
                    'meal_type' => 'breakfast',
                    'recommended_time' => '08:30',
                    'meal_name' => 'Oatmeal & Eggs',
                    'items_description' => '50g oats, 4 egg whites, 1 cup almond milk',
                    'calories' => 450,
                ],
                [
                    'meal_type' => 'lunch',
                    'recommended_time' => '13:30',
                    'meal_name' => 'Chicken Rice Bowl',
                    'items_description' => '150g grilled chicken, 150g brown rice',
                    'calories' => 600,
                ],
            ],
        ];

        $response = $this->actingAs($user)->post('/app/diets', $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('diet_plans', [
            'tenant_id' => $tenant->id,
            'title' => 'Test High Protein Plan 2000',
            'daily_calories' => 2000,
        ]);

        $this->assertDatabaseHas('diet_meals', [
            'meal_name' => 'Oatmeal & Eggs',
            'calories' => 450,
        ]);

        DietMeal::query()->delete();
        DietPlan::query()->delete();
    }

    public function test_workouts_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/app/workouts');

        $response->assertStatus(200);
        $response->assertSee('Workout Routines');
    }
}
