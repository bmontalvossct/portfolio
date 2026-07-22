<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Support\Profile\GitHubContributionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('profile:sync-github
    {username? : GitHub username; defaults to the profile setting}')]
#[Description('Refresh the cached GitHub contribution activity for the public portfolio.')]
class SyncGitHubContributions extends Command
{
    public function handle(GitHubContributionService $github): int
    {
        $username = trim((string) ($this->argument('username') ?: Profile::query()->value('github_username')));

        if ($username === '') {
            $this->error('Add a GitHub username to the profile before syncing contribution activity.');

            return self::FAILURE;
        }

        $activity = $github->refreshForUser($username);

        if (($activity['source'] ?? null) !== 'github') {
            $this->error('GitHub could not be reached, so the live activity cache was not replaced.');

            return self::FAILURE;
        }

        $total = is_int($activity['total'] ?? null) ? $activity['total'] : 0;

        $this->info("GitHub activity refreshed: {$total} contribution(s) loaded.");

        return self::SUCCESS;
    }
}
