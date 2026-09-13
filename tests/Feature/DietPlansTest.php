<?php

namespace Tests\Feature;

use App\Models\DietMeal;
use App\Models\DietPlan;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AiDietPlannerService;
use Tests\TestCase;

class DietPlansTest extends TestCase
{
    protected Tenant $tenant;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Diet Test Gym',
            'slug' => 'diet-test-gym',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->user = User::where('tenant_id', $this->tenant->id)->first() ?? User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Diet Admin',
            'email' => 'dietadmin@gym.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_diet_plans_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/app/diets');

        $response->assertSee('Diet');
        $response->assertSee('Create Diet Plan');
    }

    public function test_can_seed_starter_diet_templates(): void
    {
        $response = $this->actingAs($this->user)->post('/app/diets/seed-starter');

        $response->assertRedirect();
        $this->assertDatabaseHas('diet_plans', [
            'tenant_id' => $this->tenant->id,
            'is_template' => 1,
        ]);

        $pageResponse = $this->actingAs($this->user)->get('/app/diets');
        $pageResponse->assertSee('Share on WhatsApp');

        // Clean up test seeded records
        DietMeal::query()->delete();
        DietPlan::query()->delete();
    }

    public function test_can_create_custom_diet_plan_with_meals(): void
    {
        $member = Member::where('tenant_id', $this->tenant->id)->first();

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

        $response = $this->actingAs($this->user)->post('/app/diets', $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('diet_plans', [
            'tenant_id' => $this->tenant->id,
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
        $response = $this->actingAs($this->user)->get('/app/workouts');

        $response->assertStatus(200);
        $response->assertSee('Workout Routines');
    }

    public function test_ai_diet_planner_filters_out_tts_and_non_text_models(): void
    {
        $service = app(AiDietPlannerService::class);

        // Reflection to test protected getModelCandidates
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getModelCandidates');
        $method->setAccessible(true);

        // Case 1: If an accidental TTS model is requested, it must be normalized to a text model
        $candidates = $method->invoke($service, 'gemini-2.5-pro-tts');
        $this->assertNotEmpty($candidates);
        $this->assertNotContains('gemini-2.5-pro-tts', $candidates);
        $this->assertContains('gemini-2.5-flash', $candidates);

        // Case 2: Candidate list must NEVER contain any audio/tts/embed models
        foreach ($candidates as $cand) {
            $this->assertDoesNotMatchRegularExpression('/(?:tts|audio|speech|embed|aqa|imagen)/i', $cand);
        }

        // Case 3: gemini-2.5-flash requested
        $flashCandidates = $method->invoke($service, 'gemini-2.5-flash');
        $this->assertEquals('gemini-2.5-flash', $flashCandidates[0]);
    }
}
