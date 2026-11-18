<?php

namespace App\Http\Controllers;

use App\Support\Profile\VersionedPortfolioContent;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $baseUrl = rtrim((string) config('seo.base_url', config('app.url')), '/');
        $content = app(VersionedPortfolioContent::class);
        $profile = $content->get('profile');

        $urls = [
            [
                'loc' => $baseUrl.'/',
                // A database timestamp cannot represent changes to versioned content.
                'lastmod' => null,
                'images' => $this->localImages(
                    collect()
                        ->push([
                            'url' => $profile['avatar_url'] ?? null,
                            'title' => $profile['display_name'] ?? null,
                        ])
                        ->concat(collect($content->get('projects'))->map(
                            fn (array $project): array => ['url' => $project['thumbnail_url'] ?? null, 'title' => $project['title'] ?? null],
                        ))
                        ->concat(collect($content->get('publishedWorks'))->map(
                            fn (array $work): array => ['url' => $work['cover_url'] ?? null, 'title' => $work['title'] ?? null],
                        ))
                        ->concat([
                            ['url' => '/creative-assets/magazine/page-0001.jpg', 'title' => 'Digital magazine cover'],
                            ['url' => '/creative-assets/graphic-io.jpg', 'title' => 'IO graphic design'],
                            ['url' => '/creative-assets/graphic-el-corso-2.jpg', 'title' => 'El Corso dimsum graphic design'],
                            ['url' => '/creative-assets/graphic-el-corso.jpg', 'title' => 'El Corso whisky graphic design'],
                            ['url' => '/creative-assets/palakasan-preview.png', 'title' => 'Video production preview'],
                        ]),
                    $baseUrl,
                ),
            ],
            [
                'loc' => $baseUrl.'/services',
                'lastmod' => null,
                'images' => [],
            ],
            [
                'loc' => $baseUrl.'/badges',
                'lastmod' => null,
                'images' => [],
            ],
            [
                'loc' => $baseUrl.'/certifications',
                'lastmod' => null,
                'images' => [],
            ],
        ];

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * @param  Collection<int, array{url: ?string, title: ?string}>  $items
     * @return array<int, array{loc: string, title: ?string}>
     */
    private function localImages(Collection $items, string $baseUrl): array
    {
        return $items
            ->filter(fn (array $item): bool => is_string($item['url'])
                && str_starts_with($item['url'], '/')
                && ! str_starts_with($item['url'], '//'))
            ->map(fn (array $item): array => [
                'loc' => $baseUrl.$item['url'],
                'title' => $item['title'],
            ])
            ->unique('loc')
            ->values()
            ->all();
    }
}
