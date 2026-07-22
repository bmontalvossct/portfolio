<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('canva_url')->nullable()->after('github_username');
        });

        Schema::create('design_media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('media_type', 24);
            $table->text('description')->nullable();
            $table->string('year', 20)->nullable();
            $table->string('media_url');
            $table->string('thumbnail_url')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['media_type', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('design_media');

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('canva_url');
        });
    }
};
