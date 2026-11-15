<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_page_views', function (Blueprint $table): void {
            $table->date('day')->primary();
            $table->unsignedBigInteger('count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_page_views');
    }
};
