<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_ordered_gallery_and_public_page_receives_it(): void
    {
        $this->withoutVite();
        $session = ['portfolio_admin_authenticated' => true];
        $this->withSession($session)->post('/admin/projects', [
            'title' => 'Gallery project',
            'gallery_urls_text' => "https://example.com/one.png\n/images/two.png\n/storage/portfolio/projects/three.jpg",
        ])->assertSessionHasNoErrors();

        $project = Project::query()->firstOrFail();
        $urls = ['https://example.com/one.png', '/images/two.png', '/storage/portfolio/projects/three.jpg'];
        $this->assertSame($urls, $project->gallery_urls);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->where('projects.0.gallery_urls', $urls));

        $this->withSession($session)->put("/admin/projects/{$project->id}", ['title' => 'Gallery project', 'gallery_urls_text' => ''])
            ->assertSessionHasNoErrors();
        $this->assertSame([], $project->fresh()->gallery_urls);
    }

    public function test_invalid_or_private_gallery_paths_are_rejected_and_admin_is_required(): void
    {
        $this->post('/admin/projects', ['title' => 'Denied'])->assertRedirect('/admin/login');

        foreach (['http://example.com/a.png', '/storage/portfolio/certificates/private.pdf', '/images/../private.png', '/images/%2e%2e/private.png', 'javascript:alert(1)'] as $url) {
            $this->withSession(['portfolio_admin_authenticated' => true])
                ->post('/admin/projects', ['title' => 'Invalid', 'gallery_urls_text' => $url])
                ->assertSessionHasErrors('gallery_urls_text');
        }
        $this->assertSame(0, Project::query()->count());
    }

    public function test_edit_without_gallery_or_thumbnail_preserves_existing_images(): void
    {
        $project = Project::query()->create([
            'title' => 'Existing project',
            'thumbnail_url' => '/storage/portfolio/projects/existing.png',
            'gallery_urls' => ['/images/projects/one.jpg', '/images/projects/two.jpg'],
        ]);

        $this->withSession(['portfolio_admin_authenticated' => true])
            ->put("/admin/projects/{$project->id}", [
                'title' => 'Existing project',
                'summary' => 'Updated project details.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('/storage/portfolio/projects/existing.png', $project->fresh()->thumbnail_url);
        $this->assertSame(['/images/projects/one.jpg', '/images/projects/two.jpg'], $project->fresh()->gallery_urls);
    }

    public function test_migration_backfills_only_classcheck(): void
    {
        $migration = require database_path('migrations/2026_11_04_000002_add_gallery_urls_to_projects.php');
        $migration->down();
        Project::query()->create(['title' => 'ClassCheck']);
        Project::query()->create(['title' => 'Another project']);
        $migration->up();

        $this->assertSame(['/images/projects/classcheck-home.jpg', '/images/projects/classcheck-features.jpg'], Project::query()->where('title', 'ClassCheck')->firstOrFail()->gallery_urls);
        $this->assertNull(Project::query()->where('title', 'Another project')->firstOrFail()->gallery_urls);
    }
}
