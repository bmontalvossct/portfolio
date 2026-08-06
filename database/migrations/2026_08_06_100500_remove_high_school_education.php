<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('education')
            ->where('institution', 'San Nicolas Academy')
            ->where('program', 'High School')
            ->delete();
    }

    public function down(): void
    {
        $exists = DB::table('education')
            ->where('institution', 'San Nicolas Academy')
            ->where('program', 'High School')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('education')->insert([
            'institution' => 'San Nicolas Academy',
            'program' => 'High School',
            'level' => 'Secondary education',
            'start_date' => 'June 2011',
            'end_date' => 'March 2015',
            'location' => null,
            'description' => null,
            'activities' => null,
            'sort_order' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
