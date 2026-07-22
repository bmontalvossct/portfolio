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
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('organization');
            $table->string('position');
            $table->string('start_date');
            $table->string('end_date')->nullable();
            $table->string('location')->nullable();
            $table->text('summary')->nullable();
            $table->json('responsibilities')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['is_current', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
