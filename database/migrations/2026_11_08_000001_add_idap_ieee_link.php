<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('published_works')
            ->where('type', 'scopus_paper')
            ->where('title', 'Forecasting Child Immunization Coverage and Predicting Underperformance Risk Using a Hybrid Time-Series Framework: Evidence from FHSIS Data')
            ->whereNull('external_url')
            ->update(['external_url' => 'https://ieeexplore.ieee.org/document/11727904']);
    }

    public function down(): void
    {
        // Keep user-supplied links when rolling back application migrations.
    }
};
