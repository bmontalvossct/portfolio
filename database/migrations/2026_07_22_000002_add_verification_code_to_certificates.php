<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->string('verification_code')->nullable()->after('external_id');
        });

        DB::table('certificates')
            ->where('source', 'manual')
            ->where('external_id', 'omada-ocna-wireless')
            ->update([
                'verification_code' => '57E94B6682EC4E95',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropColumn('verification_code');
        });
    }
};