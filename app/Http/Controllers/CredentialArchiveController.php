<?php

namespace App\Http\Controllers;

use App\Support\Profile\CredentialCategory;
use App\Support\Profile\PublicCertificateExposure;
use App\Support\Profile\VersionedPortfolioContent;
use Inertia\Inertia;
use Inertia\Response;

class CredentialArchiveController extends Controller
{
    public function badges(): Response
    {
        $versionedBadges = app(VersionedPortfolioContent::class)->get('badges');
        $items = collect($versionedBadges)
            ->sort(function (array $a, array $b): int {
                return ((int) ($b['is_featured'] ?? false) <=> (int) ($a['is_featured'] ?? false))
                    ?: strcmp($b['issued_at_sort'] ?? '', $a['issued_at_sort'] ?? '')
                    ?: (($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
            })
            ->map(fn (array $badge): array => [
                'id' => $badge['id'],
                'title' => $badge['name'],
                'issuer' => $badge['issuer'],
                'description' => $badge['description'],
                'date' => $badge['issued_at'],
                'expires_at' => $badge['expires_at'],
                'image_url' => $badge['image_url'],
                'verify_url' => $badge['certificate_url'],
                'criteria_url' => $badge['criteria_url'],
                'evidence_url' => $badge['evidence_url'],
                'tags' => $badge['skills'] ?? [],
                'category' => $badge['category'],
                'category_label' => $badge['category_label'],
            ])->values();

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
        $versionedCertificates = app(VersionedPortfolioContent::class)->get('certificates');
        $items = collect($versionedCertificates)
            ->sort(function (array $a, array $b): int {
                return ((int) ($b['is_featured'] ?? false) <=> (int) ($a['is_featured'] ?? false))
                    ?: strcmp($b['issued_on_sort'] ?? '', $a['issued_on_sort'] ?? '')
                    ?: (($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0))
                    ?: strcmp($b['created_at_sort'] ?? '', $a['created_at_sort'] ?? '');
            })
            ->map(fn (array $certificate): array => [
                'id' => $certificate['id'],
                'title' => $certificate['title'],
                'issuer' => $certificate['issuer'],
                'description' => $certificate['description'],
                'date' => $certificate['issued_on'],
                'expires_at' => null,
                'image_url' => $certificate['thumbnail_url'],
                'provider_name' => $certificate['provider_name'],
                'provider_initials' => $certificate['provider_initials'],
                'verification_code' => null,
                'verify_url' => PublicCertificateExposure::verificationUrl($certificate['file_url'] ?? null),
                'criteria_url' => null,
                'evidence_url' => null,
                'tags' => $certificate['tags'] ?? [],
                'category' => $certificate['category'],
                'category_label' => $certificate['category_label'],
            ])->values();

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
        $profile = (object) app(VersionedPortfolioContent::class)->get('profile');

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
