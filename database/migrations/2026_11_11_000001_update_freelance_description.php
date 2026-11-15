<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $content = [
            'summary' => 'Provide freelance services specializing in graphic design, video editing, AI-powered automation, and Customer Relationship Management (CRM) solutions. Help businesses, entrepreneurs, and organizations improve their digital presence, streamline operations, optimize workflows, and enhance productivity through creative content and technology-driven solutions.',
            'responsibilities' => [
                    '1. Graphic Design and Digital Content Creation',
                    'Design professional graphics, social media materials, marketing assets, presentations, and digital promotional content.',
                    'Develop visually engaging materials aligned with client branding, communication goals, and target audiences.',
                    '2. Video Editing and Multimedia Production',
                    'Edit and produce videos for social media, promotional campaigns, educational materials, and other digital platforms.',
                    'Enhance multimedia content through transitions, captions, audio editing, visual effects, and storytelling techniques.',
                    '3. AI Automation and Workflow Development',
                    'Design and implement AI-powered workflows to automate repetitive business tasks and improve operational efficiency.',
                    'Integrate AI tools, third-party applications, APIs, and automation platforms to streamline business processes.',
                    'Develop automated systems for data processing, notifications, document management, and information retrieval.',
                    '4. CRM Management and Business Process Optimization',
                    'Organize, maintain, and update customer records, contact databases, and CRM workflows.',
                    'Configure automated lead tracking, customer follow-ups, task assignments, and communication processes.',
                    'Improve customer engagement and operational efficiency through structured data management and workflow automation.',
                    '5. Client Collaboration and Technical Support',
                    'Collaborate with clients to understand project requirements and develop tailored creative and technical solutions.',
                    'Manage project deliverables, implement revisions, troubleshoot workflow issues, and provide ongoing technical assistance.',
                    'Ensure the quality, consistency, and timely delivery of freelance projects.',
                ],
        ];
        $query = DB::table('work_experiences')
            ->where('organization', 'Self-employed')
            ->where('position', 'Freelancer')
            ->where('start_date', '2026');

        foreach ($query->get() as $role) {
            if ($role->summary === $content['summary'] && json_decode($role->responsibilities, true) === $content['responsibilities']) {
                continue;
            }

            DB::table('work_experiences')->where('id', $role->id)->update([
                'summary' => $content['summary'],
                'responsibilities' => json_encode($content['responsibilities'], JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Preserve subsequent content edits.
    }
};
