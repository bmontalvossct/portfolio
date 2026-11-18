<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VersionedAdminProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_versioned_content_writes_and_external_sync_are_rejected_after_authentication(): void
    {
        $badgeCount = \App\Models\CredentialBadge::count();
        $projectCount = \App\Models\Project::count();
        $this->put('/admin/profile', [])->assertRedirect('/admin/login');
        $this->withSession(['portfolio_admin_authenticated' => true]);
        $this->put('/admin/profile', [])->assertStatus(409)->assertSee('managed in the repository');
        foreach (['achievements', 'projects', 'design-media', 'portfolio-tools', 'published-works', 'work-experiences', 'education', 'credential-badges', 'certificates'] as $path) {
            $this->post('/admin/'.$path, [])->assertStatus(409);
        }
        $this->post('/admin/sync/badges')->assertStatus(409);
        $this->post('/admin/sync/certificates')->assertStatus(409);
        $this->assertDatabaseCount('credential_badges', $badgeCount);
        $this->assertDatabaseCount('projects', $projectCount);
    }

    public function test_all_update_and_delete_routes_have_protection_while_live_routes_do_not(): void
    {
        foreach (['achievements', 'projects', 'design-media', 'portfolio-tools', 'published-works', 'work-experiences', 'education', 'credential-badges', 'certificates'] as $name) {
            foreach (['store', 'update', 'destroy'] as $action) {
                $this->assertContains(\App\Http\Middleware\PreventVersionedContentMutation::class, app('router')->getRoutes()->getByName('admin.'.$name.'.'.$action)->gatherMiddleware());
            }
        }
        foreach (['admin.sync.github', 'admin.guestbook.update', 'admin.logout'] as $name) {
            $this->assertNotContains(\App\Http\Middleware\PreventVersionedContentMutation::class, app('router')->getRoutes()->getByName($name)->gatherMiddleware());
        }
    }
}
