<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkExperienceContentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_one_current_freelance_role_with_the_four_requested_services(): void
    {
        // Exercise this historical migration from its pre-freelancer state.
        DB::table('work_experiences')->where('organization', 'Self-employed')->delete();
        $this->migration()->up();
        $this->migration()->up();

        $roles = DB::table('work_experiences')->where('organization', 'Self-employed')->get();
        $this->assertCount(1, $roles);
        $role = $roles->first();
        $this->assertSame('Freelancer', $role->position);
        $this->assertSame('2026', $role->start_date);
        $this->assertNull($role->end_date);
        $this->assertSame(1, (int) $role->is_current);
        $this->assertSame(0, (int) $role->sort_order);
        $this->assertNull($role->logo_url);
        $this->assertNull($role->location);
        $this->assertSame([
            'Create graphic designs for digital content.',
            'Edit videos for digital content.',
            'Build AI automation workflows.',
            'Manage CRM records and workflows.',
        ], json_decode($role->responsibilities, true));
    }

    public function test_it_refines_all_sixteen_responsibilities_without_changing_career_metadata(): void
    {
        $records = [
            [
                'organization' => 'Surigao del Norte State University',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => '2026',
                'summary' => 'Systems analysis, data operations, technical support, network administration, documentation, and user training for university services.',
                'responsibilities' => ['Systems analysis and design', 'Data management and reporting', 'Technical and network support', 'User training and documentation'],
                'expected' => ['Analyzed university service needs and supported information systems design.', 'Managed operational data and prepared reports for university services.', 'Provided technical support and assisted with network administration.', 'Trained users and documented systems and procedures.'],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'summary' => 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.',
                'responsibilities' => ['EMR implementation', 'Health facility readiness assessment', 'Training and technical support', 'Compliance monitoring and reporting'],
                'expected' => ['Implemented electronic medical record systems in health facilities.', 'Assessed facility readiness for EMR implementation.', 'Trained facility personnel and provided technical support.', 'Monitored compliance and prepared implementation and technical reports.'],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
                'responsibilities' => ['Health information system support', 'Data and service statistics', 'User account validation', 'Facility monitoring and troubleshooting'],
                'expected' => ['Provided operational support for health information systems.', 'Compiled health data and service statistics for reports.', 'Validated user accounts for health information systems.', 'Monitored health facilities through visits and technical troubleshooting.'],
            ],
            [
                'organization' => 'Cebupot',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'summary' => 'Supported database updates, administrative work, image editing, documentation, and ICT equipment setup.',
                'responsibilities' => ['Excel database updates', 'Photo editing and enhancement', 'Administrative support', 'ICT equipment assistance'],
                'expected' => ['Updated and maintained Excel database records.', 'Edited and enhanced photos for visual content.', 'Assisted with administrative tasks and documentation.', 'Helped set up and support ICT equipment.'],
            ],
        ];

        $snapshots = [];
        foreach ($records as $index => $record) {
            unset($record['expected']);
            $record['responsibilities'] = json_encode($record['responsibilities']);
            $record['sort_order'] = $index + 1;
            $record['is_current'] = false;
            $record['logo_url'] = '/existing-logo-'.$index.'.png';
            $record['location'] = 'Existing location';
            $id = DB::table('work_experiences')->insertGetId($record);
            $snapshots[$id] = (array) DB::table('work_experiences')->find($id);
        }

        $this->migration()->up();
        $this->migration()->up();

        foreach (array_keys($snapshots) as $index => $id) {
            $updated = (array) DB::table('work_experiences')->find($id);
            $this->assertSame($records[$index]['expected'], json_decode($updated['responsibilities'], true));
            $this->assertNotSame($snapshots[$id]['summary'], $updated['summary']);
            unset($updated['summary'], $updated['responsibilities'], $updated['updated_at']);
            unset($snapshots[$id]['summary'], $snapshots[$id]['responsibilities'], $snapshots[$id]['updated_at']);
            $this->assertSame($snapshots[$id], $updated);
        }
    }

    public function test_it_preserves_custom_admin_copy_and_nonmatching_records(): void
    {
        $custom = [
            'organization' => 'Department of Health',
            'position' => 'Computer Programmer I',
            'start_date' => 'February 18, 2021',
            'summary' => 'An independently edited summary.',
            'responsibilities' => json_encode(['An independently edited responsibility.']),
        ];
        $customId = DB::table('work_experiences')->insertGetId($custom);
        $otherId = DB::table('work_experiences')->insertGetId(array_merge($custom, [
            'start_date' => '2020',
            'summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
            'responsibilities' => json_encode(['Health information system support', 'Data and service statistics', 'User account validation', 'Facility monitoring and troubleshooting']),
        ]));
        $before = DB::table('work_experiences')->whereIn('id', [$customId, $otherId])->orderBy('id')->get();

        $freelanceId = DB::table('work_experiences')->where('organization', 'Self-employed')->value('id');
        DB::table('work_experiences')->where('id', $freelanceId)->update(['summary' => 'Custom freelance copy.']);

        $this->migration()->up();
        $this->migration()->up();
        $this->assertEquals($before, DB::table('work_experiences')->whereIn('id', [$customId, $otherId])->orderBy('id')->get());
        $this->assertSame('Custom freelance copy.', DB::table('work_experiences')->find($freelanceId)->summary);
        $this->assertSame(1, DB::table('work_experiences')->where('organization', 'Self-employed')->count());
    }

    public function test_rollback_retains_career_history_and_later_edits(): void
    {
        DB::table('work_experiences')->where('organization', 'Self-employed')->update(['summary' => 'Later admin edit.']);
        $before = DB::table('work_experiences')->orderBy('id')->get();

        $this->migration()->down();

        $this->assertEquals($before, DB::table('work_experiences')->orderBy('id')->get());
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_09_000001_refine_work_experience_and_add_freelance.php');
    }
}
