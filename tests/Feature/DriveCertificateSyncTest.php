<?php

namespace Tests\Feature;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DriveCertificateSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_new_drive_files_without_overwriting_curated_metadata(): void
    {
        config()->set('services.google_drive.certificate_folder_id', 'folder12345678901234567890');

        $existing = Certificate::query()->create([
            'source' => 'google_drive',
            'external_id' => 'existing12345678901234567890',
            'title' => 'Curated Sangfor Training Title',
            'issuer' => 'ITDEPOT / Sangfor',
            'category' => 'cybersecurity',
            'description' => 'Curated in the admin.',
            'issued_on' => '2026-03-25',
            'file_url' => 'https://example.test/curated-certificate',
            'thumbnail_url' => 'https://example.test/curated-thumbnail',
            'tags' => ['Curated'],
            'sort_order' => 7,
            'is_featured' => false,
        ]);

        Http::fake([
            'drive.google.com/drive/folders/*' => Http::response(<<<'HTML'
                <html><body>
                    <div data-id="newfile12345678901234567890" data-tooltip="Cloud Security Fundamentals.pdf PDF"></div>
                    <div data-id="imagefile123456789012345678" data-tooltip="Regional Research Workshop.jpg JPEG"></div>
                    <div data-id="existing12345678901234567890" data-tooltip="Renamed Sangfor Certificate.pdf PDF"></div>
                    <div data-id="newfile12345678901234567890" data-tooltip="Cloud Security Fundamentals.pdf PDF"></div>
                    <div data-id="ignored12345678901234567890" data-tooltip="Notes.txt Text"></div>
                </body></html>
                HTML),
        ]);

        $this->artisan('profile:sync-drive-certificates')
            ->expectsOutput('Drive certificate sync complete: 2 new, 1 preserved, 3 found.')
            ->assertSuccessful();

        $existing->refresh();

        $this->assertSame('Curated Sangfor Training Title', $existing->title);
        $this->assertSame('https://example.test/curated-certificate', $existing->file_url);
        $this->assertSame(['Curated'], $existing->tags);
        $this->assertFalse($existing->is_featured);

        $created = Certificate::query()->where('external_id', 'newfile12345678901234567890')->firstOrFail();

        $this->assertSame('Cloud Security Fundamentals', $created->title);
        $this->assertSame('cybersecurity', $created->category);
        $this->assertSame('https://drive.google.com/file/d/newfile12345678901234567890/view', $created->file_url);
        $this->assertSame('https://drive.google.com/thumbnail?id=newfile12345678901234567890&sz=w600', $created->thumbnail_url);
        $this->assertTrue($created->is_featured);

        $image = Certificate::query()->where('external_id', 'imagefile123456789012345678')->firstOrFail();

        $this->assertSame('Regional Research Workshop', $image->title);
        $this->assertSame('https://drive.google.com/file/d/imagefile123456789012345678/view', $image->file_url);
        $this->assertTrue($image->is_featured);

        Http::assertSentCount(1);
    }

    public function test_sync_fails_safely_when_the_folder_contains_no_certificate_files(): void
    {
        config()->set('services.google_drive.certificate_folder_id', 'folder12345678901234567890');
        $certificateCountBeforeSync = Certificate::query()->count();

        Http::fake([
            'drive.google.com/drive/folders/*' => Http::response('<html><body><div>No files</div></body></html>'),
        ]);

        $this->artisan('profile:sync-drive-certificates')
            ->expectsOutput('No PDF or image certificates were found. Check that the Drive folder is shared publicly.')
            ->assertFailed();

        $this->assertDatabaseCount('certificates', $certificateCountBeforeSync);
    }
    public function test_sync_excludes_private_files_and_normalizes_requested_titles(): void
    {
        config()->set('services.google_drive.certificate_folder_id', 'folder12345678901234567890');

        $filenames = [
            'Britt Kristoff Montalvo.pdf',
            'CODE WITHOUT BARRIER.pdf',
            'Copy of BRITT KRISTOFF MONTALVO DICTR5:2023 W NKOC 1.pdf',
            '(Bulk) NWMC Women Adding Value through Maximization of Online Platforms for Optimum Opportunities byElwin Argana BRITT(signed).pdf',
            'AIatWorkAnalyzeCustomerReviews.pdf',
            'CERTS.pdf',
            'CERT TRAINING.pdf',
            'CreateDigitalContent.pdf',
            'ECERT MS365 Training BRITT KRISTOFF BALINSUHE MONTALVO.pdf',
            'E COMMERCE.pdf',
            'EnglishforIT1.pdf',
            'EnglishforIT2.pdf',
            'EthicalHacker.pdf',
            '5G CYBERSECURITY STANDARDS AND SOLUTIONS AND 6G NEW TECHNOLOGY INNOVATION.pdf',
            'CyberThreatManagement.pdf',
            'EndpointSecurity.pdf',
            'Cisco NETWORK BASICS.pdf',
        ];

        $nodes = collect($filenames)
            ->map(fn (string $filename, int $index): string => sprintf(
                '<div data-id="normalized%02d12345678901234567890" data-tooltip="%s PDF"></div>',
                $index,
                e($filename),
            ))
            ->implode('');

        Http::fake([
            'drive.google.com/drive/folders/*' => Http::response("<html><body>{$nodes}</body></html>"),
        ]);

        $this->artisan('profile:sync-drive-certificates')
            ->expectsOutput('Drive certificate sync complete: 14 new, 0 preserved, 17 found.')
            ->assertSuccessful();

        $imported = Certificate::query()
            ->where('source', 'google_drive')
            ->where('external_id', 'like', 'normalized%')
            ->get();

        $this->assertCount(14, $imported);
        $this->assertDatabaseMissing('certificates', ['external_id' => 'normalized0012345678901234567890']);
        $this->assertDatabaseMissing('certificates', ['external_id' => 'normalized0112345678901234567890']);
        $this->assertDatabaseMissing('certificates', ['external_id' => 'normalized0212345678901234567890']);
        $this->assertEqualsCanonicalizing([
            'Women Adding Value through Maximization of Online Platforms for Optimum Opportunities',
            'AI at Work Analyze Customer Reviews',
            'Cyber Emergency Response Team',
            'Foundations of Cyber Emergency Response Team',
            'Creating Digital Content',
            'Microsoft 365 Future Ready Skills',
            'Ecommerce Businesses Training',
            'English for IT 1',
            'English for IT 2',
            'Ethical Hacker',
            '5G Cybersecurity Standards and Solutions and 6G New Technology Innovation',
            'Cyber Threat Management',
            'Endpoint Security',
            'Cisco Network Basics',
        ], $imported->pluck('title')->all());
    }
}
