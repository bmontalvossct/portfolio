<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guestbook_entries', function (Blueprint $table): void {
            $table->string('notification_status', 20)->default('pending')->after('status');
            $table->timestamp('notification_attempted_at')->nullable()->after('replied_at');
            $table->timestamp('notification_accepted_at')->nullable()->after('notification_attempted_at');
            $table->text('notification_error')->nullable()->after('notification_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('guestbook_entries', function (Blueprint $table): void {
            $table->dropColumn([
                'notification_status',
                'notification_attempted_at',
                'notification_accepted_at',
                'notification_error',
            ]);
        });
    }
};
