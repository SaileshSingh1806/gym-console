<?php

namespace App\Services;

class AiDietPlannerService
{
    /**
     * Generate a personalized, practical, Indian-friendly diet plan JSON based on member input.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array
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
