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

    /**
     * Match a workout exercise name against the video library.
     *
     * @param string|null $name
     * @return array<string, mixed>|null
     */
    public static function matchByName(?string $name): ?array
    {
        $raw = strtolower(trim((string) $name));
        if ($raw === '') {
            return null;
        }

        $all = static::all();
        $normalizedTarget = preg_replace('/[^a-z0-9]+/', ' ', $raw);
        $normalizedTarget = trim((string) $normalizedTarget);
        $compactTarget = str_replace(' ', '', $normalizedTarget);

        foreach ($all as $ex) {
            if (empty($ex['video_url'])) {
                continue;
            }

            $title = strtolower(trim((string) ($ex['title'] ?? '')));
            if ($title === $raw) {
                return $ex;
            }
        }

        foreach ($all as $ex) {
            if (empty($ex['video_url'])) {
                continue;
            }

            $norm = trim((string) preg_replace('/[^a-z0-9]+/', ' ', strtolower((string) ($ex['title'] ?? ''))));
            if ($norm === $normalizedTarget || str_replace(' ', '', $norm) === $compactTarget) {
                return $ex;
            }
        }

        $targetTokens = array_values(array_filter(
            explode(' ', $normalizedTarget),
            static fn (string $t): bool => strlen($t) >= 2
        ));
        if (empty($targetTokens)) {
            return null;
        }

        $singularize = static fn (string $w): string => strlen($w) > 3 && str_ends_with($w, 's') && ! str_ends_with($w, 'ss')
            ? substr($w, 0, -1)
            : $w;
        $singularTargetTokens = array_map($singularize, $targetTokens);

        $bestMatch = null;
        $bestScore = PHP_INT_MAX;

        foreach ($all as $ex) {
            if (empty($ex['video_url'])) {
                continue;
            }

            $norm = trim((string) preg_replace('/[^a-z0-9]+/', ' ', strtolower((string) ($ex['title'] ?? ''))));
            $exTokens = array_values(array_filter(explode(' ', $norm), static fn (string $t): bool => strlen($t) >= 2));
            if (empty($exTokens)) {
                continue;
            }

            $singularExTokens = array_map($singularize, $exTokens);

            if ($singularTargetTokens === $singularExTokens) {
                return $ex;
            }

            $shorter = count($singularTargetTokens) <= count($singularExTokens) ? $singularTargetTokens : $singularExTokens;
            $longer = count($singularTargetTokens) <= count($singularExTokens) ? $singularExTokens : $singularTargetTokens;

            $minRequired = count($singularTargetTokens) === 1 ? 1 : 2;
            if (count($shorter) >= $minRequired && count(array_diff($shorter, $longer)) === 0) {
                $extraWords = count($longer) - count($shorter);
                if ($extraWords <= 2 && $extraWords < $bestScore) {
                    $bestScore = $extraWords;
                    $bestMatch = $ex;
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Compact exercise list with valid video URLs for the member video library.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function portalLibrary(): array
    {
        $items = [];
        foreach (static::all() as $ex) {
            if (empty($ex['video_url'])) {
                continue;
            }

            $items[] = [
                'id' => $ex['id'],
                'title' => $ex['title'] ?? '',
                'category' => $ex['category'] ?? 'General',
                'equipment' => $ex['equipment'] ?? 'Bodyweight',
                'video_url' => $ex['video_url'],
                'default_sets' => $ex['default_sets'] ?? 3,
                'default_reps' => $ex['default_reps'] ?? '10-12',
                'default_rest' => $ex['default_rest'] ?? '60s',
                'notes' => $ex['notes'] ?? '',
            ];
        }

        return $items;
    }
}
