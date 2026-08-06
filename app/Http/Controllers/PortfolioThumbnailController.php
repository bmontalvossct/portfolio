<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PortfolioThumbnailController extends Controller
{
    private const DEFAULT_WIDTH = 720;

    private const MIN_WIDTH = 160;

    private const MAX_WIDTH = 1280;

    public function __invoke(Request $request): BinaryFileResponse|RedirectResponse
    {
        $sourceUrl = (string) $request->query('src', '');
        $relativePath = $this->relativeStoragePath($sourceUrl);

        abort_unless($relativePath, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($relativePath), 404);

        if (! function_exists('imagewebp')) {
            return redirect($sourceUrl);
        }

        $sourcePath = $disk->path($relativePath);
        $maxWidth = max(self::MIN_WIDTH, min(self::MAX_WIDTH, $request->integer('w', self::DEFAULT_WIDTH)));
        $maxHeight = (int) round($maxWidth * 1.5);
        $cacheKey = sha1($relativePath.'|'.filemtime($sourcePath).'|'.$maxWidth.'x'.$maxHeight);
        $thumbnailPath = "portfolio/generated-thumbnails/{$cacheKey}.webp";

        if (! $disk->exists($thumbnailPath)) {
            $disk->makeDirectory('portfolio/generated-thumbnails');

            if (! $this->generate($sourcePath, $disk->path($thumbnailPath), $maxWidth, $maxHeight)) {
                return redirect($sourceUrl);
            }
        }

        return response()->file($disk->path($thumbnailPath), [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function relativeStoragePath(string $sourceUrl): ?string
    {
        $path = rawurldecode((string) parse_url($sourceUrl, PHP_URL_PATH));

        if (! str_starts_with($path, '/storage/portfolio/')) {
            return null;
        }

        $relativePath = ltrim(substr($path, strlen('/storage/')), '/');

        if (str_contains($relativePath, '..') || str_contains($relativePath, "\0")) {
            return null;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            ? $relativePath
            : null;
    }

    private function generate(string $sourcePath, string $thumbnailPath, int $maxWidth, int $maxHeight): bool
    {
        $details = @getimagesize($sourcePath);

        if (! $details || $details[0] < 1 || $details[1] < 1) {
            return false;
        }

        $estimatedBytes = ($details[0] * $details[1] * 7) + (64 * 1024 * 1024);

        if (! $this->ensureMemoryAvailable($estimatedBytes)) {
            return false;
        }

        $source = match ($details[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if (! $source) {
            return false;
        }

        $scale = min(1, $maxWidth / $details[0], $maxHeight / $details[1]);
        $width = max(1, (int) round($details[0] * $scale));
        $height = max(1, (int) round($details[1] * $scale));
        $thumbnail = imagecreatetruecolor($width, $height);

        if (! $thumbnail) {
            imagedestroy($source);

            return false;
        }

        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
        imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, $details[0], $details[1]);
        $saved = imagewebp($thumbnail, $thumbnailPath, 78);

        imagedestroy($thumbnail);
        imagedestroy($source);

        return $saved;
    }

    private function ensureMemoryAvailable(int $requiredBytes): bool
    {
        $limit = ini_get('memory_limit');

        if ($limit === false || $limit === '-1') {
            return true;
        }

        $multiplier = match (strtolower(substr($limit, -1))) {
            'g' => 1024 * 1024 * 1024,
            'm' => 1024 * 1024,
            'k' => 1024,
            default => 1,
        };
        $currentBytes = (int) $limit * $multiplier;

        if ($currentBytes >= $requiredBytes) {
            return true;
        }

        $maximumBytes = 512 * 1024 * 1024;

        if ($requiredBytes > $maximumBytes) {
            return false;
        }

        @ini_set('memory_limit', (string) ceil($requiredBytes / 1024 / 1024).'M');

        return (int) ini_get('memory_limit') * 1024 * 1024 >= $requiredBytes;
    }
}
