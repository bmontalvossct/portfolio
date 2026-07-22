<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\Profile;
use App\Support\Profile\CertificateProvider;
use App\Support\Profile\CredentialCategory;
use App\Support\Profile\PublicCertificateExposure;
use Inertia\Inertia;
use Inertia\Response;

class CredentialArchiveController extends Controller
{
    public function badges(): Response
    {
        $items = CredentialBadge::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('issued_at')
            ->orderBy('sort_order')
            ->get()
            ->map(function (CredentialBadge $badge): array {
                $category = CredentialCategory::classify($badge->category, [
                    $badge->name,
                    $badge->issuer,
                    $badge->description,
                    $badge->skills ?? [],
                ]);

                return [
                    'id' => $badge->id,
                    'title' => $badge->name,
                    'issuer' => $badge->issuer,
                    'description' => $badge->description,
                    'date' => $badge->issued_at?->format('M Y'),
                    'expires_at' => $badge->expires_at?->format('M Y'),
                    'image_url' => $badge->image_url,
                    'verify_url' => $badge->certificate_url,
                    'criteria_url' => $badge->criteria_url,
                    'evidence_url' => $badge->evidence_url,
                    'tags' => $badge->skills ?? [],
                    'category' => $category,
                    'category_label' => CredentialCategory::label($category),
                ];
            });

        return $this->renderArchive(
            archiveType: 'badges',
            title: 'credential badges',
            intro: 'Verified digital badges across data, software, cybersecurity, networking, enterprise platforms, and professional practice.',
            items: $items->all(),
            alternate: ['label' => 'Certificate archive', 'url' => '/certifications'],
        );
    }

    public function certificates(): Response
    {
        $items = Certificate::query()
            ->orderByDesc('is_featured')
            ->orderByDesc('issued_on')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Certificate $certificate): array {
                $category = CredentialCategory::classify($certificate->category, [
                    $certificate->title,
                    $certificate->issuer,
                    $certificate->description,
                    $certificate->tags ?? [],
                ]);
                $provider = CertificateProvider::for($certificate);

                return [
                    'id' => $certificate->id,
                    'title' => $certificate->title,
                    'issuer' => $certificate->issuer,
                    'description' => $certificate->description,
                    'date' => $certificate->issued_on?->format('M Y'),
                    'expires_at' => null,
                    'image_url' => $provider['logo_url'],
                    'provider_name' => $provider['name'],
                    'provider_initials' => $provider['initials'],
                    'verification_code' => $certificate->verification_code,
                    'verify_url' => PublicCertificateExposure::verificationUrl($certificate->file_url),
                    'criteria_url' => null,
                    'evidence_url' => null,
                    'tags' => $certificate->tags ?? [],
                    'category' => $category,
                    'category_label' => CredentialCategory::label($category),
                ];
            });

        return $this->renderArchive(
            archiveType: 'certificates',
            title: 'Certifications',
            intro: 'Certificates from formal training, professional development, technical programs, and continuing education. Original documents are withheld for privacy; verified public credential pages remain available where provided.',
            items: $items->all(),
            alternate: ['label' => 'Digital badge archive', 'url' => '/badges'],
        );
    }

    private function renderArchive(string $archiveType, string $title, string $intro, array $items, array $alternate): Response
    {
        $profile = Profile::query()->firstOrFail();

        return Inertia::render('Profile/CredentialArchive', [
            'archiveType' => $archiveType,
            'title' => $title,
            'intro' => $intro,
            'items' => $items,
            'categories' => CredentialCategory::optionList(),
            'alternate' => $alternate,
            'profile' => [
                'display_name' => $profile->display_name,
                'avatar_url' => $profile->avatar_url,
                'credly_username' => $profile->credly_username,
                'external_links' => $profile->external_links ?? [],
            ],
        ]);
    }
}
