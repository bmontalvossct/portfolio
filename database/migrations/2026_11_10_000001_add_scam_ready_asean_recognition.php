<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $recognition = DB::table('achievements')
            ->where('title', 'Scam Ready ASEAN Master Trainer')
            ->where('issuer', 'SmartCT / ASEAN Foundation');

        if (! $recognition->exists()) {
            DB::table('achievements')->insert([
                'title' => 'Scam Ready ASEAN Master Trainer',
                'issuer' => 'SmartCT / ASEAN Foundation',
                'summary' => 'Recognized and authorized as a Master Trainer for the Scam Ready ASEAN Programme under SmartCT, delivering digital literacy and safety training and Be Scam Ready gameplay activities. Valid until December 2027.',
                'achieved_on' => null,
                'image_url' => '/images/recognitions/scam-ready-asean-logo.png',
                'external_url' => 'https://aseanfoundation.org/programme/scam-ready-asean/',
                'tags' => json_encode(['Master Trainer', 'Digital literacy', 'Online safety'], JSON_THROW_ON_ERROR),
                'sort_order' => 5,
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep the recognition and any later admin edits when rolling back code.
    }
};
