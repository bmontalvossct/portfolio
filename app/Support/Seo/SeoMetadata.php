<?php

namespace App\Support\Seo;

use App\Models\Profile;
use Illuminate\Http\Request;

class SeoMetadata
{
    public function forRequest(Request $request): array
    {
        if ($request->is('admin') || $request->is('admin/*')) {
            return [
                'client_title' => 'Portfolio admin',
                'title' => 'Portfolio admin',
                'description' => 'Private portfolio administration.',
                'canonical' => null,
                'robots' => 'noindex, nofollow, noarchive',
                'type' => 'website',
                'image' => null,
                'image_alt' => null,
                'locale' => config('seo.locale', 'en_PH'),
                'site_name' => config('seo.site_name', 'Britt Montalvo'),
                'schema' => null,
            ];
        }

        $path = '/'.trim($request->path(), '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        $page = config("seo.pages.{$path}") ?? config('seo.pages./');
        $profile = Profile::query()->first();
        $baseUrl = rtrim((string) config('seo.base_url', config('app.url')), '/');
        $canonical = $baseUrl.($path === '/' ? '/' : $path);
        $image = $this->absoluteUrl($profile?->avatar_url, $baseUrl)
            ?? config('seo.default_image');

        return [
            ...$page,
            'canonical' => $canonical,
            'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'image' => $image,
            'image_alt' => $profile?->display_name ? "{$profile->display_name} portrait" : 'Britt Montalvo',
            'locale' => config('seo.locale', 'en_PH'),
            'site_name' => config('seo.site_name', 'Britt Montalvo'),
            'schema' => $this->schemaFor($path, $canonical, $baseUrl, $profile),
        ];
    }

    private function schemaFor(string $path, string $canonical, string $baseUrl, ?Profile $profile): array
    {
        $person = $this->personSchema($baseUrl, $profile);
        $website = [
            '@type' => 'WebSite',
            '@id' => $baseUrl.'/#website',
            'url' => $baseUrl.'/',
            'name' => config('seo.site_name', 'Britt Montalvo'),
            'description' => config('seo.pages./.description'),
            'inLanguage' => 'en-PH',
            'publisher' => ['@id' => $baseUrl.'/#person'],
        ];

        if ($path === '/') {
            return [
                '@context' => 'https://schema.org',
                '@graph' => [
                    $website,
                    [
                        '@type' => 'ProfilePage',
                        '@id' => $baseUrl.'/#profile-page',
                        'url' => $canonical,
                        'name' => config('seo.pages./.title'),
                        'description' => config('seo.pages./.description'),
                        'inLanguage' => 'en-PH',
                        'isPartOf' => ['@id' => $baseUrl.'/#website'],
                        'mainEntity' => ['@id' => $baseUrl.'/#person'],
                    ],
                    $person,
                ],
            ];
        }

        $pageType = in_array($path, ['/designs', '/badges', '/certifications'], true)
            ? 'CollectionPage'
            : 'WebPage';
        $graph = [
            $website,
            [
                '@type' => $pageType,
                '@id' => $canonical.'#webpage',
                'url' => $canonical,
                'name' => config("seo.pages.{$path}.title"),
                'description' => config("seo.pages.{$path}.description"),
                'inLanguage' => 'en-PH',
                'isPartOf' => ['@id' => $baseUrl.'/#website'],
                'about' => ['@id' => $baseUrl.'/#person'],
            ],
            $this->breadcrumbSchema($path, $canonical, $baseUrl),
            $person,
        ];

        if ($path === '/services') {
            $services = collect(config('service_catalog', []))
                ->values()
                ->map(fn (array $service, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Service',
                        '@id' => $canonical.'#'.$service['key'],
                        'url' => $canonical.'#'.$service['key'],
                        'name' => $service['title'],
                        'description' => $service['summary'],
                        'provider' => ['@id' => $baseUrl.'/#person'],
                        'areaServed' => [
                            '@type' => 'Country',
                            'name' => 'Philippines',
                        ],
                    ],
                ])
                ->all();

            $graph[1]['mainEntity'] = ['@id' => $canonical.'#service-catalog'];
            $graph[] = [
                '@type' => 'ItemList',
                '@id' => $canonical.'#service-catalog',
                'name' => 'Professional services by Britt Montalvo',
                'numberOfItems' => count($services),
                'itemListElement' => $services,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    private function personSchema(string $baseUrl, ?Profile $profile): array
    {
        $displayName = $profile?->display_name ?? 'Britt Kristoff B. Montalvo, MSIT';
        $name = preg_replace('/,\s*MSIT$/i', '', $displayName) ?: $displayName;
        $sameAs = collect($profile?->external_links ?? [])
            ->pluck('url')
            ->filter()
            ->values()
            ->all();

        return array_filter([
            '@type' => 'Person',
            '@id' => $baseUrl.'/#person',
            'name' => $name,
            'alternateName' => 'Britt Montalvo',
            'honorificSuffix' => 'MSIT',
            'url' => $baseUrl.'/',
            'image' => $this->absoluteUrl($profile?->avatar_url, $baseUrl),
            'email' => $profile?->email ? 'mailto:'.$profile->email : null,
            'jobTitle' => $profile?->headline,
            'description' => $profile?->bio,
            'homeLocation' => $profile?->location ? [
                '@type' => 'Place',
                'name' => $profile->location,
            ] : null,
            'knowsAbout' => $profile?->skills ?? [],
            'sameAs' => $sameAs,
        ], fn (mixed $value): bool => $value !== null && $value !== []);
    }

    private function breadcrumbSchema(string $path, string $canonical, string $baseUrl): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Portfolio',
                    'item' => $baseUrl.'/',
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => config("seo.pages.{$path}.client_title"),
                    'item' => $canonical,
                ],
            ],
        ];
    }

    private function absoluteUrl(?string $url, string $baseUrl): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $baseUrl.'/'.ltrim($url, '/');
    }
}
