<?php

use App\Console\Commands\ImportDriveCertificates;
use App\Console\Commands\SyncCredlyBadges;
use App\Console\Commands\SyncGitHubContributions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ImportDriveCertificates::class)
    ->everyFiveMinutes()
    ->withoutOverlapping(10);

Schedule::command(SyncCredlyBadges::class)
    ->hourlyAt(10)
    ->withoutOverlapping(30);

Schedule::command(SyncGitHubContributions::class)
    ->hourlyAt(20)
    ->withoutOverlapping(30);
