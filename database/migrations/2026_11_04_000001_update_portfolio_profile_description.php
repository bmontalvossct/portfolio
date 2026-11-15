<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_BIO = 'I am a DICT Registered Cybersecurity Professional (Intermediate Level 2) and Information Systems Analyst working across software engineering, health technology implementation, network security, and visual publishing. Creator of ClassCheck, an open-source classroom management system.';

    private const NEW_BIO = 'Specializing in full-stack web development, cybersecurity, network infrastructure, AI-powered solutions, data analytics, and workflow automation. Helping businesses, educational institutions, and organizations build secure, scalable, and user-friendly digital solutions that streamline operations, improve efficiency, and support digital transformation.';

    public function up(): void
    {
        DB::table('profiles')->where('slug', 'britt-m')->where('bio', self::OLD_BIO)->update(['bio' => self::NEW_BIO]);
    }

    public function down(): void
    {
        DB::table('profiles')->where('slug', 'britt-m')->where('bio', self::NEW_BIO)->update(['bio' => self::OLD_BIO]);
    }
};
