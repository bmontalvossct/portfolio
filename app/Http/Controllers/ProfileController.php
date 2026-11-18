<?php

namespace App\Http\Controllers;

use App\Support\Profile\GitHubContributionService;
use App\Support\Profile\HomepagePageViews;
use App\Support\Profile\VersionedPortfolioContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __invoke(Request $request, GitHubContributionService $github, HomepagePageViews $pageViews): Response
    {
        $content = app(VersionedPortfolioContent::class);
        $profile = (object) $content->get('profile');

        $badges = collect($content->get('badges'));

        $publishedWorks = collect($content->get('publishedWorks'));

        $projects = collect($content->get('projects'));

        $achievements = collect($content->get('achievements'));

        $certificates = collect($content->get('certificates'));

        $education = collect($content->get('education'));

        $workExperiences = collect($content->get('workExperiences'));

        $portfolioTools = collect($content->get('portfolioTools'));

        $githubActivity = app()->runningUnitTests()
            ? null
            : $github->forUser($profile?->github_username);

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
                    ['label' => 'works', 'value' => $projects->count() + $publishedWorks->count()],
                ],
            ],
            'achievements' => $achievements,
            'badges' => $badges,
            'certificates' => $certificates,
            'education' => $education,
            'githubActivity' => $githubActivity,
            'projects' => $projects,
            'portfolioTools' => $portfolioTools,
            'publishedWorks' => $publishedWorks,
            'workExperiences' => $workExperiences,
            'visitStats' => $pageViews->recordAndStats($request),
        ]);
    }

}
