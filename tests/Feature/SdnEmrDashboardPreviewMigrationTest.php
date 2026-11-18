<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SdnEmrDashboardPreviewMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_backfills_only_the_matching_project_with_a_missing_preview(): void
    {
        $project = Project::query()->create([
            'title' => 'SDN Electronic Medical Records Dashboard',
            'category' => 'Power BI dashboard',
            'url' => 'https://app.powerbi.com/view?r=existing-report',
            'summary' => 'Existing project details.',
            'gallery_urls' => ['/images/projects/existing.png'],
            'sort_order' => 3,
            'is_featured' => true,
        ]);
        $other = Project::query()->create(['title' => 'Another dashboard']);
        $before = (array) DB::table('projects')->find($project->id);

        $this->migration()->up();

        $after = (array) DB::table('projects')->find($project->id);
        $this->assertSame('/images/projects/sdn-emr-dashboard-preview.png', $after['thumbnail_url']);
        unset($before['thumbnail_url'], $after['thumbnail_url']);
        $this->assertSame($before, $after);
        $this->assertNull($other->fresh()->thumbnail_url);

        $this->withoutVite();
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('projects', function ($projects): bool {
                $project = collect($projects)->firstWhere('title', 'SDN Electronic Medical Records Dashboard');

                return $project !== null
                    && $project['thumbnail_url'] === '/images/projects/sdn-emr-dashboard-preview.png'
                    && ! in_array('/images/projects/existing.png', $project['gallery_urls'], true)
                    && $project['url'] !== 'https://app.powerbi.com/view?r=existing-report';
            })
        );
    }

    public function test_it_handles_empty_values_without_replacing_custom_previews_and_is_idempotent(): void
    {
        $empty = Project::query()->create(['title' => 'SDN Electronic Medical Records Dashboard', 'thumbnail_url' => '']);
        $custom = Project::query()->create(['title' => 'SDN Electronic Medical Records Dashboard', 'thumbnail_url' => '/custom-preview.png']);
        $customBefore = DB::table('projects')->find($custom->id);

        $this->migration()->up();
        $after = DB::table('projects')->orderBy('id')->get();
        $this->travel(1)->hour();
        $this->migration()->up();

        $this->assertSame('/images/projects/sdn-emr-dashboard-preview.png', $empty->fresh()->thumbnail_url);
        $this->assertEquals($customBefore, DB::table('projects')->find($custom->id));
        $this->assertEquals($after, DB::table('projects')->orderBy('id')->get());
    }

    public function test_rollback_preserves_the_preview_and_later_edits(): void
    {
        Project::query()->create(['title' => 'SDN Electronic Medical Records Dashboard', 'thumbnail_url' => '/later-admin-preview.png']);
        $before = DB::table('projects')->get();

        $this->migration()->down();

        $this->assertEquals($before, DB::table('projects')->get());
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_14_000002_add_sdn_emr_dashboard_preview.php');
    }
}
