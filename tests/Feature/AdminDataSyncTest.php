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

    public function test_admin_can_sync_drive_certificates(): void
    {
        Http::fake([
            'drive.google.com/drive/folders/*' => Http::response(<<<'HTML'
                <html><body>
                    <div data-id="1rwt2bAikR6gj7ot-kwBRGvbuvr5XmKAD" data-tooltip="Sangfor Network Security Endpoint.pdf PDF"></div>
                    <div data-id="adminsync12345678901234567890" data-tooltip="Responsible AI Workshop.pdf PDF"></div>
                </body></html>
                HTML),
        ]);

        $this->withSession($this->adminSession)
            ->post('/admin/sync/certificates')
            ->assertRedirect()
            ->assertSessionHas('success', 'Drive certificate sync complete: 1 new, 1 preserved, 2 found.');

        $this->assertDatabaseHas('certificates', [
            'source' => 'google_drive',
            'external_id' => 'adminsync12345678901234567890',
            'title' => 'Responsible AI Workshop',
        ]);
    }

    public function test_admin_can_sync_credly_badges(): void
    {
        Http::fake([
            '*credly.com/*' => Http::response([
                'data' => [[
                    'id' => 'admin-badge-1',
                    'issued_at' => '2026-07-01',
                    'public_url' => 'https://www.credly.com/badges/admin-badge-1',
                    'badge_template' => [
                        'name' => 'Responsible AI Foundations',
                        'issuer' => ['name' => 'Example Institute'],
                    ],
                ]],
            ]),
        ]);

        $this->withSession($this->adminSession)
            ->post('/admin/sync/badges')
            ->assertRedirect()
            ->assertSessionHas('success', 'Synced 1 Credly badge(s).');

        $this->assertDatabaseHas('credential_badges', [
            'provider' => 'credly',
            'external_id' => 'admin-badge-1',
            'name' => 'Responsible AI Foundations',
        ]);
        $this->assertDatabaseMissing('credential_badges', [
            'external_id' => 'credly-profile-brittm',
        ]);
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
