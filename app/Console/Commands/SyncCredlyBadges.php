<?php

namespace App\Console\Commands;

use App\Models\CredentialBadge;
use App\Models\Profile;
use App\Support\Profile\CredlyBadgeSyncer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('profile:sync-credly
    {username? : Credly public username or slug; defaults to the profile setting}
    {--file= : Import from a downloaded Credly JSON payload}
    {--skip-images : Keep remote Credly image URLs instead of mirroring images locally}')]
#[Description('Sync public Credly badge metadata into local profile records.')]
class SyncCredlyBadges extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CredlyBadgeSyncer $syncer): int
    {
        $username = trim((string) ($this->argument('username') ?: Profile::query()->value('credly_username')));

        if ($username === '') {
            $this->error('Add a Credly username to the profile before syncing badges.');

            return self::FAILURE;
        }

        $result = $syncer->sync(
            $username,
            $this->option('file') ? (string) $this->option('file') : null,
            ! $this->option('skip-images')
        );

        if ($result['synced'] === 0) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        CredentialBadge::query()
            ->where('provider', 'credly')
            ->where('external_id', 'credly-profile-brittm')
            ->delete();

        $this->info("Synced {$result['synced']} badge(s) from {$result['source']}.");

        return self::SUCCESS;
    }
}
