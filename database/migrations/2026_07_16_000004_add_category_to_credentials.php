<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credential_badges', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('issuer')->index();
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('issuer')->index();
        });
    }

    public function down(): void
    {
        Schema::table('credential_badges', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
