<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DICT_ISSUER = 'Department of Information and Communications Technology';

    public function up(): void
    {
        $timestamp = now();

        $certificates = [
            [
                'source' => 'google_drive',
                'external_id' => '1cvs4f3vmkMzS7b_6xhje_MDyzBZ5RQlm',
                'title' => 'Building with the Claude API',
                'issuer' => 'Anthropic',
                'category' => 'ai_data',
                'description' => 'Anthropic course covering practical application development with the Claude API.',
                'file_url' => 'https://anthropic.skilljar.com/claude-with-the-anthropic-api',
                'tags' => ['Anthropic', 'Claude API', 'API development', 'Artificial intelligence'],
                'sort_order' => 10,
            ],
            [
                'source' => 'google_drive',
                'external_id' => '1Ru6Xe5op2tuvnK_JDvQGDHSfM2e9Rww3',
                'title' => 'Introduction to Agent Skills',
                'issuer' => 'Anthropic',
                'category' => 'ai_data',
                'description' => 'Anthropic course introducing reusable agent skills and their application in Claude workflows.',
                'file_url' => 'https://anthropic.skilljar.com/introduction-to-agent-skills',
                'tags' => ['Anthropic', 'Claude', 'Agent skills', 'Artificial intelligence'],
                'sort_order' => 11,
            ],
            [
                'source' => 'anthropic',
                'external_id' => 'introduction-to-model-context-protocol',
                'title' => 'Introduction to Model Context Protocol',
                'issuer' => 'Anthropic',
                'category' => 'ai_data',
                'description' => 'Anthropic course introducing Model Context Protocol concepts and integrations.',
                'file_url' => 'https://anthropic.skilljar.com/introduction-to-model-context-protocol',
                'tags' => ['Anthropic', 'Model Context Protocol', 'MCP', 'Artificial intelligence'],
                'sort_order' => 12,
            ],
            [
                'source' => 'manual',
                'external_id' => 'omada-ocna-wireless',
                'title' => 'Omada Certified Network Administrator (OCNA) - Wireless',
                'issuer' => 'Omada by TP-Link',
                'category' => 'networking',
                'description' => 'Omada certification focused on wireless networking, deployment, configuration, and administration.',
                'file_url' => 'https://training.tp-link.com/omada',
                'tags' => ['Omada', 'TP-Link', 'OCNA', 'Wireless networking', 'Network administration'],
                'sort_order' => 13,
            ],
            [
                'source' => 'manual',
                'external_id' => 'hikvision-training-camp',
                'title' => 'Hikvision Training Camp',
                'issuer' => 'Hikvision',
                'category' => 'professional',
                'description' => 'Hikvision technical training covering video surveillance and integrated security technologies.',
                'file_url' => 'https://content.hikvision.com/htc-registration',
                'tags' => ['Hikvision', 'Video surveillance', 'Security systems', 'Technical training'],
                'sort_order' => 14,
            ],
        ];

        foreach ($certificates as $certificate) {
            DB::table('certificates')->updateOrInsert(
                [
                    'source' => $certificate['source'],
                    'external_id' => $certificate['external_id'],
                ],
                [
                    'title' => $certificate['title'],
                    'issuer' => $certificate['issuer'],
                    'category' => $certificate['category'],
                    'description' => $certificate['description'],
                    'issued_on' => null,
                    'file_url' => $certificate['file_url'],
                    'thumbnail_url' => null,
                    'local_path' => null,
                    'tags' => json_encode($certificate['tags'], JSON_THROW_ON_ERROR),
                    'sort_order' => $certificate['sort_order'],
                    'is_featured' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
            );
        }

        DB::table('certificates')
            ->whereIn('external_id', [
                '1vQ-BIcQHnCykWlorXTm27DKWA0NER9Uc',
                '13WHGH8bgi4zVN9NTJhrP78jt4Zzm2Cf_',
                '1BadLsEl0YgbZWhqVxpS1ZX0qqfdRJx3s',
                '1qP8VoAaoaM6VE0Fokhgb9L3wfmtcObAF',
                '1aACyNKcDvT94auD8z-VFwCk7rcdyaWfF',
                '1Va6rkk0DC6wRplmkK8rtwKyweoLRaNM3',
                '1NO3VpuH9SELX8mqUH313MyCSaSi1yc3a',
                '1Rk_E15ZHcHmfyzO0LJ1Br3QPoLJm6cq_',
            ])
            ->update([
                'issuer' => self::DICT_ISSUER,
                'updated_at' => $timestamp,
            ]);
    }

    public function down(): void
    {
        DB::table('certificates')
            ->whereIn('external_id', [
                'introduction-to-model-context-protocol',
                'omada-ocna-wireless',
                'hikvision-training-camp',
            ])
            ->delete();
    }
};
