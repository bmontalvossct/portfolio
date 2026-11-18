<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminDataSyncTest extends TestCase
{
    use RefreshDatabase;

    private array $adminSession = ['portfolio_admin_authenticated' => true];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_sync_actions_require_admin_authentication(): void
    {
        $this->post('/admin/sync/certificates')->assertRedirect('/admin/login');
        $this->post('/admin/sync/badges')->assertRedirect('/admin/login');
        $this->post('/admin/sync/github')->assertRedirect('/admin/login');
    }

    public function test_versioned_certificates_cannot_be_synced_from_admin(): void
    {
        Http::fake();
        $this->withSession($this->adminSession)->post('/admin/sync/certificates')->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_versioned_badges_cannot_be_synced_from_admin(): void
    {
        Http::fake();
        $this->withSession($this->adminSession)->post('/admin/sync/badges')->assertStatus(409);
        Http::assertNothingSent();
    }

    public function test_admin_can_refresh_the_github_contribution_cache(): void
    {
        Cache::put('portfolio.github.bmontalvossct', ['total' => 999], now()->addHour());

        Http::fake([
            'github.com/users/*/contributions' => Http::response(<<<'HTML'
                <h2 id="js-contribution-activity-description">12 contributions in the last year</h2>
                <table>
                    <td class="ContributionCalendar-day" data-date="2026-07-12" data-level="0"></td>
                    <td class="ContributionCalendar-day" data-date="2026-07-13" data-level="3"></td>
                </table>
                HTML),
        ]);

        $this->withSession($this->adminSession)
            ->post('/admin/sync/github')
            ->assertRedirect()
            ->assertSessionHas('success', 'GitHub activity refreshed: 12 contribution(s) loaded.');

        $activity = Cache::get('portfolio.github.bmontalvossct');

        $this->assertSame(12, $activity['total']);
        $this->assertSame('github', $activity['source']);
    }
}
