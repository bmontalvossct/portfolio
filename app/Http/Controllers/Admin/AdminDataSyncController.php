<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CredentialBadge;
use App\Models\Profile;
use App\Support\Profile\CredlyBadgeSyncer;
use App\Support\Profile\GitHubContributionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class AdminDataSyncController extends Controller
{
    public function certificates(): RedirectResponse
    {
        $exitCode = Artisan::call('profile:sync-drive-certificates');
        $message = trim(Artisan::output());

        if ($exitCode !== 0) {
            return back()->with('error', $message ?: 'Google Drive certificate sync failed.');
        }

        return back()->with('success', $message ?: 'Google Drive certificates synced.');
    }

    public function badges(CredlyBadgeSyncer $syncer): RedirectResponse
    {
        $username = trim((string) Profile::query()->value('credly_username'));

        if ($username === '') {
            return back()->with('error', 'Add a Credly username to the profile before syncing badges.');
        }

        try {
            $result = $syncer->sync($username);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Credly badge sync failed.');
        }

        if ($result['synced'] === 0) {
            return back()->with('error', $result['message']);
        }

        CredentialBadge::query()
            ->where('provider', 'credly')
            ->where('external_id', 'credly-profile-brittm')
            ->delete();

        return back()->with('success', "Synced {$result['synced']} Credly badge(s).");
    }

    public function github(GitHubContributionService $github): RedirectResponse
    {
        $username = Profile::query()->value('github_username');
        $activity = $github->refreshForUser(is_string($username) ? $username : null);

        if ($activity === null) {
            return back()->with('error', 'Add a valid GitHub username to the profile before refreshing activity.');
        }

        if (($activity['source'] ?? null) !== 'github') {
            return back()->with('error', 'GitHub could not be reached, so the live activity cache was not replaced.');
        }

        $total = is_int($activity['total'] ?? null) ? $activity['total'] : 0;

        return back()->with('success', "GitHub activity refreshed: {$total} contribution(s) loaded.");
    }
}
