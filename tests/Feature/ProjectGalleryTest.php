<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_versioned_gallery_in_order_not_database_changes(): void
    {
        $this->withoutVite();
        Project::query()->create(['title' => 'Database-only project', 'gallery_urls' => ['/images/injected.png']]);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('projects', function ($projects): bool {
                $items = collect($projects);
                $classcheck = $items->firstWhere('title', 'ClassCheck');

                return $classcheck !== null
                    && $classcheck['gallery_urls'] === ['/images/projects/classcheck-home.jpg', '/images/projects/classcheck-features.jpg']
                    && ! $items->contains('title', 'Database-only project');
            }));
    }

    public function test_admin_gallery_mutations_are_blocked_and_require_authentication(): void
    {
        $this->post('/admin/projects', ['title' => 'Denied'])->assertRedirect('/admin/login');
        $project = Project::query()->create([
            'title' => 'Existing project', 'thumbnail_url' => '/images/projects/existing.png',
            'gallery_urls' => ['/images/projects/one.jpg', '/images/projects/two.jpg'],
        ]);
        $before = $project->fresh()->toArray();
        foreach (['http://example.com/a.png', '/storage/portfolio/certificates/private.pdf', '/images/../private.png', 'javascript:alert(1)'] as $url) {
            $this->withSession(['portfolio_admin_authenticated' => true])
                ->put("/admin/projects/{$project->id}", ['title' => 'Invalid', 'gallery_urls_text' => $url])
                ->assertStatus(409);
        }
        $this->assertSame($before, $project->fresh()->toArray());
    }

    public function test_public_gallery_contains_no_private_document_paths(): void
    {
        $this->withoutVite();
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('projects', fn ($projects): bool => collect($projects)->every(
                fn ($project): bool => collect($project['gallery_urls'] ?? [])->every(
                    fn ($url): bool => ! str_contains(strtolower($url), 'certificates/')
                        && ! str_contains(rawurldecode($url), '..')
                        && ! str_starts_with(strtolower($url), 'javascript:')
                )
            )));
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
