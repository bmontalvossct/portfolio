<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RestoreOriginalWorkExperienceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_restores_exact_original_details_and_retains_all_original_responsibilities(): void
    {
        $originals = $this->insertOriginalExperiences();
        $freelancer = DB::table('work_experiences')->where('organization', 'Self-employed')->first();
        $this->refinement()->up();

        foreach ($originals as $original) {
            $this->assertNotSame($original->summary, DB::table('work_experiences')->find($original->id)->summary);
        }

        $this->restoration()->up();

        foreach ($originals as $original) {
            $restored = DB::table('work_experiences')->find($original->id);
            $this->assertSame($original->summary, $restored->summary);
            $this->assertSame(json_decode($original->responsibilities, true), json_decode($restored->responsibilities, true));
            $expectedMetadata = (array) $original;
            $actualMetadata = (array) $restored;
            unset($expectedMetadata['summary'], $expectedMetadata['responsibilities'], $expectedMetadata['updated_at']);
            unset($actualMetadata['summary'], $actualMetadata['responsibilities'], $actualMetadata['updated_at']);
            $this->assertSame($expectedMetadata, $actualMetadata);
        }

        $this->assertEquals($freelancer, DB::table('work_experiences')->find($freelancer->id));
        $this->assertSame(5, DB::table('work_experiences')->count());
        $this->assertSame(16, DB::table('work_experiences')->whereIn('id', array_column($originals, 'id'))->get()->sum(fn ($role) => count(json_decode($role->responsibilities, true))));

        $after = DB::table('work_experiences')->orderBy('id')->get();
        $this->travel(1)->hour();
        $this->restoration()->up();
        $this->assertEquals($after, DB::table('work_experiences')->orderBy('id')->get());
    }

    public function test_it_guards_summary_and_responsibilities_independently(): void
    {
        $originals = $this->insertOriginalExperiences();
        $this->refinement()->up();
        $summaryId = $originals[0]->id;
        $responsibilitiesId = $originals[1]->id;
        DB::table('work_experiences')->where('id', $summaryId)->update(['summary' => 'Custom admin summary.']);
        DB::table('work_experiences')->where('id', $responsibilitiesId)->update(['responsibilities' => json_encode(['Custom admin responsibility.'])]);

        $this->restoration()->up();

        $summaryRole = DB::table('work_experiences')->find($summaryId);
        $responsibilityRole = DB::table('work_experiences')->find($responsibilitiesId);
        $this->assertSame('Custom admin summary.', $summaryRole->summary);
        $this->assertSame(json_decode($originals[0]->responsibilities, true), json_decode($summaryRole->responsibilities, true));
        $this->assertSame($originals[1]->summary, $responsibilityRole->summary);
        $this->assertSame(['Custom admin responsibility.'], json_decode($responsibilityRole->responsibilities, true));
    }

    public function test_it_preserves_records_with_a_different_identity(): void
    {
        $originals = $this->insertOriginalExperiences();
        $this->refinement()->up();
        $refined = (array) DB::table('work_experiences')->find($originals[0]->id);
        unset($refined['id']);
        $otherDateId = DB::table('work_experiences')->insertGetId(array_merge($refined, ['start_date' => '2020']));
        $otherOrgId = DB::table('work_experiences')->insertGetId(array_merge($refined, ['organization' => 'Another organization']));
        $otherPositionId = DB::table('work_experiences')->insertGetId(array_merge($refined, ['position' => 'Another role']));
        $ids = [$otherDateId, $otherOrgId, $otherPositionId];
        $before = DB::table('work_experiences')->whereIn('id', $ids)->orderBy('id')->get();

        $this->restoration()->up();

        $this->assertEquals($before, DB::table('work_experiences')->whereIn('id', $ids)->orderBy('id')->get());
    }

    public function test_rollback_does_not_remove_or_rewrite_career_records(): void
    {
        $this->insertOriginalExperiences();
        $before = DB::table('work_experiences')->orderBy('id')->get();

        $this->restoration()->down();

        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    private function insertOriginalExperiences(): array
    {
        $originals = [
            [
                'organization' => 'Surigao del Norte State University',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => '2026',
                'summary' => 'Systems analysis, data operations, technical support, network administration, documentation, and user training for university services.',
                'responsibilities' => ['Systems analysis and design', 'Data management and reporting', 'Technical and network support', 'User training and documentation'],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'summary' => 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.',
                'responsibilities' => ['EMR implementation', 'Health facility readiness assessment', 'Training and technical support', 'Compliance monitoring and reporting'],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
                'responsibilities' => ['Health information system support', 'Data and service statistics', 'User account validation', 'Facility monitoring and troubleshooting'],
            ],
            [
                'organization' => 'Cebupot',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'summary' => 'Supported database updates, administrative work, image editing, documentation, and ICT equipment setup.',
                'responsibilities' => ['Excel database updates', 'Photo editing and enhancement', 'Administrative support', 'ICT equipment assistance'],
            ],
        ];

        return array_map(function (array $role, int $index): object {
            $role['responsibilities'] = json_encode($role['responsibilities']);
            $role['sort_order'] = $index + 1;
            $role['is_current'] = false;
            $role['logo_url'] = '/existing-logo-'.$index.'.png';
            $role['location'] = 'Existing location';
            $id = DB::table('work_experiences')->insertGetId($role);

            return DB::table('work_experiences')->find($id);
        }, $originals, array_keys($originals));
    }

    private function refinement(): object
    {
        return require database_path('migrations/2026_11_09_000001_refine_work_experience_and_add_freelance.php');
    }

    private function restoration(): object
    {
        return require database_path('migrations/2026_11_09_000002_restore_original_work_experience_details.php');
    }
}
