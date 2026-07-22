<?php

namespace App\Support\Profile;

use App\Models\CredentialBadge;
use Carbon\Carbon;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CredlyBadgeSyncer
{
    public function sync(string $username, ?string $file = null, bool $mirrorImages = true): array
    {
        $result = $file
            ? $this->payloadFromFile($file)
            : $this->fetchPayload($username);

        if ($result['payload'] === null) {
            return [
                'source' => null,
                'synced' => 0,
                'message' => 'No public Credly JSON payload was available.',
            ];
        }

        $records = collect($this->extractBadges($result['payload']))
            ->map(fn (array $badge, int $index): ?array => $this->mapBadge($badge, $username, $index, $mirrorImages))
            ->filter()
            ->values();

        $records->each(function (array $record): void {
            CredentialBadge::query()->updateOrCreate(
                [
                    'provider' => 'credly',
                    'external_id' => $record['external_id'],
                ],
                Arr::except($record, ['external_id'])
            );
        });

        return [
            'source' => $result['source'],
            'synced' => $records->count(),
            'message' => $records->isEmpty()
                ? 'Credly responded, but no badge records were recognized.'
                : 'Credly badges synced.',
        ];
    }

    private function payloadFromFile(string $file): array
    {
        $path = base_path($file);

        if (! File::exists($path)) {
            $path = $file;
        }

        if (! File::exists($path)) {
            return ['source' => null, 'payload' => null];
        }

        $payload = json_decode(File::get($path), true);

        return [
            'source' => $path,
            'payload' => is_array($payload) ? $payload : null,
        ];
    }

    private function fetchPayload(string $username): array
    {
        foreach ($this->candidateUrls($username) as $url) {
            try {
                $response = Http::acceptJson()
                    ->withUserAgent('Mozilla/5.0 ProfilePortfolio/1.0')
                    ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                    ->timeout(20)
                    ->retry(2, 300)
                    ->get($url);

                if (! $response->ok()) {
                    continue;
                }

                $payload = $response->json();

                if (is_array($payload)) {
                    return ['source' => $url, 'payload' => $payload];
                }

                $embedded = $this->extractEmbeddedJson($response->body());

                if ($embedded !== null) {
                    return ['source' => $url, 'payload' => $embedded];
                }
            } catch (Throwable) {
                continue;
            }
        }

        return ['source' => null, 'payload' => null];
    }

    private function candidateUrls(string $username): array
    {
        $slug = rawurlencode($username);

        return [
            "https://www.credly.com/users/{$slug}/badges.json",
            "https://www.credly.com/users/{$slug}/badges?format=json",
            "https://www.credly.com/api/v1/users/{$slug}/badges",
            "https://www.credly.com/users/{$slug}",
        ];
    }

    private function extractEmbeddedJson(string $html): ?array
    {
        if (preg_match('/<script[^>]+id=["\']__NEXT_DATA__["\'][^>]*>(.*?)<\/script>/is', $html, $matches) !== 1) {
            return null;
        }

        $decoded = json_decode(html_entity_decode($matches[1]), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function extractBadges(array $payload): array
    {
        $direct = data_get($payload, 'data')
            ?? data_get($payload, 'badges')
            ?? data_get($payload, 'props.pageProps.badges')
            ?? data_get($payload, 'page.props.badges');

        if ($this->isList($direct)) {
            return $direct;
        }

        $found = [];
        $this->walkForBadges($payload, $found);

        return array_values($found);
    }

    private function walkForBadges(mixed $value, array &$found): void
    {
        if (! is_array($value)) {
            return;
        }

        if ($this->looksLikeBadge($value)) {
            $found[] = $value;

            return;
        }

        foreach ($value as $child) {
            $this->walkForBadges($child, $found);
        }
    }

    private function looksLikeBadge(array $value): bool
    {
        $name = data_get($value, 'name')
            ?? data_get($value, 'title')
            ?? data_get($value, 'badge_template.name')
            ?? data_get($value, 'badge.name');

        $visual = data_get($value, 'image_url')
            ?? data_get($value, 'image.url')
            ?? data_get($value, 'badge_template.image_url')
            ?? data_get($value, 'badge_template.image.url')
            ?? data_get($value, 'badge.image_url');

        return is_string($name) && (
            is_string($visual)
            || data_get($value, 'issuer') !== null
            || data_get($value, 'badge_template.issuer') !== null
        );
    }

    private function mapBadge(array $raw, string $username, int $index, bool $mirrorImages): ?array
    {
        $template = data_get($raw, 'badge_template') ?? data_get($raw, 'badge') ?? $raw;
        $name = data_get($template, 'name') ?? data_get($raw, 'name') ?? data_get($raw, 'title');

        if (! is_string($name) || trim($name) === '') {
            return null;
        }

        $issuedAt = $this->dateValue(data_get($raw, 'issued_at') ?? data_get($raw, 'issued_to_recipient_at'));
        $id = data_get($raw, 'id') ?? data_get($raw, 'uuid') ?? data_get($raw, 'slug');
        $externalId = is_scalar($id) ? (string) $id : sha1($username.'|'.$name.'|'.($issuedAt ?? $index));
        $remoteImageUrl = $this->firstString([
            data_get($raw, 'image_url'),
            data_get($raw, 'image.url'),
            data_get($template, 'image_url'),
            data_get($template, 'image.url'),
        ]);
        $assertionUrl = is_scalar($id) ? "https://www.credly.com/badges/{$id}" : null;

        return [
            'external_id' => $externalId,
            'name' => $this->cleanText($name),
            'issuer' => $this->cleanText($this->firstString([
                data_get($raw, 'issuer.name'),
                data_get($template, 'issuer.name'),
                data_get($raw, 'issuer.entities.0.entity.name'),
                data_get($template, 'issuer.entities.0.entity.name'),
                data_get($raw, 'organization.name'),
            ])),
            'category' => CredentialCategory::classify(null, [
                $name,
                data_get($template, 'description'),
                data_get($raw, 'description'),
                $this->skills(data_get($raw, 'skills') ?? data_get($template, 'skills')),
            ]),
            'description' => $this->cleanText($this->firstString([
                data_get($template, 'description'),
                data_get($raw, 'description'),
            ])),
            'issued_at' => $issuedAt,
            'expires_at' => $this->dateValue(data_get($raw, 'expires_at') ?? data_get($raw, 'expires_at_date') ?? data_get($raw, 'expires_on')),
            'image_url' => $mirrorImages ? $this->mirrorImage($remoteImageUrl, $externalId) ?? $remoteImageUrl : $remoteImageUrl,
            'certificate_url' => $this->firstString([
                data_get($raw, 'public_url'),
                data_get($raw, 'url'),
                data_get($raw, 'badge_url'),
                $assertionUrl,
            ]) ?? "https://www.credly.com/users/{$username}",
            'criteria_url' => $this->firstString([
                data_get($template, 'criteria_url'),
                data_get($template, 'criteria.url'),
                data_get($raw, 'criteria_url'),
            ]),
            'evidence_url' => $this->firstString([
                data_get($raw, 'evidence_url'),
                data_get($raw, 'evidence.0.url'),
            ]),
            'skills' => $this->skills(data_get($raw, 'skills') ?? data_get($template, 'skills')),
            'sort_order' => $index,
            'is_featured' => $index < 3,
            'synced_at' => now(),
        ];
    }

    private function firstString(array $values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_replace(
            ['â€œ', 'â€�', 'â€', 'â€˜', 'â€™', 'â€“', 'â€”', 'â€¢', 'Â'],
            ['"', '"', '"', "'", "'", '-', '-', '*', ''],
            $value
        );
    }

    private function mirrorImage(?string $url, string $externalId): ?string
    {
        if ($url === null) {
            return null;
        }

        $baseName = Str::of($externalId)->replaceMatches('/[^A-Za-z0-9_-]/', '-')->lower()->toString();
        $directory = public_path('imports/credly');

        foreach (['svg', 'png'] as $existingExtension) {
            if (File::exists($directory.DIRECTORY_SEPARATOR.$baseName.'.'.$existingExtension)) {
                return '/imports/credly/'.$baseName.'.'.$existingExtension;
            }
        }

        try {
            $response = Http::withUserAgent('Mozilla/5.0 ProfilePortfolio/1.0')
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->timeout(25)
                ->retry(2, 300)
                ->get($url);

            if (! $response->ok()) {
                return null;
            }

            $extension = str_contains((string) $response->header('Content-Type'), 'svg')
                ? 'svg'
                : 'png';
            $fileName = $baseName.'.'.$extension;

            File::ensureDirectoryExists($directory);
            File::put($directory.DIRECTORY_SEPARATOR.$fileName, $response->body());

            return '/imports/credly/'.$fileName;
        } catch (Throwable) {
            return null;
        }
    }

    private function dateValue(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function skills(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(function (mixed $skill): ?string {
                if (is_string($skill)) {
                    return $skill;
                }

                if (is_array($skill)) {
                    return data_get($skill, 'name') ?? data_get($skill, 'title');
                }

                return null;
            })
            ->filter()
            ->map(fn (string $skill): string => $this->cleanText(Str::of($skill)->trim()->toString()))
            ->unique()
            ->values()
            ->all();
    }

    private function isList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }
}
