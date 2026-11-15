<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Public badge assertions verified against the brittm Credly profile on 14 November 2026.
        $badges = [
            [
                'external_id' => '6b9517c0-4f34-44b2-ac5e-8b187218448a',
                'name' => 'Google AI Professional Certificate',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the Google AI Professional Certificate are fluent in AI, and have completed 8 courses demonstrating their ability to apply AI to the skills where AI is transforming work: brainstorming, research, communication, content creation, data analysis, and vibe coding. They have built a portfolio of 20+ artifacts using AI, and vibe coded a custom AI solution. They prompt effectively, evaluate outputs, and leverage AI tools responsibly to solve real workplace challenges.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/782f33e7-c4b5-4d1d-a7ce-02f25b7b9a1e/blob',
                'certificate_url' => 'https://www.credly.com/badges/6b9517c0-4f34-44b2-ac5e-8b187218448a',
                'skills' => [
                    'AI Acumen',
                    'AI for Data Analysis',
                    'AI for brainstorming and planning',
                    'AI for content creation',
                    'AI for research and insights',
                    'AI for writing and communicating',
                    'Artificial Intelligence (AI)',
                    'Prompt Engineering',
                    'Responsible AI',
                    'vibe coding',
                ],
                'is_featured' => true,
            ],
            [
                'external_id' => '867c3728-1b67-4a39-b377-c0a780fb180b',
                'name' => 'Google AI for App Building',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for App Building badge from Google can apply "vibe coding"—a technique where natural language is used to generate fully functioning code. They have mapped out their personal workflows to identify opportunities for AI and analyzed these tasks to determine their suitability for an app. They have built custom web applications to solve workflow challenges and possess an understanding of the common pitfalls of vibe coding.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/2cf18bea-6b75-401a-bf9d-ac0a23f582fd/blob',
                'certificate_url' => 'https://www.credly.com/badges/867c3728-1b67-4a39-b377-c0a780fb180b',
                'skills' => [
                    'App building',
                    'Artificial Intelligence (AI)',
                    'vibe coding',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => 'daf1b63f-a887-4c3b-bf75-848f4cecc69d',
                'name' => 'Google AI for Brainstorming and Planning',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for Brainstorming and Planning badge from Google can transform abstract ideas into actionable project plans using AI. They have demonstrated the ability to use AI as a thought partner to brainstorm business concepts, conduct cost-benefit analyses, and identify project risks and dependencies. They are skilled in creating centralized project hubs to keep stakeholders informed and using scheduled actions to automate routine project management tasks.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/fb6dc8be-1471-4aab-b2ab-da1f76080ecf/blob',
                'certificate_url' => 'https://www.credly.com/badges/daf1b63f-a887-4c3b-bf75-848f4cecc69d',
                'skills' => [
                    'Artificial Intelligence (AI)',
                    'Brainstorming',
                    'Coordinating',
                    'Planning',
                    'Problem Solving',
                    'Process Improvement',
                    'Project Management',
                    'Time Management',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => '8d9f4d51-7b24-4a8e-9ec9-6a5a95bbf90e',
                'name' => 'Google AI for Content Creation',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for Content Creation badge from Google can use AI to generate and transform multimedia content. They have demonstrated the ability to create marketing assets, including images and video, and enhance presentation slides for maximum engagement with image and video generation models. They possess the skills to critique creative work against brand guidelines and refine outputs to ensure they are polished and align to standards.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/f0c5c16d-1480-4968-9259-50851d03f79a/blob',
                'certificate_url' => 'https://www.credly.com/badges/8d9f4d51-7b24-4a8e-9ec9-6a5a95bbf90e',
                'skills' => [
                    'Artificial Intelligence (AI)',
                    'Brand consistency',
                    'Content Strategy',
                    'Image Generation',
                    'Presentation Design',
                    'Prototyping',
                    'Video Generation',
                    'Visual Storytelling',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => 'ecf91e1f-1db3-4d3d-b9d6-3a4e721ef0a5',
                'name' => 'Google AI for Data Analysis',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for Data Analysis badge from Google can use AI to clean, analyze, and visualize complex data. They have acquired the skills to convert messy, unstructured data into structured formats and develop spreadsheet functions using natural language. Beyond basic analysis, they can collaborate with AI to identify key business metrics, and visualize their findings to drive evidence-based decision making.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/97e068dc-d64a-4806-a6cd-5c4e68fa3d5f/blob',
                'certificate_url' => 'https://www.credly.com/badges/ecf91e1f-1db3-4d3d-b9d6-3a4e721ef0a5',
                'skills' => [
                    'Artificial Intelligence (AI)',
                    'Data Analysis',
                    'Data Manipulation',
                    'Data Reporting',
                    'Data Visualization',
                    'Data-Driven Decision Making',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => '6c65fe3a-9263-40bf-9105-a6bdd2a26c69',
                'name' => 'Google AI for Research and Insights',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for Research and Insights badge from Google can leverage AI to increase the speed and depth of their research. They have the skills to synthesize complex, multimodal information (text, video, documents) into concise summaries and reports. Furthermore, they have demonstrated the ability to validate sources, create expert personas using Gems to pressure-test ideas, and use tools like Gemini Deep Research to generate data-backed insights to help inform decisions.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/c065d009-d51b-40f8-8f9b-4b90cb4ece67/blob',
                'certificate_url' => 'https://www.credly.com/badges/6c65fe3a-9263-40bf-9105-a6bdd2a26c69',
                'skills' => [
                    'Artificial Intelligence (AI)',
                    'Information Synthesis',
                    'Reports',
                    'Research',
                    'Synthesis',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => '61d6c68d-9e35-4223-a9a2-95ea7ec98c8c',
                'name' => 'Google AI for Writing and Communicating',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI for Writing and Communicating badge from Google can use AI to refine communication strategies and produce high-quality writing. They are proficient in transforming meeting transcripts into actionable decision lists and adapting messages for diverse audiences using Gemini Canvas. They have also practiced using AI to practice their talking points ahead of a high stakes conversation and anticipate stakeholder feedback to improve work outcomes.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/791a77e5-1590-40bc-92a9-c69126fa4e4b/blob',
                'certificate_url' => 'https://www.credly.com/badges/61d6c68d-9e35-4223-a9a2-95ea7ec98c8c',
                'skills' => [
                    'Artificial Intelligence (AI)',
                    'Collaboration',
                    'Communication',
                    'Editing',
                    'Stakeholder Engagement',
                    'Transcribing',
                    'Writing',
                ],
                'is_featured' => false,
            ],
            [
                'external_id' => '649fcb31-788c-4c1c-8b19-ad652dfaa4e0',
                'name' => 'Google AI Fundamentals',
                'issuer' => 'Coursera',
                'description' => 'Those who earn the AI Fundamentals badge from Google have adopted the "AI as a Collaborator" mindset and developed an understanding of the AI ecosystem, including models, features, and applications. They are familiar with AI strengths, limitations and risks. They have mastered prompting and have practiced advanced techniques. They have demonstrated the ability to use AI responsibly by applying the ACT framework (Ask, Check, Tell) to evaluate outputs for accuracy, bias, and privacy risks.',
                'issued_at' => '2026-10-05',
                'expires_at' => null,
                'image_url' => 'https://images.credly.com/images/36ecc7ff-8ccf-4bd8-83a7-6f35797e0b64/blob',
                'certificate_url' => 'https://www.credly.com/badges/649fcb31-788c-4c1c-8b19-ad652dfaa4e0',
                'skills' => [
                    'AI Acumen',
                    'Artificial Intelligence (AI)',
                    'Multimodal Prompting',
                    'Prompt Engineering',
                    'Prompt chaining',
                    'Responsible AI',
                ],
                'is_featured' => false,
            ],
        ];

        DB::transaction(function () use ($badges): void {
            $nextOrder = ((int) DB::table('credential_badges')->max('sort_order')) + 1;

            foreach ($badges as $badge) {
                $existing = DB::table('credential_badges')
                    ->where('provider', 'credly')
                    ->where('external_id', $badge['external_id'])
                    ->exists();

                if ($existing) {
                    continue;
                }

                DB::table('credential_badges')->insert(array_merge($badge, [
                    'provider' => 'credly',
                    'category' => 'ai_data',
                    'skills' => json_encode($badge['skills'], JSON_THROW_ON_ERROR),
                    'criteria_url' => null,
                    'evidence_url' => null,
                    'sort_order' => $nextOrder++,
                    'synced_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        });
    }

    public function down(): void
    {
        // Keep earned badges and any later admin edits when rolling back application code.
    }
};
