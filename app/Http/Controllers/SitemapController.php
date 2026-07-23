<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\DesignMedia;
use App\Models\Education;
use App\Models\GuestbookEntry;
use App\Models\PortfolioTool;
use App\Models\Profile;
use App\Models\Project;
use App\Models\PublishedWork;
use App\Models\WorkExperience;
use App\Support\Profile\DesignMediaArchive;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(DesignMediaArchive $designMediaArchive): Response
    {
        $baseUrl = rtrim((string) config('seo.base_url', config('app.url')), '/');
        $designMedia = $designMediaArchive->all();

        $urls = [
            [
                'loc' => $baseUrl.'/',
                'lastmod' => $this->latest([
                    Profile::query(),
                    Achievement::query(),
                    Project::query(),
                    PublishedWork::query(),
                    Education::query(),
                    WorkExperience::query(),
                    PortfolioTool::query(),
                    GuestbookEntry::query()->where('status', 'approved')->whereNotNull('approved_at'),
                    Certificate::query(),
                    CredentialBadge::query(),
                    DesignMedia::query(),
                ]),
                'images' => $this->localImages(
                    collect()
                        ->push([
                            'url' => Profile::query()->value('avatar_url'),
                            'title' => Profile::query()->value('display_name'),
                        ])
                        ->concat(Project::query()->get(['title', 'thumbnail_url'])->map(
                            fn (Project $project): array => ['url' => $project->thumbnail_url, 'title' => $project->title],
                        ))
                        ->concat(PublishedWork::query()->get(['title', 'cover_url'])->map(
                            fn (PublishedWork $work): array => ['url' => $work->cover_url, 'title' => $work->title],
                        ))
                        ->concat($designMedia->map(
                            fn (array $item): array => [
                                'url' => $item['thumbnail_url'] ?: ($item['media_type'] === 'image' ? $item['media_url'] : null),
                                'title' => $item['title'],
                            ],
                        )),
                    $baseUrl,
                ),
            ],
            [
                'loc' => $baseUrl.'/services',
                'lastmod' => null,
                'images' => [],
            ],
            [
                'loc' => $baseUrl.'/designs',
                'lastmod' => $this->latest([DesignMedia::query()]),
                'images' => $this->localImages(
                    $designMedia->map(
                        fn (array $item): array => [
                            'url' => $item['thumbnail_url'] ?: ($item['media_type'] === 'image' ? $item['media_url'] : null),
                            'title' => $item['title'],
                        ],
                    ),
                    $baseUrl,
                ),
            ],
            [
                'loc' => $baseUrl.'/badges',
                'lastmod' => $this->latest([CredentialBadge::query()]),
                'images' => [],
            ],
            [
                'loc' => $baseUrl.'/certifications',
                'lastmod' => $this->latest([Certificate::query()]),
                'images' => [],
            ],
        ];

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * @param  array<int, Builder>  $queries
     */
    private function latest(array $queries): ?string
    {
        $latest = collect($queries)
            ->map(fn (Builder $query): mixed => $query->max('updated_at'))
            ->filter()
            ->map(fn (mixed $timestamp): Carbon => Carbon::parse($timestamp))
            ->sortDesc()
            ->first();

        return $latest?->toAtomString();
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
