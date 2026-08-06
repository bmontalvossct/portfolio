<?php

namespace App\Console\Commands;

use App\Http\Controllers\PortfolioThumbnailController;
use App\Support\Profile\DesignMediaArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

#[Signature('portfolio:warm-thumbnails')]
#[Description('Generate and cache lightweight previews for local portfolio images.')]
class WarmPortfolioThumbnails extends Command
{
    public function handle(
        DesignMediaArchive $archive,
        PortfolioThumbnailController $thumbnailController,
    ): int {
        $previewUrls = $archive->all()
            ->pluck('preview_url')
            ->filter()
            ->unique()
            ->values();

        if ($previewUrls->isEmpty()) {
            $this->components->info('No local portfolio images need previews.');

            return self::SUCCESS;
        }

        $failures = 0;
        $bar = $this->output->createProgressBar($previewUrls->count());

        foreach ($previewUrls as $previewUrl) {
            $request = Request::create((string) $previewUrl, 'GET');
            $response = $thumbnailController($request);

            if (! $response->isSuccessful()) {
                $failures++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->components->info($previewUrls->count().' portfolio previews warmed.');

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }
}
