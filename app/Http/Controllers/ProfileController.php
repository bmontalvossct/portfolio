<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\Education;
use App\Models\GuestbookEntry;
use App\Models\PortfolioTool;
use App\Models\Profile;
use App\Models\Project;
use App\Models\PublishedWork;
use App\Models\WorkExperience;
use App\Support\Profile\CertificateProvider;
use App\Support\Profile\CredentialCategory;
use App\Support\Profile\DesignMediaArchive;
use App\Support\Profile\GitHubContributionService;
use App\Support\Profile\PublicCertificateExposure;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __invoke(GitHubContributionService $github, DesignMediaArchive $mediaArchive): Response
    {
        $profile = Profile::query()->first();

        $badges = CredentialBadge::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('issued_at')
            ->get()
            ->map(function (CredentialBadge $badge): array {
                $category = CredentialCategory::classify($badge->category, [$badge->name, $badge->issuer, $badge->description, $badge->skills ?? []]);

                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'issuer' => $badge->issuer,
                    'category' => $category,
                    'category_label' => CredentialCategory::label($category),
                    'description' => $badge->description,
                    'issued_at' => $badge->issued_at?->format('M Y'),
                    'expires_at' => $badge->expires_at?->format('M Y'),
                    'image_url' => $badge->image_url,
                    'certificate_url' => $badge->certificate_url,
                    'criteria_url' => $badge->criteria_url,
                    'evidence_url' => $badge->evidence_url,
                    'skills' => $badge->skills ?? [],
                    'is_featured' => $badge->is_featured,
                ];
            });

        $publishedWorks = PublishedWork::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_on')
            ->get()
            ->map(fn (PublishedWork $work): array => [
                'id' => $work->id,
                'type' => $work->type,
                'type_label' => str($work->type)->replace('_', ' ')->title()->toString(),
                'title' => $work->title,
                'publication' => $work->publication,
                'role' => $work->role,
                'summary' => $work->summary,
                'published_on' => $work->published_on?->format('M Y'),
                'cover_url' => $work->cover_url,
                'cover_preview_url' => $this->portfolioPreviewUrl($work->cover_url, 320),
                'doi' => $work->doi,
                'external_url' => $work->external_url,
                'tags' => $work->tags ?? [],
                'is_featured' => $work->is_featured,
            ]);

        $projects = Project::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Project $project): array => [
                'id' => $project->id,
                'title' => $project->title,
                'category' => $project->category,
                'summary' => $project->summary,
                'year' => $project->year,
                'url' => $project->url,
                'repo_url' => $project->repo_url,
                'thumbnail_url' => $project->thumbnail_url,
                'tags' => $project->tags ?? [],
                'is_featured' => $project->is_featured,
            ]);

        $designMedia = $mediaArchive->all();

        $achievements = Achievement::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('achieved_on')
            ->get()
            ->map(fn (Achievement $achievement): array => [
                'id' => $achievement->id,
                'title' => $achievement->title,
                'issuer' => $achievement->issuer,
                'summary' => $achievement->summary,
                'achieved_on' => $achievement->achieved_on?->format('M Y'),
                'image_url' => $achievement->image_url,
                'external_url' => $achievement->external_url,
                'tags' => $achievement->tags ?? [],
                'is_featured' => $achievement->is_featured,
            ]);

        $certificates = Certificate::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('issued_on')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Certificate $certificate): array {
                $category = CredentialCategory::classify($certificate->category, [$certificate->title, $certificate->issuer, $certificate->description, $certificate->tags ?? []]);
                $provider = CertificateProvider::for($certificate);

                return [
                    'id' => $certificate->id,
                    'title' => $certificate->title,
                    'issuer' => $certificate->issuer,
                    'category' => $category,
                    'category_label' => CredentialCategory::label($category),
                    'description' => $certificate->description,
                    'issued_on' => $certificate->issued_on?->format('M Y'),
                    'file_url' => PublicCertificateExposure::verificationUrl($certificate->file_url),
                    'thumbnail_url' => $provider['logo_url'],
                    'provider_name' => $provider['name'],
                    'provider_initials' => $provider['initials'],
                    'verification_code' => $certificate->verification_code,
                    'tags' => $certificate->tags ?? [],
                    'is_featured' => $certificate->is_featured,
                ];
            });

        $education = Education::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Education $item): array => [
                'id' => $item->id,
                'institution' => $item->institution,
                'logo_url' => $item->logo_url,
                'program' => $item->program,
                'level' => $item->level,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'location' => $item->location,
                'description' => $item->description,
                'activities' => $item->activities ?? [],
            ]);

        $workExperiences = WorkExperience::query()
            ->orderByDesc('is_current')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (WorkExperience $item): array => [
                'id' => $item->id,
                'organization' => $item->organization,
                'logo_url' => $item->logo_url,
                'position' => $item->position,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'location' => $item->location,
                'summary' => $item->summary,
                'responsibilities' => $item->responsibilities ?? [],
                'is_current' => $item->is_current,
            ]);

        $portfolioTools = PortfolioTool::query()
            ->orderByRaw("case category when 'backend' then 1 when 'frontend' then 2 when 'automation' then 3 when 'data' then 4 when 'design' then 5 when 'infrastructure' then 6 when 'platforms' then 7 when 'development' then 8 else 9 end")
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (PortfolioTool $tool): array => [
                'id' => $tool->id,
                'name' => $tool->name,
                'category' => $tool->category,
                'description' => $tool->description,
                'icon_url' => $tool->icon_url,
                'external_url' => $tool->external_url,
                'is_featured' => $tool->is_featured,
            ]);

        $githubActivity = app()->runningUnitTests()
            ? null
            : $github->forUser($profile?->github_username);

        $guestbookEntries = GuestbookEntry::query()
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->orderByDesc('approved_at')
            ->limit(12)
            ->get()
            ->map(fn (GuestbookEntry $entry): array => [
                'public_id' => $entry->public_id,
                'name' => $entry->name,
                'role_or_organization' => $entry->role_or_organization,
                'body' => $entry->body,
                'rating' => $entry->rating,
                'admin_reply' => $entry->admin_reply,
                'approved_at' => $entry->approved_at?->format('M Y'),
            ]);

        return Inertia::render('Profile/Show', [
            'profile' => [
                'display_name' => $profile?->display_name ?? 'Britt Kristoff B. Montalvo, MSIT',
                'headline' => $profile?->headline ?? 'Information systems, research, publishing, and design.',
                'availability' => $profile?->availability,
                'location' => $profile?->location,
                'email' => $profile?->email,
                'bio' => $profile?->bio ?? '',
                'avatar_url' => $profile?->avatar_url,
                'credly_username' => $profile?->credly_username ?? 'brittm',
                'github_username' => $profile?->github_username,
                'canva_url' => $profile?->canva_url,
                'external_links' => $profile?->external_links ?? [],
                'highlights' => $profile?->highlights ?? [],
                'skills' => $profile?->skills ?? [],
                'stats' => [
                    ['label' => 'verified badges', 'value' => $badges->count()],
                    ['label' => 'certificates', 'value' => $certificates->count()],
                    ['label' => 'works', 'value' => $projects->count() + $publishedWorks->count() + $designMedia->count()],
                ],
            ],
            'achievements' => $achievements,
            'badges' => $badges,
            'certificates' => $certificates,
            'designMedia' => $designMedia,
            'education' => $education,
            'githubActivity' => $githubActivity,
            'guestbookEntries' => $guestbookEntries,
            'projects' => $projects,
            'portfolioTools' => $portfolioTools,
            'publishedWorks' => $publishedWorks,
            'workExperiences' => $workExperiences,
        ]);
    }

    private function portfolioPreviewUrl(?string $mediaUrl, int $width): ?string
    {
        if (! $mediaUrl || ! str_starts_with($mediaUrl, '/storage/portfolio/')) {
            return null;
        }

        return route('portfolio-thumbnail', [
            'src' => $mediaUrl,
            'w' => $width,
        ], false);
    }
}
