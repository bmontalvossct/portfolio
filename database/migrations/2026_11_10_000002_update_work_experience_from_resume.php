<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Public work-experience section of the user-supplied resume, 10 November 2026.
        $dohResponsibilities = [
            'Oversee the overall implementation of EMR Records.',
            'Assist in Assessment of hospital readiness for system implementation.',
            'Orient and train personnel of local health facilities on how to use DOH implement systems.',
            'Install Relevant systems needed in respective health facilities.',
            'Conduct regular visits, troubleshooting, and monitoring in all health facilities.',
            'Validate request for username and password for particular systems.',
            'Monitor health facility systems for proper compliance.',
            'Answer phone calls and update FAQs encountered during system implementation.',
            'Submit monthly reports/transcriptions/summaries of all queries and actions made to the regional office.',
            'Generate service statistics or other relevant reports requested by relevant and authorized offices.',
            'Provide other forms of technical assistance connected to the implemented systems.',
            'Perform other tasks that may be assigned related to EMR implementation.',
            'Conduct training regarding ICLINICSYS to Surigao del Norte Health Facilities.',
            'Create Statistics/Analytical reports.',
            'Create Formulated Excels.',
            'Attend meetings if necessary.',
            'Handle and provide troubleshooting assistance to health facilities regarding DOH-implemented websites.',
            'Email advisories/administrative orders to health facilities.',
        ];

        $experiences = [
            [
                'organization' => 'Surigao del Norte State University',
                'position' => 'Information Systems Analyst I',
                'start_date' => 'November 25, 2024',
                'end_date' => 'June 18, 2026',
                'responsibilities' => [
                    'Systems Analysis and Design: Analyze existing information systems to identify areas for improvement and assist in designing new IT solutions or modifying existing systems to enhance user experience and operational efficiency.',
                    'Data Management and Analysis: Gather, validate, and document system information and needs; identify and interpret data patterns and trends; and analyze results using statistical techniques or tools.',
                    'Technical Support: Provide technical assistance to business owners and end-users of information systems, including troubleshooting hardware and software issues, and ensuring smooth operation of various information systems.',
                    'Network Administration: Understand network concepts and be capable of administering and operating various information systems, including system administration and infrastructure development towards digitalization.',
                    'Documentation and Reporting: Prepare and maintain technical documents, user manuals, and system processes; document system changes and enhancements; and produce accomplishment reports periodically.',
                    'User Training: Conduct user training sessions to ensure proper utilization of information systems and best practices.',
                    'Research and Market Analysis: Conduct research and market analysis to stay updated with industry trends and advancements in information systems.',
                ],
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer II',
                'start_date' => 'April 30, 2024',
                'end_date' => 'November 25, 2024',
                'responsibilities' => $dohResponsibilities,
            ],
            [
                'organization' => 'Department of Health',
                'position' => 'Computer Programmer I',
                'start_date' => 'February 18, 2021',
                'end_date' => 'April 30, 2024',
                'responsibilities' => $dohResponsibilities,
            ],
            [
                'organization' => 'Cebupot',
                'position' => 'Intern',
                'start_date' => 'March 2018',
                'end_date' => 'June 2018',
                'responsibilities' => [
                    'Assist in updating and maintaining Excel databases, ensuring accurate and organized data storage for operational needs.',
                    'Provide administrative assistance to supervisors, including scheduling appointments, organizing documents, and handling correspondence to support daily office operations.',
                    'Participate in coordinating and conducting photo ops for company units, assisting with setup, image capture, and post-production editing as directed by supervisors.',
                    'Support supervisors in editing and enhancing photos using editing software, following provided guidelines and instructions to maintain consistency and quality.',
                    'Assist in processing and issuing checks when required, ensuring accurate documentation and adherence to financial procedures under supervision.',
                    'Attend company meetings as directed, actively observing and contributing as appropriate, gaining insights into organizational processes and decision-making.',
                    'Provide technical assistance for company ICT equipment under supervision, learning troubleshooting techniques and maintenance procedures to ensure equipment functionality.',
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
                    $updates = [];

                    if (json_decode($record->responsibilities ?? 'null', true) !== $experience['responsibilities']) {
                        $updates['responsibilities'] = json_encode($experience['responsibilities'], JSON_THROW_ON_ERROR);
                    }

                    if ($record->end_date !== $experience['end_date']) {
                        $updates['end_date'] = $experience['end_date'];
                    }

                    if ((bool) $record->is_current) {
                        $updates['is_current'] = false;
                    }

                    if ($updates !== []) {
                        DB::table('work_experiences')
                            ->where('id', $record->id)
                            ->update([...$updates, 'updated_at' => now()]);
                    }
                }
            }
        });
    }

    public function down(): void
    {
        // Keep user-approved resume details and later content edits on code rollback.
    }
};
