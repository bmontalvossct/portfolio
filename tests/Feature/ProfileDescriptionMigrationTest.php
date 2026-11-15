<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileDescriptionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_description_migration_updates_only_the_known_previous_bio(): void
    {
        $old = 'I am a DICT Registered Cybersecurity Professional (Intermediate Level 2) and Information Systems Analyst working across software engineering, health technology implementation, network security, and visual publishing. Creator of ClassCheck, an open-source classroom management system.';
        $new = 'Specializing in full-stack web development, cybersecurity, network infrastructure, AI-powered solutions, data analytics, and workflow automation. Helping businesses, educational institutions, and organizations build secure, scalable, and user-friendly digital solutions that streamline operations, improve efficiency, and support digital transformation.';

        $profile = Profile::query()->create([
            'display_name' => 'Britt Montalvo',
            'slug' => 'britt-m',
            'headline' => 'Information systems',
            'bio' => $old,
        ]);

        $migration = require database_path('migrations/2026_11_04_000001_update_portfolio_profile_description.php');
        $migration->up();
        $this->assertSame($new, $profile->fresh()->bio);

        $profile->update(['bio' => 'A later custom description.']);
        $migration->up();
        $migration->down();
        $this->assertSame('A later custom description.', $profile->fresh()->bio);
    }
}
