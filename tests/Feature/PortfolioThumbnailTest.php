<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortfolioThumbnailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_a_cached_webp_preview_for_a_local_portfolio_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->putFileAs(
            'portfolio/Images',
            UploadedFile::fake()->image('large.jpg', 2400, 1800),
            'large.jpg',
        );

        $response = $this->get(route('portfolio-thumbnail', [
            'src' => '/storage/portfolio/Images/large.jpg',
            'w' => 320,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
        $this->assertStringContainsString(
            'max-age=31536000',
            (string) $response->headers->get('Cache-Control'),
        );

        $thumbnails = Storage::disk('public')->files('portfolio/generated-thumbnails');
        $this->assertCount(1, $thumbnails);
        $dimensions = getimagesize(Storage::disk('public')->path($thumbnails[0]));
        $this->assertNotFalse($dimensions);
        $this->assertLessThanOrEqual(320, $dimensions[0]);
        $this->assertLessThanOrEqual(480, $dimensions[1]);

        $this->get(route('portfolio-thumbnail', [
            'src' => '/storage/portfolio/Images/large.jpg',
            'w' => 320,
        ]))->assertOk();
        $this->assertCount(1, Storage::disk('public')->files('portfolio/generated-thumbnails'));
    }

    public function test_it_rejects_non_portfolio_and_remote_image_paths(): void
    {
        $this->get(route('portfolio-thumbnail', ['src' => 'https://example.test/image.jpg']))->assertNotFound();
        $this->get(route('portfolio-thumbnail', ['src' => '/storage/private/image.jpg']))->assertNotFound();
    }
}
