<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $experiences = [
            [
                'organization' => 'Surigao del Norte State University',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'old_summary' => 'Systems analysis, data operations, technical support, network administration, documentation, and user training for university services.',
                'summary' => 'Supported university services through systems analysis, data operations, and technical support.',
                'old_responsibilities' => ['Systems analysis and design', 'Data management and reporting', 'Technical and network support', 'User training and documentation'],
                'responsibilities' => [
                    'Analyzed university service needs and supported information systems design.',
                    'Managed operational data and prepared reports for university services.',
                    'Provided technical support and assisted with network administration.',
                    'Trained users and documented systems and procedures.',
                ],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'old_summary' => 'Implemented and supported electronic medical record systems across health facilities, including readiness assessment, training, monitoring, and technical reporting.',
                'summary' => 'Implemented and supported electronic medical record systems across health facilities.',
                'old_responsibilities' => ['EMR implementation', 'Health facility readiness assessment', 'Training and technical support', 'Compliance monitoring and reporting'],
                'responsibilities' => [
                    'Implemented electronic medical record systems in health facilities.',
                    'Assessed facility readiness for EMR implementation.',
                    'Trained facility personnel and provided technical support.',
                    'Monitored compliance and prepared implementation and technical reports.',
                ],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'old_summary' => 'Supported health information systems, user accounts, data reports, facility visits, and troubleshooting for regional health programs.',
                'summary' => 'Supported health information systems and data reporting for regional health programs.',
                'old_responsibilities' => ['Health information system support', 'Data and service statistics', 'User account validation', 'Facility monitoring and troubleshooting'],
                'responsibilities' => [
                    'Provided operational support for health information systems.',
                    'Compiled health data and service statistics for reports.',
                    'Validated user accounts for health information systems.',
                    'Monitored health facilities through visits and technical troubleshooting.',
                ],
            ],
            [
                'organization' => 'Cebupot',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'old_summary' => 'Supported database updates, administrative work, image editing, documentation, and ICT equipment setup.',
                'summary' => 'Assisted with data updates, visual content, documentation, and office ICT support.',
                'old_responsibilities' => ['Excel database updates', 'Photo editing and enhancement', 'Administrative support', 'ICT equipment assistance'],
                'responsibilities' => [
                    'Updated and maintained Excel database records.',
                    'Edited and enhanced photos for visual content.',
                    'Assisted with administrative tasks and documentation.',
                    'Helped set up and support ICT equipment.',
                ],
            ],
        ];

        DB::transaction(function () use ($experiences): void {
            foreach ($experiences as $experience) {
                $records = DB::table('work_experiences')
                    ->where('organization', $experience['organization'])
                    ->where('position', $experience['position'])
                    ->where('start_date', $experience['start_date'])
                    ->get();

                foreach ($records as $record) {
                    // Update only the known copy; retain independently edited admin content.
                    if ($record->summary === $experience['old_summary']) {
                        DB::table('work_experiences')
                            ->where('id', $record->id)
                            ->where('summary', $record->summary)
                            ->update(['summary' => $experience['summary'], 'updated_at' => now()]);
                    }

                    if (json_decode($record->responsibilities ?? 'null', true) === $experience['old_responsibilities']) {
                        DB::table('work_experiences')
                            ->where('id', $record->id)
                            ->where('responsibilities', $record->responsibilities)
                            ->update([
                                'responsibilities' => json_encode($experience['responsibilities'], JSON_THROW_ON_ERROR),
                                'updated_at' => now(),
                            ]);
                    }
                }
            }

            $freelance = DB::table('work_experiences')
                ->where('organization', 'Self-employed')
                ->where('position', 'Freelancer')
                ->where('start_date', '2026');

            if (! $freelance->exists()) {
                DB::table('work_experiences')->insert([
                    'organization' => 'Self-employed',
                    'position' => 'Freelancer',
                    'start_date' => '2026',
                    'end_date' => null,
                    'summary' => 'Provide freelance graphic design, video editing, AI automation, and CRM management services.',
                    'responsibilities' => json_encode([
                        'Create graphic designs for digital content.',
                        'Edit videos for digital content.',
                        'Build AI automation workflows.',
                        'Manage CRM records and workflows.',
                    ], JSON_THROW_ON_ERROR),
                    'sort_order' => 0,
                    'is_current' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Retain career history and any later admin edits when rolling back code.
    }
};
