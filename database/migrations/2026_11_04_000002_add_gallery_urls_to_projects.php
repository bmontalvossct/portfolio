<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->json('gallery_urls')->nullable();
        });

        DB::table('projects')->where('title', 'ClassCheck')->whereNull('gallery_urls')->get(['id', 'title'])
            ->filter(fn (object $project): bool => $project->title === 'ClassCheck')
            ->each(fn (object $project) => DB::table('projects')->where('id', $project->id)->whereNull('gallery_urls')->update([
                'gallery_urls' => json_encode(['/images/projects/classcheck-home.jpg', '/images/projects/classcheck-features.jpg']),
            ]));
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropColumn('gallery_urls');
        });
    }
};
