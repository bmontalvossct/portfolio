<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')
            ->where('title', 'SDN Electronic Medical Records Dashboard')
            ->where(fn (Builder $query) => $query->whereNull('thumbnail_url')->orWhere('thumbnail_url', ''))
            ->update(['thumbnail_url' => '/images/projects/sdn-emr-dashboard-preview.png']);
    }

    public function down(): void
    {
        // Retain the supplied preview and any subsequent admin changes when rolling back code.
    }
};
