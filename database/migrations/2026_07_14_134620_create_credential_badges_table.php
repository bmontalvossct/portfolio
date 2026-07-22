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
        Schema::create('credential_badges', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('credly');
            $table->string('external_id');
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->text('description')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('image_url')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('criteria_url')->nullable();
            $table->string('evidence_url')->nullable();
            $table->json('skills')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['is_featured', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credential_badges');
    }
};
