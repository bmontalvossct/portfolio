<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResumeWorkExperienceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_all_fifty_resume_duties_in_source_order_and_preserves_career_metadata(): void
    {
        $before = $this->insertExistingExperiences();
        $freelancer = DB::table('work_experiences')->where('organization', 'Self-employed')->first();

        $this->migration()->up();

        foreach ($this->resumeExpectations() as $index => $expected) {
            $role = DB::table('work_experiences')->find($before[$index]->id);
            $responsibilities = json_decode($role->responsibilities, true, flags: JSON_THROW_ON_ERROR);

            $this->assertCount($expected['count'], $responsibilities);
            $this->assertSame($expected['sha256'], hash('sha256', json_encode($responsibilities, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)), $role->position);
            $this->assertSame($expected['end_date'], $role->end_date);
            $this->assertSame(0, (int) $role->is_current);

            $expectedMetadata = (array) $before[$index];
            $actualMetadata = (array) $role;
            foreach (['responsibilities', 'end_date', 'is_current', 'updated_at'] as $field) {
                unset($expectedMetadata[$field], $actualMetadata[$field]);
            }
            $this->assertSame($expectedMetadata, $actualMetadata);
        }

        $this->assertEquals($freelancer, DB::table('work_experiences')->find($freelancer->id));
        $this->assertSame(5, DB::table('work_experiences')->count());
        $this->assertSame(50 + count(json_decode($freelancer->responsibilities, true)), DB::table('work_experiences')->get()->sum(fn ($role) => count(json_decode($role->responsibilities, true))));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame(base_path('bootstrap/cache/config-testing.php'), app()->getCachedConfigPath());
    }

    public function test_it_is_idempotent_including_update_timestamps(): void
    {
        $this->insertExistingExperiences();
        $this->migration()->up();
        $before = DB::table('work_experiences')->orderBy('id')->get();
        $this->travel(1)->hour();

        $this->migration()->up();

        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    public function test_it_does_not_match_another_organization_position_or_start_date(): void
    {
        $originals = $this->insertExistingExperiences();
        $template = (array) $originals[0];
        unset($template['id']);
        $otherIds = [];
        foreach ([['organization' => 'Other organization'], ['position' => 'Other position'], ['start_date' => '2020']] as $identity) {
            $otherIds[] = DB::table('work_experiences')->insertGetId([...$template, ...$identity]);
        }
        $before = DB::table('work_experiences')->whereIn('id', $otherIds)->orderBy('id')->get();

        $this->migration()->up();

        $this->assertEquals($before, DB::table('work_experiences')->whereIn('id', $otherIds)->orderBy('id')->get());
    }

    public function test_it_does_not_create_missing_jobs_or_modify_unrelated_records(): void
    {
        $before = DB::table('work_experiences')->orderBy('id')->get();

        $this->migration()->up();

        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    public function test_rollback_keeps_imported_details_and_subsequent_admin_edits(): void
    {
        $originals = $this->insertExistingExperiences();
        $this->migration()->up();
        DB::table('work_experiences')->where('id', $originals[0]->id)->update([
            'summary' => 'Later admin summary.',
            'responsibilities' => json_encode(['Later admin responsibility.']),
        ]);
        $before = DB::table('work_experiences')->orderBy('id')->get();

        $this->migration()->down();

        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    private function insertExistingExperiences(): array
    {
        return array_map(function (array $role, int $index): object {
            $id = DB::table('work_experiences')->insertGetId([
                'organization' => $role['organization'],
                'position' => $role['position'],
                'start_date' => $role['start_date'],
                'end_date' => $index === 0 ? '2026' : $role['end_date'],
                'is_current' => $index === 0,
                'responsibilities' => json_encode(['Existing public duty.']),
                'summary' => 'Preserve custom summary '.$index,
                'logo_url' => '/existing-logo-'.$index.'.png',
                'location' => 'Existing location '.$index,
                'sort_order' => $index + 1,
                'created_at' => '2026-01-01 00:00:00',
                'updated_at' => '2026-01-01 00:00:00',
            ]);

            return DB::table('work_experiences')->find($id);
        }, $this->resumeExpectations(), array_keys($this->resumeExpectations()));
    }

    private function resumeExpectations(): array
    {
        // SHA-256 of each ordered, unescaped JSON duty list, independently extracted from the
        // resume's public work section; the private document is not stored here.
        return [
            [
                'organization' => 'Surigao del Norte State University',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => 'June 18, 2026',
                'count' => 7,
                'sha256' => '710797aa51178e293b05c6d3be0211dba02a89273b758f50cba25820ba2055e1',
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'count' => 18,
                'sha256' => 'e62962971277607081839238ee5fb57d73b34a75d1da1836816874809393f004',
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'count' => 18,
                'sha256' => 'e62962971277607081839238ee5fb57d73b34a75d1da1836816874809393f004',
            ],
            [
                'organization' => 'Cebupot',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'count' => 7,
                'sha256' => '3db98e6169bdbcbe79cc507fffe483c4c1a6baa76e7c172489f16ae277e6494e',
            ],
        ];
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_10_000002_update_work_experience_from_resume.php');
    }
}
