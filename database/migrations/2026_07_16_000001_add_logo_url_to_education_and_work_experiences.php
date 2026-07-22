<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('institution');
        });

        Schema::table('work_experiences', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('organization');
        });
    }

    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });

        Schema::table('work_experiences', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
