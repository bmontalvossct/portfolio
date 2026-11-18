<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Support\Profile\PublicCertificateExposure;
use App\Support\Profile\RecognitionCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScamReadyAseanRecognitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_migration_adds_the_recognition_once_with_no_invented_issue_date(): void
    {
        $otherId = DB::table('achievements')->insertGetId(['title' => 'Existing recognition', 'sort_order' => 2]);
        $other = DB::table('achievements')->find($otherId);
        $this->migration()->up();
        $this->migration()->up();

        $rows = Achievement::query()->where('title', 'Scam Ready ASEAN Master Trainer')->get();
        $this->assertCount(1, $rows);
        $recognition = $rows->first();
        $this->assertSame('SmartCT / ASEAN Foundation', $recognition->issuer);
        $this->assertNull($recognition->achieved_on);
        $this->assertTrue($recognition->is_featured);
        $this->assertSame(5, $recognition->sort_order);
        $this->assertSame('/images/recognitions/scam-ready-asean-logo.png', $recognition->image_url);
        $this->assertSame('https://aseanfoundation.org/programme/scam-ready-asean/', $recognition->external_url);
        $this->assertSame('Recognized and authorized as a Master Trainer for the Scam Ready ASEAN Programme under SmartCT, delivering digital literacy and safety training and Be Scam Ready gameplay activities. Valid until December 2027.', $recognition->summary);
        $this->assertSame(['Master Trainer', 'Digital literacy', 'Online safety'], $recognition->tags);
        $this->assertEquals($other, DB::table('achievements')->find($otherId));
    }

    public function test_reapplying_or_rolling_back_preserves_admin_edits(): void
    {
        $recognition = Achievement::query()->where('title', 'Scam Ready ASEAN Master Trainer')->firstOrFail();
        $recognition->update(['summary' => 'Admin copy.', 'image_url' => '/custom-logo.png', 'sort_order' => 9, 'is_featured' => false]);
        $before = DB::table('achievements')->find($recognition->id);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertSame(1, Achievement::query()->where('title', 'Scam Ready ASEAN Master Trainer')->count());
        $this->assertEquals($before, DB::table('achievements')->find($recognition->id));
    }

    public function test_only_the_exact_allowlisted_recognition_has_the_fixed_preview(): void
    {
        $recognition = new Achievement([
            'title' => 'Scam Ready ASEAN Master Trainer',
            'issuer' => 'SmartCT / ASEAN Foundation',
            'image_url' => '/storage/private/source-certificate.pdf',
            'external_url' => 'https://drive.google.com/file/d/private-source/view',
        ]);
        $this->assertSame('/images/recognitions/scam-ready-asean-certificate.jpg', RecognitionCertificate::previewUrl($recognition));

        foreach ([
            ['title' => 'Another recognition', 'issuer' => 'SmartCT / ASEAN Foundation'],
            ['title' => 'Scam Ready ASEAN Master Trainer', 'issuer' => 'Another issuer'],
            ['title' => 'scam ready asean master trainer', 'issuer' => 'SmartCT / ASEAN Foundation'],
            ['title' => 'Scam Ready ASEAN Master Trainer', 'issuer' => null],
        ] as $identity) {
            $this->assertNull(RecognitionCertificate::previewUrl(new Achievement($identity)));
        }

        $this->assertNull(PublicCertificateExposure::verificationUrl('/images/recognitions/scam-ready-asean-certificate.jpg'));
        $this->assertNull(PublicCertificateExposure::verificationUrl('https://drive.google.com/file/d/private-source/view'));
        $this->assertNull(PublicCertificateExposure::verificationUrl('https://example.test/certificate.pdf'));
        $this->assertSame('https://example.test/verify/123', PublicCertificateExposure::verificationUrl('https://example.test/verify/123'));
    }

    public function test_the_homepage_exposes_the_optional_preview_without_source_document_fields(): void
    {
        $this->withoutVite();
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('achievements', function ($items): bool {
                    $items = collect($items);
                    $recognition = $items->firstWhere('title', 'Scam Ready ASEAN Master Trainer');

                    return $recognition !== null
                        && $recognition['certificate_preview_url'] === '/images/recognitions/scam-ready-asean-certificate.jpg'
                        && $recognition['achieved_on'] === null
                        && $items->every(fn ($item): bool => ! array_key_exists('file_url', $item)
                            && ! array_key_exists('local_path', $item)
                            && ! array_key_exists('audit_trail', $item)
                            && ($item['title'] === 'Scam Ready ASEAN Master Trainer' || $item['certificate_preview_url'] === null));
                })
            );
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_11_10_000001_add_scam_ready_asean_recognition.php');
    }
}
