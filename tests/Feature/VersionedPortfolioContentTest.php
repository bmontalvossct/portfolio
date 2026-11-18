<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Support\Profile\VersionedPortfolioContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VersionedPortfolioContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_the_versioned_content_instead_of_a_different_database_profile(): void
    {
        Profile::query()->delete();
        $content = app(VersionedPortfolioContent::class);
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('profile.display_name', $content->get('profile')['display_name'])
            ->has('projects', 6)
            ->has('achievements', 6)
            ->has('publishedWorks', 3)
            ->has('workExperiences', 5)
            ->has('education', 2)
            ->has('badges', 38)
            ->has('certificates', 51)
            ->has('portfolioTools', 37));
    }

    public function test_snapshot_exposes_only_the_intended_sections_and_maps_bundled_media(): void
    {
        $content = app(VersionedPortfolioContent::class);
        $this->assertSame([], $content->get('guestbookEntries'));
        $this->assertSame('/repository-media/portfolio/organizations/snsu.png', $content->get('workExperiences')[1]['logo_url']);
        $this->assertArrayNotHasKey('created_at', $content->get('profile'));
        foreach ($content->get('certificates') as $certificate) {
            $this->assertArrayNotHasKey('local_path', $certificate);
            $this->assertArrayNotHasKey('external_id', $certificate);
            $this->assertNull($certificate['verification_code']);
            $this->assertSame($certificate['file_url'], \App\Support\Profile\PublicCertificateExposure::verificationUrl($certificate['file_url']));
        }
    }

    public function test_certificate_snapshot_filters_private_fields_and_non_public_document_urls(): void
    {
        $provider = new VersionedPortfolioContent;
        $content = new \ReflectionProperty($provider, 'content');
        $content->setValue($provider, ['certificates' => [
            ['title' => 'Public', 'file_url' => 'https://example.org/verify/123', 'verification_code' => 'private-code', 'local_path' => '/private/document.pdf', 'external_id' => 'private-id', 'unexpected_private_field' => 'private'],
            ['title' => 'Drive', 'file_url' => 'https://drive.google.com/file/d/example/view'],
            ['title' => 'Document', 'file_url' => 'https://example.org/certificate.pdf'],
            ['title' => 'Insecure', 'file_url' => 'http://example.org/verify/123'],
        ]]);

        $certificates = $provider->get('certificates');
        $this->assertSame('https://example.org/verify/123', $certificates[0]['file_url']);
        foreach ($certificates as $certificate) {
            $this->assertNull($certificate['verification_code']);
            $this->assertArrayNotHasKey('local_path', $certificate);
            $this->assertArrayNotHasKey('external_id', $certificate);
            $this->assertArrayNotHasKey('unexpected_private_field', $certificate);
        }
        foreach (array_slice($certificates, 1) as $certificate) {
            $this->assertNull($certificate['file_url']);
        }
    }
}
