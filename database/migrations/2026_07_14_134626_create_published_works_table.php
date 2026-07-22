<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('published_works', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->string('publication')->nullable();
            $table->string('role')->nullable();
            $table->text('summary')->nullable();
            $table->date('published_on')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('doi')->nullable();
            $table->string('external_url')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('published_works');
    }
};
