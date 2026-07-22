<?php

namespace App\Support\Profile;

use Carbon\CarbonImmutable;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class GitHubContributionService
{
    public function forUser(?string $username): ?array
    {
        if (! $this->validUsername($username)) {
            return null;
        }

        return Cache::remember(
            $this->cacheKey($username),
            now()->addHours(config('portfolio.github_cache_hours', 6)),
            fn (): ?array => $this->fetch($username),
        );
    }

    public function refreshForUser(?string $username): ?array
    {
        if (! $this->validUsername($username)) {
            return null;
        }

        $activity = $this->fetch($username);

        if (($activity['source'] ?? null) === 'github') {
            Cache::put(
                $this->cacheKey($username),
                $activity,
                now()->addHours(config('portfolio.github_cache_hours', 6)),
            );
        }

        return $activity;
    }

    private function fetch(string $username): ?array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'text/html',
                'User-Agent' => 'Britt-Portfolio/1.0',
            ])
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->timeout(15)
                ->get("https://github.com/users/{$username}/contributions");

            if (! $response->ok()) {
                return $this->fallback($username);
            }

            $activity = $this->parse($response->body(), $username);

            if ($activity !== null) {
                $activity['source'] = 'github';
            }

            return $activity;
        } catch (Throwable) {
            return $this->fallback($username);
        }
    }

    private function parse(string $html, string $username): ?array
    {
        preg_match_all('/data-date="(\d{4}-\d{2}-\d{2})"[^>]*data-level="([0-4])"/i', $html, $matches, PREG_SET_ORDER);

        if ($matches === []) {
            return null;
        }

        $days = collect($matches)
            ->map(fn (array $match): array => [
                'date' => $match[1],
                'level' => (int) $match[2],
            ])
            ->unique('date')
            ->sortBy('date')
            ->values();

        $weeks = $days
            ->groupBy(fn (array $day): string => CarbonImmutable::parse($day['date'])
                ->startOfWeek(CarbonImmutable::SUNDAY)
                ->toDateString())
            ->map(fn ($week, string $startDate): array => [
                'start_date' => $startDate,
                'days' => $week->values()->all(),
            ])
            ->values()
            ->all();

        preg_match('/<h2[^>]*id="js-contribution-activity-description"[^>]*>\s*([\d,]+)/is', $html, $totalMatch);

        return [
            'username' => $username,
            'profile_url' => "https://github.com/{$username}",
            'total' => isset($totalMatch[1]) ? (int) str_replace(',', '', $totalMatch[1]) : null,
            'from' => $days->first()['date'],
            'to' => $days->last()['date'],
            'weeks' => $weeks,
        ];
    }

    private function fallback(string $username): array
    {
        $snapshot = storage_path("app/imports/github-contributions-{$username}.html");

        if (File::exists($snapshot)) {
            $parsed = $this->parse(File::get($snapshot), $username);

            if ($parsed !== null) {
                $parsed['source'] = 'snapshot';

                return $parsed;
            }
        }

        $start = CarbonImmutable::now()->subYear()->startOfWeek(CarbonImmutable::SUNDAY);
        $end = CarbonImmutable::now();
        $days = collect();

        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            $days->push(['date' => $date->toDateString(), 'level' => 0]);
        }

        return [
            'username' => $username,
            'source' => 'empty',
            'profile_url' => "https://github.com/{$username}",
            'total' => 0,
            'from' => $days->first()['date'],
            'to' => $days->last()['date'],
            'weeks' => $days
                ->chunk(7)
                ->map(fn ($week): array => [
                    'start_date' => $week->first()['date'],
                    'days' => $week->values()->all(),
                ])
                ->values()
                ->all(),
        ];
    }

    private function validUsername(?string $username): bool
    {
        return is_string($username)
            && preg_match('/\A[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?\z/', $username) === 1;
    }

    private function cacheKey(string $username): string
    {
        return 'portfolio.github.'.strtolower($username);
    }
}
