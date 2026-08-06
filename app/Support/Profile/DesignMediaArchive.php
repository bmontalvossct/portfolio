<?php

namespace App\Support\Profile;

use App\Models\DesignMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesignMediaArchive
{
    public function all(): Collection
    {
        $managedMedia = DesignMedia::query()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (DesignMedia $item): array => [
                'id' => $item->id,
                'title' => $item->title,
                'media_type' => $item->media_type,
                'type_label' => str($item->media_type)->title()->toString(),
                'description' => $item->description,
                'year' => $item->year,
                'media_url' => $item->media_url,
                'thumbnail_url' => $item->thumbnail_url,
                'preview_url' => $this->previewUrl($item->media_url, $item->media_type),
                'external_url' => $item->external_url,
                'is_featured' => $item->is_featured,
            ]);

        return $managedMedia
            ->concat($this->storedMedia($managedMedia))
            ->values();
    }

    private function storedMedia(Collection $managedMedia): Collection
    {
        if (app()->runningUnitTests() && ! config('portfolio.discover_stored_media_in_tests', false)) {
            return collect();
        }

        $managedUrls = $managedMedia
            ->pluck('media_url')
            ->filter()
            ->map(fn (string $url): string => rawurldecode($url))
            ->all();

        $folders = [
            'portfolio/Images' => ['image', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'pdf']],
            'portfolio/Videos' => ['video', ['mp4', 'webm', 'mov', 'm4v']],
        ];

        return collect($folders)->flatMap(function (array $definition, string $folder) use ($managedUrls): Collection {
            [$mediaType, $extensions] = $definition;

            return collect(Storage::disk('public')->files($folder))
                ->filter(fn (string $path): bool => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), $extensions, true))
                ->reject(fn (string $path): bool => Str::lower(pathinfo($path, PATHINFO_FILENAME)) === 'profile video')
                ->sortBy(fn (string $path): string => strtolower(basename($path)))
                ->reject(fn (string $path): bool => in_array(rawurldecode(Storage::disk('public')->url($path)), $managedUrls, true))
                ->map(function (string $path) use ($mediaType): array {
                    $filename = pathinfo($path, PATHINFO_FILENAME);
                    $resolvedMediaType = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : $mediaType;

                    return [
                        'id' => 'stored-'.substr(sha1($path), 0, 12),
                        'title' => Str::of($filename)->replace(['_', '-'], ' ')->squish()->toString(),
                        'media_type' => $resolvedMediaType,
                        'type_label' => $resolvedMediaType === 'pdf' ? 'Brochure PDF' : Str::title($resolvedMediaType),
                        'description' => null,
                        'year' => null,
                        'media_url' => $this->publicUrl($path),
                        'thumbnail_url' => $this->storedThumbnailUrl($path, $resolvedMediaType),
                        'preview_url' => $this->previewUrl($this->publicUrl($path), $resolvedMediaType),
                        'external_url' => null,
                        'is_featured' => false,
                    ];
                });
        });
    }

    private function publicUrl(string $path): string
    {
        return '/storage/'.collect(explode('/', str_replace('\\', '/', $path)))
            ->map(fn (string $segment): string => rawurlencode($segment))
            ->implode('/');
    }

    private function previewUrl(string $mediaUrl, string $mediaType): ?string
    {
        if ($mediaType !== 'image' || ! str_starts_with($mediaUrl, '/storage/portfolio/')) {
            return null;
        }

        return route('portfolio-thumbnail', ['src' => $mediaUrl, 'w' => 720], false);
    }

    private function storedThumbnailUrl(string $path, string $mediaType): ?string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);

        if ($mediaType === 'pdf') {
            $previewPath = 'images/design-previews/'.Str::slug($filename).'.jpg';

            return is_file(public_path($previewPath)) ? "/{$previewPath}" : null;
        }

        if ($mediaType !== 'video') {
            return null;
        }

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $thumbnailPath = "portfolio/design-thumbnails/{$filename}.{$extension}";

            if (Storage::disk('public')->exists($thumbnailPath)) {
                return $this->publicUrl($thumbnailPath);
            }
        }

        return null;
    }
}
