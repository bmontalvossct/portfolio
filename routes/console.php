<?php

use App\Console\Commands\SyncGitHubContributions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Credentials are published from repository content; only live GitHub activity is refreshed.
Schedule::command(SyncGitHubContributions::class)
    ->hourlyAt(20)
    ->withoutOverlapping(30);
