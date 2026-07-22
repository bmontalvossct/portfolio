<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('achievements')
            ->where('title', 'SAP Certified - Implementation Consultant')
            ->whereNull('image_url')
            ->update(['image_url' => '/images/brands/sap.svg']);
    }

    public function down(): void
    {
        DB::table('achievements')
            ->where('title', 'SAP Certified - Implementation Consultant')
            ->where('image_url', '/images/brands/sap.svg')
            ->update(['image_url' => null]);
    }
};
