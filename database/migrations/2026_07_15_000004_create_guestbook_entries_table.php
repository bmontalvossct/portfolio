<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guestbook_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name', 120);
            $table->string('email');
            $table->string('role_or_organization')->nullable();
            $table->text('body');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_reply')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guestbook_entries');
    }
};
