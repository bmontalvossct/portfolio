<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('work_experiences')
            ->where('organization', 'Surigao del Norte State University')
            ->where('position', 'Information Systems Analyst I')
            ->update([
                'end_date' => '2026',
                'is_current' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('work_experiences')
            ->where('organization', 'Surigao del Norte State University')
            ->where('position', 'Information Systems Analyst I')
            ->update([
                'end_date' => null,
                'is_current' => true,
                'updated_at' => now(),
            ]);
    }
};