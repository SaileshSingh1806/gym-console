<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDietPlannerService
{
    /**
     * Generate a personalized, practical, Indian-friendly diet plan based on member input.
     * Uses Google Gemini AI if configured, with automatic fallback to built-in algorithmic engine.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input, ?string $apiKey = null, ?string $model = null): array
    {
        // 1. Resolve Gemini API Key & Model
        $resolvedKey = $apiKey;
        $resolvedModel = $model;

        if (empty($resolvedKey)) {
            $resolvedKey = Setting::getGlobal('gemini_api_key') ?: config('services.gemini.api_key');
            $resolvedModel = $resolvedModel ?: (Setting::getGlobal('gemini_model') ?: config('services.gemini.model', 'gemini-2.5-flash'));

            // Optional tenant custom override if configured
            $tenant = TenantContext::getTenant() ?? (auth()->check() ? auth()->user()->tenant : null);
            if (! empty($tenant?->settings['gemini_api_key'])) {
                $resolvedKey = $tenant->settings['gemini_api_key'];
                $resolvedModel = $tenant->settings['gemini_model'] ?? $resolvedModel;
            }
        }

        $resolvedModel = $resolvedModel ?: 'gemini-2.5-flash';

        // 2. If API Key is present, attempt Gemini generation
        if (! empty($resolvedKey)) {
            try {
                $geminiPlan = $this->generateWithGemini($input, $resolvedKey, $resolvedModel);
                if (! empty($geminiPlan) && ! empty($geminiPlan['meals'])) {
                    $geminiPlan['is_gemini'] = true;
                    $geminiPlan['gemini_model'] = $geminiPlan['gemini_model'] ?? $resolvedModel;

                    return $geminiPlan;
                }
            } catch (Exception $e) {
                Log::warning('Gemini AI Diet Generation failed, falling back to algorithmic engine: '.$e->getMessage(), [
                    'exception' => $e,
                    'input' => $input,
                ]);
            }
        }

        // 3. Fallback to robust procedural/algorithmic diet engine
        $plan = $this->generateAlgorithmic($input);
        $plan['is_gemini'] = false;
        $plan['gemini_model'] = null;

        return $plan;
    }

    /**
     * Query Google's ListModels endpoint to fetch actual supported text generateContent models for the API key.
     * Strictly filters out TTS, audio, speech, embedding, and other non-text generation models.
     *
     * @return array<int, string>
     */
    public function fetchAvailableModels(string $apiKey): array
    {
        $apiKey = trim($apiKey);
        if (empty($apiKey)) {
            return [];
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(8)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            if ($response->successful()) {
                $data = $response->json();
                $models = [];
                foreach ($data['models'] ?? [] as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods)) {
                        $name = str_replace('models/', '', $m['name'] ?? '');
                        // Strictly filter OUT TTS, audio, speech, embedding, imagen, and non-text generation models
                        if (
                            ! empty($name) &&
                            ! preg_match('/(?:tts|audio|speech|embed|aqa|imagen|whisper|transcription|realtime)/i', $name)
                        ) {
                            $models[] = $name;
                        }
                    }
                }

                return $models;
            }
        } catch (\Throwable $e) {
            Log::debug('Failed to fetch Gemini available models list: '.$e->getMessage());
        }

        return [];
    }

    /**
     * Get list of candidate text-generation model identifiers to try in order of preference.
     * Ensures NO TTS or audio models are ever queried for text/diet generation.
     *
     * @return array<int, string>
     */
    protected function getModelCandidates(string $model, ?string $apiKey = null): array
    {
        $normalized = strtolower(trim($model));

        // Strip any accidental TTS or non-text model reference
        if (preg_match('/(?:tts|audio|speech|embed|aqa|imagen|whisper|transcription|realtime)/i', $normalized)) {
            $normalized = 'gemini-2.5-flash';
        }

        $candidates = [$normalized];

        // Specific family fallbacks
        if (str_contains($normalized, '3.8')) {
            $candidates[] = 'gemini-3.8-flash';
            $candidates[] = 'gemini-3.5-flash';
            $candidates[] = 'gemini-2.5-flash';
            $candidates[] = 'gemini-2.0-flash';
        } elseif (str_contains($normalized, '3.5')) {
            $candidates[] = 'gemini-3.5-flash';
            $candidates[] = 'gemini-3.8-flash';
            $candidates[] = 'gemini-2.5-flash';
            $candidates[] = 'gemini-2.0-flash';
        } elseif (str_contains($normalized, '3.1')) {
            $candidates[] = 'gemini-3.1-flash-lite';
            $candidates[] = 'gemini-2.0-flash-lite';
            $candidates[] = 'gemini-2.5-flash';
            $candidates[] = 'gemini-2.0-flash';
        } elseif (str_contains($normalized, '2.5')) {
            $candidates[] = 'gemini-2.5-flash';
            $candidates[] = 'gemini-2.5-pro';
            $candidates[] = 'gemini-2.0-flash';
        } elseif (str_contains($normalized, '2.0')) {
            $candidates[] = 'gemini-2.0-flash';
            $candidates[] = 'gemini-2.0-flash-lite';
            $candidates[] = 'gemini-2.5-flash';
        } elseif (str_contains($normalized, 'pro')) {
            $candidates[] = 'gemini-2.5-pro';
            $candidates[] = 'gemini-2.5-flash';
            $candidates[] = 'gemini-2.0-flash';
            $candidates[] = 'gemini-1.5-pro-latest';
        }

        // Standard modern text generation fallback stack
        $standardModernStack = [
            'gemini-2.5-flash',
            'gemini-2.0-flash',
            'gemini-2.5-pro',
            'gemini-2.0-flash-lite',
            'gemini-3.5-flash',
            'gemini-3.8-flash',
            'gemini-3.1-flash-lite',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
            'gemini-1.5-pro-latest',
            'gemini-1.5-pro',
        ];

        $candidates = array_merge($candidates, $standardModernStack);

        // If apiKey is provided, discover Google API's live models list
        if (! empty($apiKey)) {
            $liveModels = $this->fetchAvailableModels($apiKey);
            if (! empty($liveModels)) {
                $candidates = array_merge([$normalized], $liveModels, $candidates);
            }
        }

        // Final strict filter: exclude any non-text/TTS/audio model
        $filtered = array_filter($candidates, function ($item) {
            return ! empty($item) && ! preg_match('/(?:tts|audio|speech|embed|aqa|imagen|whisper|transcription|realtime)/i', $item);
        });

        return array_values(array_unique($filtered));
    }

    /**
     * Test Google Gemini API connection with a given API key and model.
     *
     * @return array{success: bool, message: string, model?: string}
     */
    public function testConnection(string $apiKey, ?string $model = null): array
    {
        $requestedModel = $model ?: 'gemini-2.5-flash';
        $apiKey = trim($apiKey);

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Gemini API Key cannot be empty.',
            ];
        }

        $candidates = $this->getModelCandidates($requestedModel, $apiKey);
        $lastError = 'Unknown error';

        foreach ($candidates as $candModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candModel}:generateContent?key={$apiKey}";

            try {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Respond strictly in JSON: {"status": "ok", "message": "Connection verified"}'],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $cleanText = trim(preg_replace('/^```(?:json)?\s*/i', '', preg_replace('/\s*```$/', '', trim($text))));
                    $parsed = json_decode($cleanText, true);

                    $activeModelMsg = ($candModel === $requestedModel) ? "Google Gemini AI ({$candModel})" : "Google Gemini AI ({$candModel} [resolved for {$requestedModel}])";

                    return [
                        'success' => true,
                        'model' => $candModel,
                        'message' => "{$activeModelMsg} connected and verified successfully!",
                    ];
                }

                $errorData = $response->json();
                $lastError = $errorData['error']['message'] ?? ('HTTP '.$response->status().': '.$response->body());

                // If not a 404 / model not found, don't keep trying alternative models (e.g. invalid API key or quota issue)
                if ($response->status() !== 404 && ! str_contains(strtolower($lastError), 'not found') && ! str_contains(strtolower($lastError), 'not supported')) {
                    return [
                        'success' => false,
                        'message' => 'Google Gemini API Error: '.$lastError,
                    ];
                }
            } catch (Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return [
            'success' => false,
            'message' => 'Google Gemini API Error: '.$lastError,
        ];
    }

    /**
     * Generate diet plan using Google Gemini AI models.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    protected function generateWithGemini(array $input, string $apiKey, string $model): array
    {
        $name = trim((string) ($input['name'] ?? 'Gym Member'));
        $age = max(12, min(90, (int) ($input['age'] ?? 26)));
        $gender = strtolower((string) ($input['gender'] ?? 'male'));
        $height = max(100, min(240, (float) ($input['height'] ?? 172)));
        $weight = max(30, min(250, (float) ($input['weight'] ?? 72)));
        $goal = strtolower(str_replace([' ', '-'], '_', (string) ($input['goal'] ?? 'muscle_gain')));
        $activityLevel = strtolower(str_replace([' ', '-'], '_', (string) ($input['activity_level'] ?? 'moderately_active')));
        $dietPreference = strtolower(str_replace([' ', '-'], '_', (string) ($input['diet_preference'] ?? 'vegetarian')));
        $mealsPerDay = max(3, min(6, (int) ($input['meals_per_day'] ?? 4)));
        $workoutTime = strtolower(str_replace([' ', '-'], '_', (string) ($input['workout_time'] ?? 'morning')));
        $foodPreferences = trim((string) ($input['food_preferences'] ?? ''));
        $foodsToAvoid = trim((string) ($input['foods_to_avoid'] ?? ''));
        $allergies = trim((string) ($input['allergies'] ?? ''));
        $additionalNotes = trim((string) ($input['additional_notes'] ?? ''));

        $prompt = <<<PROMPT
You are a World-Class Certified Clinical Nutritionist & Sports Dietitian specializing in personalized Indian and international gym nutrition plans.

Create a highly detailed, authentic, scientifically accurate, and personalized Indian-friendly daily diet plan for the following gym member:

MEMBER BIOMETRIC & LIFESTYLE PROFILE:
- Name: {$name}
- Age: {$age} years old (CRITICAL: Adjust metabolism, hormonal balance, protein bioavailability, recovery capacity, micronutrients like calcium/iron/vitamin D, and joint support based specifically on this age group)
- Gender: {$gender}
- Height: {$height} cm
- Weight: {$weight} kg
- Primary Fitness Goal: {$goal} (e.g. muscle_gain, fat_loss, weight_loss, weight_gain, maintenance, general_fitness)
- Activity & Training Level: {$activityLevel}
- Workout Timing: {$workoutTime} (CRITICAL: Place Pre-Workout energy and Post-Workout protein/glycogen recovery meals precisely relative to this workout timing)
- Meals Per Day: {$mealsPerDay} meals
- Dietary Preference: {$dietPreference} (vegetarian / non_vegetarian / eggetarian / vegan)
- Preferred Foods: {$foodPreferences}
- Foods to Avoid: {$foodsToAvoid}
- Food Allergies / Intolerances: {$allergies}
- Special Notes / Medical Conditions / Lifestyle: {$additionalNotes}

NUTRITIONAL RULES & CALCULATIONS:
1. Accurately calculate BMR (using Mifflin-St Jeor formula) and TDEE based on activity multiplier.
2. Calculate target daily Calories and precise macronutrients (Protein in grams [1.6g - 2.2g per kg depending on goal], Carbohydrates in grams, Fats in grams, Dietary Fiber in grams, and Daily Hydration in Litres).
3. Structure exactly {$mealsPerDay} meals throughout the day with precise meal names, recommended clock times, individual meal calories and macros (P, C, F).
4. Each meal MUST have:
   - "items_description": Clear bullet points with specific gram/portion measurements (e.g. "• 100g Grilled Paneer", "• 2 medium Whole Wheat Phulkas", "• 1 katori (150g) Yellow Moong Dal", "• 1 bowl Cucumber & Beetroot salad").
   - "alternatives": A practical alternative Indian food combination matching similar macros.
5. Provide 4-5 actionable lifestyle & cooking guidelines tailored to the member's age, goal, and medical notes.
6. Provide 2-4 safe, evidence-based optional fitness supplements (e.g. Whey Protein, Creatine, Omega-3, Multivitamin) with exact dosage and timing.
7. Output strict JSON matching the exact schema below. Do NOT output any markdown code fences, comments, or explanations outside the JSON.

REQUIRED JSON SCHEMA:
{
  "member_name": "{$name}",
  "plan_title": "String summarizing Member Name, Goal & Diet Type",
  "goal": "{$goal}",
  "diet_preference": "{$dietPreference}",
  "daily_totals": {
    "calories": 2400,
    "protein_grams": 140,
    "carbs_grams": 280,
    "fat_grams": 60,
    "fiber_grams": 35,
    "water_liters": 3.5
  },
  "bmr_calculated": 1650,
  "tdee_calculated": 2550,
  "meals": [
    {
      "sort_order": 1,
      "meal_type": "breakfast",
      "meal_name": "Post-Workout High Protein Breakfast",
      "recommended_time": "08:30 AM",
      "target_macros": {
        "calories": 600,
        "protein_g": 35,
        "carbs_g": 70,
        "fat_g": 15
      },
      "items_description": "• Bullet point list of food items with gram portions",
      "alternatives": "• Bullet point list of alternative options"
    }
  ],
  "guidelines": [
    "Actionable tip 1",
    "Actionable tip 2",
    "Actionable tip 3",
    "Actionable tip 4"
  ],
  "optional_supplements": [
    {
      "name": "Supplement Name",
      "dosage": "Recommended dosage and timing",
      "purpose": "Why this supplement helps with their goal [OPTIONAL]"
    }
  ],
  "medical_disclaimer": "This diet plan is an AI-generated fitness guideline created for general gym nutrition purposes. It is not a medical prescription. Members with health conditions should consult a licensed doctor or clinical dietitian."
}
PROMPT;

        $candidates = $this->getModelCandidates($model, $apiKey);
        $lastException = null;

        foreach ($candidates as $candModel) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$candModel}:generateContent?key={$apiKey}";

            try {
                $response = Http::withoutVerifying()
                    ->timeout(45)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.65,
                            'topK' => 40,
                            'topP' => 0.95,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                if (! $response->successful()) {
                    $errBody = $response->body();
                    $errStatus = $response->status();

                    if ($errStatus === 404 || str_contains(strtolower($errBody), 'not found') || str_contains(strtolower($errBody), 'not supported')) {
                        $lastException = new Exception("Gemini API request failed ({$candModel}) with status {$errStatus}: {$errBody}");

                        continue;
                    }

                    throw new Exception("Gemini API request failed ({$candModel}) with status {$errStatus}: {$errBody}");
                }

                $body = $response->json();
                $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (empty($rawText)) {
                    throw new Exception('Gemini API returned empty response candidates.');
                }

                // Clean any potential markdown wrapping
                $cleanJson = trim(preg_replace('/^```(?:json)?\s*/i', '', preg_replace('/\s*```$/', '', trim($rawText))));
                $parsed = json_decode($cleanJson, true);

                if (! is_array($parsed) || empty($parsed['meals'])) {
                    throw new Exception('Failed to decode structured JSON diet plan from Gemini response: '.$rawText);
                }

                // Normalize and safeguard values
                $parsed['member_name'] = $parsed['member_name'] ?? $name;
                $parsed['plan_title'] = $parsed['plan_title'] ?? "{$name} - Personalized Diet Plan";
                $parsed['goal'] = $parsed['goal'] ?? $goal;
                $parsed['diet_preference'] = $parsed['diet_preference'] ?? $dietPreference;
                $parsed['bmr_calculated'] = (int) ($parsed['bmr_calculated'] ?? 1600);
                $parsed['tdee_calculated'] = (int) ($parsed['tdee_calculated'] ?? 2400);

                if (! isset($parsed['daily_totals']) || ! is_array($parsed['daily_totals'])) {
                    $parsed['daily_totals'] = [
                        'calories' => (int) ($parsed['daily_calories'] ?? 2200),
                        'protein_grams' => (int) ($parsed['protein_grams'] ?? 120),
                        'carbs_grams' => (int) ($parsed['carbs_grams'] ?? 250),
                        'fat_grams' => (int) ($parsed['fat_grams'] ?? 60),
                        'fiber_grams' => (int) ($parsed['fiber_grams'] ?? 30),
                        'water_liters' => (float) ($parsed['water_liters'] ?? 3.5),
                    ];
                }

                if (empty($parsed['medical_disclaimer'])) {
                    $parsed['medical_disclaimer'] = 'This diet plan is an AI-generated fitness guideline created for general gym nutrition purposes. It is not a medical prescription. Members with health conditions should consult a licensed doctor or clinical dietitian.';
                }

                $parsed['is_gemini'] = true;
                $parsed['gemini_model'] = $candModel;
                $parsed['generated_at'] = now()->toIso8601String();

                return $parsed;
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?: new Exception("Failed to generate diet with Gemini AI model {$model}");
    }

    /**
     * Generate a personalized, practical, Indian-friendly diet plan using the built-in algorithmic engine.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generateAlgorithmic(array $input): array
    {
        $name = trim((string) ($input['name'] ?? 'Gym Member'));
        $age = max(12, min(90, (int) ($input['age'] ?? 26)));
        $gender = strtolower((string) ($input['gender'] ?? 'male'));
        $height = max(100, min(240, (float) ($input['height'] ?? 172)));
        $weight = max(30, min(250, (float) ($input['weight'] ?? 72)));
        $goal = strtolower(str_replace([' ', '-'], '_', (string) ($input['goal'] ?? 'muscle_gain')));
        $activityLevel = strtolower(str_replace([' ', '-'], '_', (string) ($input['activity_level'] ?? 'moderately_active')));
        $dietPreference = strtolower(str_replace([' ', '-'], '_', (string) ($input['diet_preference'] ?? 'vegetarian')));
        $mealsPerDay = max(3, min(6, (int) ($input['meals_per_day'] ?? 4)));
        $workoutTime = strtolower(str_replace([' ', '-'], '_', (string) ($input['workout_time'] ?? 'morning')));
        $foodPreferences = (string) ($input['food_preferences'] ?? '');
        $foodsToAvoid = (string) ($input['foods_to_avoid'] ?? '');
        $allergies = (string) ($input['allergies'] ?? '');
        $additionalNotes = (string) ($input['additional_notes'] ?? '');

        // 1. Calculate BMR (Mifflin-St Jeor Formula)
        if ($gender === 'female') {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
        } else {
            $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
        }

        // 2. Activity Multiplier -> TDEE
        $activityMultipliers = [
            'sedentary' => 1.2,
            'lightly_active' => 1.375,
            'moderately_active' => 1.55,
            'very_active' => 1.725,
            'extremely_active' => 1.9,
        ];
        $multiplier = $activityMultipliers[$activityLevel] ?? 1.55;
        $tdee = $bmr * $multiplier;

        // 3. Goal Calorie Adjustment
        $calories = match ($goal) {
            'weight_loss', 'fat_loss' => $tdee - 450,
            'weight_gain', 'muscle_gain' => $tdee + 350,
            default => $tdee, // maintenance, general_fitness
        };

        // Safety floors
        $minCalories = ($gender === 'female') ? 1300 : 1550;
        $targetCalories = max($minCalories, (int) round($calories));

        // 4. Macronutrient Targets
        $proteinGramsPerKg = match ($goal) {
            'muscle_gain', 'fat_loss' => 2.0,
            'weight_loss', 'weight_gain' => 1.8,
            default => 1.6,
        };
        $targetProtein = (int) round(min($targetCalories * 0.35 / 4, $weight * $proteinGramsPerKg));
        $targetFat = (int) round(($targetCalories * 0.24) / 9);
        $carbCalories = max(0, $targetCalories - ($targetProtein * 4) - ($targetFat * 9));
        $targetCarbs = (int) round($carbCalories / 4);
        $targetFiber = (int) round(min(45, max(26, $targetCalories / 70)));
        $waterLiters = round(max(2.5, min(5.0, $weight * 0.04)), 1);

        // 5. Structure Meals according to workout time & meals per day
        $meals = $this->buildMeals(
            $mealsPerDay,
            $workoutTime,
            $dietPreference,
            $targetCalories,
            $targetProtein,
            $targetCarbs,
            $targetFat,
            $foodPreferences,
            $foodsToAvoid,
            $allergies
        );

        $goalTitles = [
            'weight_loss' => 'Healthy Weight Loss & Fat Shred',
            'fat_loss' => 'Lean Fat Loss & Body Recomposition',
            'muscle_gain' => 'Clean Muscle Hypertrophy & Strength',
            'weight_gain' => 'Caloric Surplus Clean Weight Gain',
            'maintenance' => 'Balanced Metabolic Maintenance',
            'general_fitness' => 'Active Lifestyle & General Vitality',
        ];
        $dietTitles = [
            'vegetarian' => 'Vegetarian (Lacto/High-Protein)',
            'non_vegetarian' => 'Non-Vegetarian (Lean Protein)',
            'vegan' => '100% Plant-Based Vegan',
            'eggetarian' => 'Eggetarian (Egg & Dairy Protein)',
        ];

        $planTitle = "{$name} - ".($goalTitles[$goal] ?? 'Custom Fitness Diet').' ('.($dietTitles[$dietPreference] ?? 'Balanced').')';

        $guidelines = [
            "Drink at least {$waterLiters} Litres of filtered water daily to maintain metabolic efficiency and muscle hydration.",
            'Meal timings can be shifted by 30-45 minutes to suit your daily gym/work schedule.',
            'Prioritize whole foods and cook with measured healthy fats (e.g. 1-2 tsp mustard/olive/coconut oil or desi ghee).',
            'Keep raw salads (cucumber, tomato, carrot with lemon) with major meals for fiber and digestive enzyme support.',
            'Limit refined sugars, deep-fried snacks, and packaged ultra-processed beverages.',
        ];

        $optionalSupplements = [
            [
                'name' => 'Whey Protein Concentrate/Isolate (or Plant Protein for Vegans)',
                'dosage' => '1 Scoop (30g) daily post-workout in 250ml water',
                'purpose' => 'Supports convenient daily protein goal and accelerated muscle recovery [OPTIONAL]',
            ],
            [
                'name' => 'Creatine Monohydrate',
                'dosage' => '3g - 5g daily with warm water or pre/post-workout meal',
                'purpose' => 'Increases cellular ATP energy, power output, and muscle volume [OPTIONAL]',
            ],
            [
                'name' => 'Omega-3 Fish Oil / Flaxseed Oil',
                'dosage' => '1 capsule daily with lunch or dinner',
                'purpose' => 'Joint lubrication, cardiovascular health, and anti-inflammatory support [OPTIONAL]',
            ],
            [
                'name' => 'Multivitamin & Mineral Tablet',
                'dosage' => '1 tablet after breakfast',
                'purpose' => 'Covers potential micronutrient shortfalls during active training [OPTIONAL]',
            ],
        ];

        return [
            'member_name' => $name,
            'plan_title' => $planTitle,
            'goal' => $goal,
            'diet_preference' => $dietPreference,
            'daily_totals' => [
                'calories' => $targetCalories,
                'protein_grams' => $targetProtein,
                'carbs_grams' => $targetCarbs,
                'fat_grams' => $targetFat,
                'fiber_grams' => $targetFiber,
                'water_liters' => $waterLiters,
            ],
            'bmr_calculated' => (int) round($bmr),
            'tdee_calculated' => (int) round($tdee),
            'meals' => $meals,
            'guidelines' => $guidelines,
            'optional_supplements' => $optionalSupplements,
            'medical_disclaimer' => 'This diet plan is an AI-generated fitness guideline created for general gym nutrition purposes. It is not a medical prescription. Members with underlying health conditions (e.g., diabetes, hypertension, thyroid, kidney disease, pregnancy) should consult a licensed doctor or clinical dietitian before initiating any significant dietary changes.',
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build varied Indian meals based on preference, macros, and workout timing.
     */
    protected function buildMeals(
        int $mealsCount,
        string $workoutTime,
        string $dietPreference,
        int $totalCalories,
        int $totalProtein,
        int $totalCarbs,
        int $totalFat,
        string $preferences,
        string $avoid,
        string $allergies
    ): array {
        $isVeg = in_array($dietPreference, ['vegetarian', 'vegan']);
        $isVegan = $dietPreference === 'vegan';
        $isEgg = $dietPreference === 'eggetarian';

        // Distribution ratios based on meal count
        $mealTemplates = $this->getMealScheduleTemplates($mealsCount, $workoutTime);
        $meals = [];

        foreach ($mealTemplates as $index => $tpl) {
            $mealRatio = $tpl['ratio'];
            $mealCal = (int) round($totalCalories * $mealRatio);
            $mealP = (int) round($totalProtein * $mealRatio);
            $mealC = (int) round($totalCarbs * $mealRatio);
            $mealF = (int) round($totalFat * $mealRatio);

            $mealOptions = $this->getIndianMealOptions(
                $tpl['type'],
                $dietPreference,
                $preferences,
                $avoid,
                $allergies
            );

            $primary = $mealOptions['primary'];
            $alternative = $mealOptions['alternative'];

            $meals[] = [
                'sort_order' => $index + 1,
                'meal_type' => $tpl['type'],
                'meal_name' => $tpl['name'],
                'recommended_time' => $tpl['time'],
                'target_macros' => [
                    'calories' => $mealCal,
                    'protein_g' => $mealP,
                    'carbs_g' => $mealC,
                    'fat_g' => $mealF,
                ],
                'items_description' => $primary,
                'alternatives' => $alternative,
            ];
        }

        return $meals;
    }

    /**
     * Map out meal time slots based on workout timing and total meals.
     */
    protected function getMealScheduleTemplates(int $count, string $workoutTime): array
    {
        $isMorningWorkout = in_array($workoutTime, ['early_morning', 'morning']);
        $isEveningWorkout = in_array($workoutTime, ['evening', 'afternoon', 'night']);

        if ($count === 3) {
            return [
                [
                    'type' => 'breakfast',
                    'name' => $isMorningWorkout ? 'Post-Workout Power Breakfast' : 'Energizing Breakfast',
                    'time' => $isMorningWorkout ? '08:30 AM' : '08:00 AM',
                    'ratio' => 0.35,
                ],
                [
                    'type' => 'lunch',
                    'name' => 'Nutritious High-Protein Lunch',
                    'time' => '01:30 PM',
                    'ratio' => 0.40,
                ],
                [
                    'type' => 'dinner',
                    'name' => $isEveningWorkout ? 'Post-Workout Recovery Dinner' : 'Light & Balanced Dinner',
                    'time' => '08:30 PM',
                    'ratio' => 0.25,
                ],
            ];
        }

        if ($count === 4) {
            if ($isMorningWorkout) {
                return [
                    [
                        'type' => 'breakfast',
                        'name' => 'Post-Workout Power Breakfast',
                        'time' => '08:00 AM',
                        'ratio' => 0.30,
                    ],
                    [
                        'type' => 'lunch',
                        'name' => 'Balanced Indian Lunch',
                        'time' => '01:00 PM',
                        'ratio' => 0.35,
                    ],
                    [
                        'type' => 'evening_snack',
                        'name' => 'High-Fiber Evening Snack',
                        'time' => '05:30 PM',
                        'ratio' => 0.15,
                    ],
                    [
                        'type' => 'dinner',
                        'name' => 'Light Recovery Dinner',
                        'time' => '08:30 PM',
                        'ratio' => 0.20,
                    ],
                ];
            } else {
                return [
                    [
                        'type' => 'breakfast',
                        'name' => 'Power Breakfast',
                        'time' => '08:30 AM',
                        'ratio' => 0.25,
                    ],
                    [
                        'type' => 'lunch',
                        'name' => 'Balanced Indian Lunch',
                        'time' => '01:30 PM',
                        'ratio' => 0.35,
                    ],
                    [
                        'type' => 'evening_snack',
                        'name' => 'Pre-Workout Energizer Snack',
                        'time' => '05:30 PM',
                        'ratio' => 0.15,
                    ],
                    [
                        'type' => 'dinner',
                        'name' => 'Post-Workout Recovery Dinner',
                        'time' => '08:45 PM',
                        'ratio' => 0.25,
                    ],
                ];
            }
        }

        if ($count === 5) {
            return [
                [
                    'type' => 'breakfast',
                    'name' => $isMorningWorkout ? 'Post-Workout Breakfast' : 'Energizing Breakfast',
                    'time' => '08:00 AM',
                    'ratio' => 0.25,
                ],
                [
                    'type' => 'morning_snack',
                    'name' => 'Mid-Morning Boost Snack',
                    'time' => '11:00 AM',
                    'ratio' => 0.10,
                ],
                [
                    'type' => 'lunch',
                    'name' => 'Wholesome High-Protein Lunch',
                    'time' => '01:30 PM',
                    'ratio' => 0.30,
                ],
                [
                    'type' => 'evening_snack',
                    'name' => $isEveningWorkout ? 'Pre-Workout Fuel Snack' : 'Evening Refreshment',
                    'time' => '05:30 PM',
                    'ratio' => 0.15,
                ],
                [
                    'type' => 'dinner',
                    'name' => $isEveningWorkout ? 'Post-Workout Dinner' : 'Clean Balanced Dinner',
                    'time' => '08:30 PM',
                    'ratio' => 0.20,
                ],
            ];
        }

        // 6 Meals default
        return [
            [
                'type' => 'morning_snack',
                'name' => $isMorningWorkout ? 'Pre-Workout Quick Fuel' : 'Early Morning Cleanser',
                'time' => '06:30 AM',
                'ratio' => 0.08,
            ],
            [
                'type' => 'breakfast',
                'name' => 'Hearty High-Protein Breakfast',
                'time' => '08:30 AM',
                'ratio' => 0.25,
            ],
            [
                'type' => 'morning_snack',
                'name' => 'Mid-Morning Fruit & Nuts',
                'time' => '11:30 AM',
                'ratio' => 0.10,
            ],
            [
                'type' => 'lunch',
                'name' => 'Complete Indian Power Lunch',
                'time' => '01:30 PM',
                'ratio' => 0.27,
            ],
            [
                'type' => 'evening_snack',
                'name' => $isEveningWorkout ? 'Pre-Workout Energy Snack' : 'Evening High-Protein Snack',
                'time' => '05:30 PM',
                'ratio' => 0.12,
            ],
            [
                'type' => 'dinner',
                'name' => 'Restorative Recovery Dinner',
                'time' => '08:45 PM',
                'ratio' => 0.18,
            ],
        ];
    }

    /**
     * Generate culturally authentic, practical Indian meal food item lists.
     */
    protected function getIndianMealOptions(
        string $mealType,
        string $dietPref,
        string $preferences,
        string $avoid,
        string $allergies
    ): array {
        $isVeg = ($dietPref === 'vegetarian');
        $isNonVeg = ($dietPref === 'non_vegetarian');
        $isVegan = ($dietPref === 'vegan');
        $isEgg = ($dietPref === 'eggetarian');

        if ($mealType === 'breakfast') {
            if ($isNonVeg || $isEgg) {
                return [
                    'primary' => "• 3 Whole Eggs / 4 Egg Whites (Boiled or Omelette with spinach & onions)\n• 2 slices Whole Wheat Brown Bread (or 2 medium Phulkas)\n• 1 small bowl Rolled Oats (40g) cooked in 150ml milk with 5 Almonds & 1 tsp Chia Seeds",
                    'alternative' => "• 2 Moong Dal Chillas stuffed with 50g grated low-fat Paneer + Mint Coriander Chutney\n• 1 glass warm lemon water with 1 tsp honey",
                ];
            } elseif ($isVegan) {
                return [
                    'primary' => "• 100g Tofu Scramble with tomatoes, turmeric & bell peppers\n• 1 bowl Rolled Oats (50g) in 200ml Soy/Almond milk with 1 tbsp Peanut Butter & 1 sliced Banana",
                    'alternative' => '• 2 Besan & Spinach Chillas with mint chutney + 1 cup Black Coffee / Green Tea',
                ];
            } else {
                // Vegetarian
                return [
                    'primary' => "• 2 High-Protein Moong Dal / Besan Chillas stuffed with 75g fresh Paneer\n• 1 small bowl Rolled Oats (40g) in 150ml low-fat Milk topped with 5 Almonds and 1 sliced Apple\n• 1 cup Green Tea or Black Coffee (no added sugar)",
                    'alternative' => '• 1 bowl Vegetable Poha / Upma enriched with 50g boiled Sprouts + 1 glass (200ml) Low-fat Milk with 1 scoop Protein Powder (optional)',
                ];
            }
        }

        if ($mealType === 'lunch') {
            if ($isNonVeg) {
                return [
                    'primary' => "• 150g Grilled / Curry Chicken Breast (cooked with 1 tsp mustard oil)\n• 1 katori (150g) Steamed Brown or Basmati Rice\n• 1 katori (150g) Yellow Moong or Toor Dal\n• 1 bowl Mixed Fresh Salad (Cucumber, Tomatoes, Beetroot with lemon juice)",
                    'alternative' => '• 150g Grilled Fish (Rohu / Katla / Tilapia / Pomfret) + 2 medium Whole Wheat Rotis + 1 katori Tadka Dal + 1 bowl Green Salad',
                ];
            } elseif ($isVegan) {
                return [
                    'primary' => "• 100g Soya Chunks Curry (boiled & seasoned with Indian spices)\n• 1.5 katoris Boiled Chickpeas / Rajma\n• 2 medium Multigrain Rotis (no butter/ghee)\n• 1 large bowl raw salad with lemon",
                    'alternative' => '• 150g Pan-seared Tofu with mixed vegetables + 1 katori Brown Rice + 1 katori Black Lentil Dal',
                ];
            } else {
                // Vegetarian / Eggetarian
                return [
                    'primary' => "• 100g Grilled / Sauteed Low-Fat Paneer\n• 1 katori (150g) Thick Boiled Dal (Chana Dal / Moong Dal / Rajma)\n• 2 medium Whole Wheat Phulkas (lightly brushed with desi ghee)\n• 1 katori (100g) Fresh Curd / Dahi\n• 1 bowl Mixed Cucumber & Carrot Salad",
                    'alternative' => '• 50g Soya Chunks Pulao with vegetables + 1 katori Dal Tadka + 1 cup Cucumber Mint Raita',
                ];
            }
        }

        if ($mealType === 'evening_snack' || $mealType === 'morning_snack') {
            if ($isNonVeg || $isEgg) {
                return [
                    'primary' => "• 2 Hard Boiled Eggs (sprinkled with black pepper & chaat masala)\n• 1 medium seasonal fruit (Apple / Orange / Guava)\n• 1 cup Green Tea (or Warm Black Coffee)",
                    'alternative' => '• 1 bowl Boiled Moong Sprouts Chaat (with diced onions, tomato, lemon, roasted peanuts)',
                ];
            } elseif ($isVegan) {
                return [
                    'primary' => "• 1 bowl Roasted Black Chana (30g) + 10 Almonds & 2 Walnuts\n• 1 medium Banana or Green Apple\n• 1 cup Herbal / Green Tea",
                    'alternative' => '• 2 Rice Cakes topped with 1 tbsp Natural Peanut Butter & chia seeds',
                ];
            } else {
                // Vegetarian
                return [
                    'primary' => "• 1 bowl Boiled Sprout Salad (Moong + Kala Chana) mixed with tomato, onion, lemon & rock salt\n• 10 Almonds + 2 Whole Walnuts\n• 1 cup Green Tea or Ginger Black Tea",
                    'alternative' => '• 1 bowl Roasted Makhana (Fox Nuts, 30g) lightly roasted in 1/2 tsp ghee + 1 scoop Whey Protein in 200ml water (optional)',
                ];
            }
        }

        if ($mealType === 'dinner') {
            if ($isNonVeg) {
                return [
                    'primary' => "• 150g Pan-Seared Chicken Breast / White Fish Tikka\n• 2 medium Whole Wheat Rotis\n• 1 bowl Stir-Fried Seasonal Vegetables (Beans, Carrots, Broccoli, Capsicum)\n• 1 small katori Yellow Moong Dal Soup",
                    'alternative' => '• 3 Whole Egg Bhurji (cooked with onions, green chillies & tomatoes) + 2 Phulkas + 1 bowl Cucumber Tomato Salad',
                ];
            } elseif ($isVegan) {
                return [
                    'primary' => "• 100g Stir-Fried Tofu with broccoli, bell peppers & mushrooms\n• 1 katori Yellow Moong Dal / Masoor Dal\n• 2 medium Whole Wheat Rotis\n• 1 bowl Fresh Green Salad",
                    'alternative' => '• 1 bowl Soybean & Vegetable Stir Fry + 1 katori Brown Rice + Warm Cumin Water',
                ];
            } else {
                // Vegetarian
                return [
                    'primary' => "• 100g Low-Fat Paneer Bhurji / Grilled Paneer Tikka\n• 1 katori (150g) Yellow Moong Dal or Palak Dal\n• 2 medium Whole Wheat Phulkas (warm & soft)\n• 1 bowl Sauteed / Steamed Green Vegetables (Beans, Lauki, Methi, or Cauliflower)",
                    'alternative' => '• 1 bowl Vegetable Khichdi (50:50 Moong Dal & Rice) topped with 1 tsp pure Cow Ghee + 1 bowl Fresh Curd (100g)',
                ];
            }
        }

        // Pre/Post workout or General
        return [
            'primary' => "• 1 medium Banana (100g) + 1 cup Black Coffee (pre-workout)\n• 1 scoop Whey Protein in 250ml water (post-workout)",
            'alternative' => "• 2 Whole Wheat Toast slices with 1 tbsp Natural Peanut Butter\n• 1 glass fresh Coconut Water (200ml)",
        ];
    }
}
