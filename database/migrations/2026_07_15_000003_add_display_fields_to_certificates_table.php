<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->text('description')->nullable()->after('issuer');
            $table->string('thumbnail_url')->nullable()->after('file_url');
            $table->boolean('is_featured')->default(false)->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['description', 'thumbnail_url', 'is_featured']);
        });
    }
};
