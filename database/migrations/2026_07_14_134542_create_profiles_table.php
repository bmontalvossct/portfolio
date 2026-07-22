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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->string('headline');
            $table->string('location')->nullable();
            $table->string('email')->nullable();
            $table->text('bio');
            $table->string('avatar_url')->nullable();
            $table->string('credly_username')->default('brittm');
            $table->json('external_links')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
