<?php

namespace App\Services;

class ExerciseDatabase
{
    /**
     * In-memory cache of all 702 video exercises.
     *
     * @var array<int, array<string, mixed>>|null
     */
    protected static ?array $exercises = null;

    /**
     * Category metadata with icons.
     *
     * @var array<string, string>
     */
    protected static array $categoryIcons = [
        'Chest' => '🏋️',
        'Back' => '🦅',
        'Quads' => '🦵',
        'Core' => '🧱',
        'Abs' => '🍫',
        'Biceps' => '💪',
        'Triceps' => '💪',
        'Shoulders' => '🛡️',
        'Glutes' => '🍑',
        'Hamstrings' => '🦵',
        'Calves' => '🦶',
        'Forearms' => '✊',
        'Full Body' => '🔥',
        'Bodyweight' => '🤸',
        'Kettlebell' => '🔔',
        'Smith Machine' => '⚙️',
        'Legs' => '🦵',
    ];

    /**
     * Return all 702 video exercises.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        if (static::$exercises !== null) {
            return static::$exercises;
        }

        $jsonPath = database_path('data/exercise_videos_702.json');
        if (file_exists($jsonPath)) {
            $raw = file_get_contents($jsonPath);
            static::$exercises = json_decode($raw, true) ?? [];
        } else {
            static::$exercises = [];
        }

        return static::$exercises;
    }

    /**
     * Return category list with counts and icons.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function categories(): array
    {
        $all = static::all();
        $counts = [];

        foreach ($all as $ex) {
            $cat = $ex['category'] ?? 'General';
            $counts[$cat] = ($counts[$cat] ?? 0) + 1;
        }

        // Sort by predefined order or count descending
        uksort($counts, function ($a, $b) use ($counts) {
            return ($counts[$b] <=> $counts[$a]);
        });

        $result = [
            'All' => [
                'name' => 'All',
                'icon' => '⚡',
                'count' => count($all),
            ],
        ];

        foreach ($counts as $cat => $count) {
            $result[$cat] = [
                'name' => $cat,
                'icon' => static::$categoryIcons[$cat] ?? '🎯',
                'count' => $count,
            ];
        }

        return $result;
    }

    /**
     * Return distinct equipment / sub-categories.
     *
     * @return array<string, int>
     */
    public static function equipments(): array
    {
        $all = static::all();
        $equipments = [];

        foreach ($all as $ex) {
            $eq = $ex['equipment'] ?? 'Bodyweight';
            $equipments[$eq] = ($equipments[$eq] ?? 0) + 1;
        }

        arsort($equipments);

        return $equipments;
    }

    /**
     * Find exercise by ID.
     *
     * @param int|string $id
     * @return array<string, mixed>|null
     */
    public static function find(int|string $id): ?array
    {
        $all = static::all();
        foreach ($all as $ex) {
            if ((string) $ex['id'] === (string) $id) {
                return $ex;
            }
        }

        return null;
    }
}
