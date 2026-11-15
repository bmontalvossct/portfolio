<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FreelanceDescriptionMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_only_the_matching_role_and_keeps_all_requested_lines(): void
    {
        $id = DB::table('work_experiences')->insertGetId([
            'organization' => 'Self-employed', 'position' => 'Freelancer', 'start_date' => '2026',
            'summary' => 'Previous summary', 'responsibilities' => json_encode(['Previous duty']),
            'logo_url' => '/existing.png', 'location' => 'Existing location', 'sort_order' => 3,
            'is_current' => true, 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $otherId = DB::table('work_experiences')->insertGetId([
            'organization' => 'Other', 'position' => 'Freelancer', 'start_date' => '2026',
            'summary' => 'Keep', 'responsibilities' => json_encode(['Keep']),
            'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00',
        ]);
        $before = (array) DB::table('work_experiences')->find($id);
        $other = DB::table('work_experiences')->find($otherId);

        $this->migration()->up();

        $after = (array) DB::table('work_experiences')->find($id);
        $lines = json_decode($after['responsibilities'], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('Provide freelance services specializing in graphic design, video editing, AI-powered automation, and Customer Relationship Management (CRM) solutions. Help businesses, entrepreneurs, and organizations improve their digital presence, streamline operations, optimize workflows, and enhance productivity through creative content and technology-driven solutions.', $after['summary']);
        $this->assertCount(18, $lines);
        $this->assertSame('1961e6ef42531bc9f5e2ae2a932584895df9006b3a96fbdf69e44d581c8d056b', hash('sha256', json_encode($lines, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        $this->assertSame($lines, preg_split('/\r\n|\r|\n/', implode("\n", $lines)));
        foreach (['summary', 'responsibilities', 'updated_at'] as $field) {
            unset($before[$field], $after[$field]);
        }
        $this->assertSame($before, $after);
        $this->assertEquals($other, DB::table('work_experiences')->find($otherId));
        $snapshot = DB::table('work_experiences')->orderBy('id')->get();
        $this->travel(1)->hour();
        $this->migration()->up();
        $this->assertEquals($snapshot, DB::table('work_experiences')->orderBy('id')->get());
        $this->migration()->down();
        $this->assertEquals($snapshot, DB::table('work_experiences')->orderBy('id')->get());
    }

    public function test_it_does_not_insert_a_missing_role(): void
    {
        $before = DB::table('work_experiences')->orderBy('id')->get();
        $this->migration()->up();
        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    public function test_admin_update_preserves_all_freelance_lines_in_order(): void
    {
        config(['portfolio.admin_token' => 'test-portfolio-token']);
        $id = DB::table('work_experiences')->insertGetId([
            'organization' => 'Self-employed', 'position' => 'Freelancer', 'start_date' => '2026',
            'summary' => 'Previous summary', 'responsibilities' => json_encode(['Previous duty']),
            'sort_order' => 0, 'is_current' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->migration()->up();
        $before = DB::table('work_experiences')->find($id);
        $lines = json_decode($before->responsibilities, true, flags: JSON_THROW_ON_ERROR);

        $this->withSession(['portfolio_admin_authenticated' => true])
            ->put("/admin/work-experiences/{$id}", [
                'organization' => $before->organization,
                'position' => $before->position,
                'start_date' => $before->start_date,
                'summary' => $before->summary,
                'responsibilities_text' => implode("\n", $lines),
                'sort_order' => 0,
                'is_current' => true,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertCount(18, $lines);
        $this->assertSame($lines, json_decode(DB::table('work_experiences')->find($id)->responsibilities, true, flags: JSON_THROW_ON_ERROR));
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_11_000001_update_freelance_description.php');
    }
}
