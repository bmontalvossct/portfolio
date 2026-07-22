<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutomaticDataSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_all_external_sources_are_scheduled_without_overlapping(): void
    {
        $events = collect(app(Schedule::class)->events());

        $this->assertScheduled($events, 'profile:sync-drive-certificates', '*/5 * * * *');
        $this->assertScheduled($events, 'profile:sync-credly', '10 * * * *');
        $this->assertScheduled($events, 'profile:sync-github', '20 * * * *');
    }

    public function test_credly_command_uses_the_editable_profile_username(): void
    {
        Profile::query()->update(['credly_username' => 'custom-credly-user']);

        Http::fake([
            '*credly.com/users/custom-credly-user/*' => Http::response([
                'data' => [[
                    'id' => 'scheduled-badge-1',
                    'badge_template' => [
                        'name' => 'Scheduled Badge',
                        'issuer' => ['name' => 'Example Institute'],
                    ],
                ]],
            ]),
        ]);

        $this->artisan('profile:sync-credly')
            ->expectsOutputToContain('Synced 1 badge(s)')
            ->assertSuccessful();

        $this->assertDatabaseHas('credential_badges', [
            'external_id' => 'scheduled-badge-1',
            'name' => 'Scheduled Badge',
        ]);
    }

    public function test_github_command_refreshes_the_profile_contribution_cache(): void
    {
        Profile::query()->update(['github_username' => 'scheduled-github-user']);

        Http::fake([
            'github.com/users/*/contributions' => Http::response(<<<'HTML'
                <h2 id="js-contribution-activity-description">7 contributions in the last year</h2>
                <table>
                    <td data-date="2026-07-19" data-level="1"></td>
                    <td data-date="2026-07-20" data-level="3"></td>
                </table>
                HTML),
        ]);

        $this->artisan('profile:sync-github')
            ->expectsOutput('GitHub activity refreshed: 7 contribution(s) loaded.')
            ->assertSuccessful();

        $activity = Cache::get('portfolio.github.scheduled-github-user');

        $this->assertSame(7, $activity['total']);
        $this->assertSame('github', $activity['source']);
    }

    private function assertScheduled($events, string $command, string $expression): void
    {
        $event = $events->first(fn ($event): bool => str_contains($event->command, $command));

        $this->assertNotNull($event);
        $this->assertSame($expression, $event->expression);
        $this->assertTrue($event->withoutOverlapping);
    }
}
