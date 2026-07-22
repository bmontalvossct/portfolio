<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('certificates')->updateOrInsert(
            [
                'source' => 'alison',
                'external_id' => '31557408f8',
            ],
            [
                'title' => 'Lean Six Sigma: Yellow Belt',
                'issuer' => 'Alison',
                'category' => 'project_management',
                'description' => 'Verified completion of Lean Six Sigma: Yellow Belt with an 89% final assessment score. Alison ID 59724336; 0-1 CPD hours completed.',
                'issued_on' => '2026-07-20',
                'file_url' => 'https://alison.com/verify/31557408f8',
                'thumbnail_url' => 'https://cdn01.alison-static.net/public/html/vendor/img/favicon/apple-touch-icon.png',
                'local_path' => null,
                'tags' => json_encode([
                    'Alison',
                    'Lean Six Sigma',
                    'Yellow Belt',
                    'DMAIC',
                    'Process improvement',
                    'Project management',
                    '89% assessment score',
                ], JSON_THROW_ON_ERROR),
                'sort_order' => 0,
                'is_featured' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        );
    }

    public function down(): void
    {
        DB::table('certificates')
            ->where('source', 'alison')
            ->where('external_id', '31557408f8')
            ->delete();
    }
};
