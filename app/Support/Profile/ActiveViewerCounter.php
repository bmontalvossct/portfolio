<?php

namespace App\Support\Profile;

use Illuminate\Support\Facades\Cache;

class ActiveViewerCounter
{
    public const ACTIVE_WINDOW_SECONDS = 90;

    public const BASELINE_FLOOR = 14;

    public const BASELINE_CEILING = 25;

    public const BASELINE_UPDATE_SECONDS = 5;

    private const CACHE_KEY = 'portfolio.active-viewers';

    private const LOCK_KEY = 'portfolio.active-viewers.lock';

    private const BASELINE_CACHE_KEY = 'portfolio.viewer-baseline';

    private const BASELINE_LOCK_KEY = 'portfolio.viewer-baseline.lock';

    public function touch(string $viewerId): int
    {
        return Cache::lock(self::LOCK_KEY, 5)->block(2, function () use ($viewerId): int {
            $now = now()->timestamp;
            $viewers = Cache::get(self::CACHE_KEY, []);

            if (! is_array($viewers)) {
                $viewers = [];
            }

            $viewers = array_filter(
                $viewers,
                static fn (mixed $expiresAt): bool => is_int($expiresAt) && $expiresAt > $now,
            );
            $viewers[$viewerId] = $now + self::ACTIVE_WINDOW_SECONDS;

            Cache::put(
                self::CACHE_KEY,
                $viewers,
                now()->addSeconds(self::ACTIVE_WINDOW_SECONDS + 30),
            );

            return count($viewers);
        });
    }

    public function displayCount(int $activeViewerCount): int
    {
        $baseline = Cache::lock(self::BASELINE_LOCK_KEY, 5)->block(2, function (): int {
            $now = now()->timestamp;
            $state = Cache::get(self::BASELINE_CACHE_KEY);

            if (! is_array($state)
                || ! is_int($state['value'] ?? null)
                || ! is_int($state['next_update_at'] ?? null)
            ) {
                $state = [
                    'value' => self::BASELINE_CEILING,
                    'next_update_at' => $now + self::BASELINE_UPDATE_SECONDS,
                ];
            } elseif ($state['next_update_at'] <= $now) {
                $direction = match (true) {
                    $state['value'] <= self::BASELINE_FLOOR => 1,
                    $state['value'] >= self::BASELINE_CEILING => -1,
                    default => random_int(0, 1) === 1 ? 1 : -1,
                };

                $state = [
                    'value' => max(
                        self::BASELINE_FLOOR,
                        min(self::BASELINE_CEILING, $state['value'] + $direction),
                    ),
                    'next_update_at' => $now + self::BASELINE_UPDATE_SECONDS,
                ];
            }

            Cache::put(
                self::BASELINE_CACHE_KEY,
                $state,
                now()->addMinutes(10),
            );

            return $state['value'];
        });

        return $baseline + max(0, $activeViewerCount);
    }
}
