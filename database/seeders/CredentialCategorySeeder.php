<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Support\Profile\CredentialCategory;
use Illuminate\Database\Seeder;

class CredentialCategorySeeder extends Seeder
{
    public function run(): void
    {
        CredentialBadge::query()->each(function (CredentialBadge $badge): void {
            $badge->update([
                'category' => CredentialCategory::classify($badge->category, [
                    $badge->name,
                    $badge->issuer,
                    $badge->description,
                    $badge->skills ?? [],
                ]),
            ]);
        });

        Certificate::query()->each(function (Certificate $certificate): void {
            $certificate->update([
                'category' => CredentialCategory::classify($certificate->category, [
                    $certificate->title,
                    $certificate->issuer,
                    $certificate->description,
                    $certificate->tags ?? [],
                ]),
            ]);
        });
    }
}
