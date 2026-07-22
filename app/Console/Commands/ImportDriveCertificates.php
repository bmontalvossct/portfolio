<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Support\Profile\CredentialCategory;
use Carbon\CarbonImmutable;
use Composer\CaBundle\CaBundle;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

#[Signature('profile:sync-drive-certificates
    {file? : Optional saved public Google Drive folder HTML}
    {--folder= : Google Drive folder ID; defaults to the configured certificate folder}')]
#[Description('Synchronize new certificate files from the public Google Drive folder.')]
class ImportDriveCertificates extends Command
{
    private const EXCLUDED_TITLES = [
        'Britt Kristoff Montalvo',
        'Code Without Barrier',
        'Copy of Britt Kristoff Montalvo DICTR5:2023 W NKOC 1',
    ];

    public function handle(): int
    {
        try {
            $html = $this->folderHtml();
        } catch (Throwable $exception) {
            report($exception);
            $this->error("Could not read the certificate folder: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $records = $this->recordsFrom($html);

        if ($records === []) {
            $this->error('No PDF or image certificates were found. Check that the Drive folder is shared publicly.');

            return self::FAILURE;
        }

        $created = 0;
        $preserved = 0;

        foreach ($records as $record) {
            $title = $this->titleFor($record['filename']);

            if (in_array($title, self::EXCLUDED_TITLES, true)) {
                continue;
            }

            if (CredentialBadge::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($title)])->exists()) {
                Certificate::query()->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])->delete();

                continue;
            }

            $certificate = Certificate::query()->firstOrNew([
                'source' => 'google_drive',
                'external_id' => $record['id'],
            ]);

            if (! $certificate->exists) {
                $seededCertificate = Certificate::query()
                    ->where('source', 'portfolio_seed')
                    ->where('title', $title)
                    ->first();

                if ($seededCertificate) {
                    $seededCertificate->forceFill([
                        'source' => 'google_drive',
                        'external_id' => $record['id'],
                    ])->save();

                    $preserved++;

                    continue;
                }
            }

            if ($certificate->exists) {
                $preserved++;

                continue;
            }

            $issuer = $this->issuerFor($record['filename']);
            $category = CredentialCategory::classify(null, [$title, $issuer]);

            $certificate->fill([
                'title' => $title,
                'issuer' => $issuer,
                'category' => $category,
                'issued_on' => $this->dateFor($record['filename']),
                'file_url' => "https://drive.google.com/file/d/{$record['id']}/view",
                'thumbnail_url' => "https://drive.google.com/thumbnail?id={$record['id']}&sz=w600",
                'tags' => array_values(array_filter([$issuer, CredentialCategory::label($category), 'Certificate'])),
                'sort_order' => 0,
                'is_featured' => true,
            ])->save();

            $created++;
        }

        $this->info("Drive certificate sync complete: {$created} new, {$preserved} preserved, ".count($records).' found.');

        return self::SUCCESS;
    }

    private function folderHtml(): string
    {
        $file = $this->argument('file');

        if (filled($file)) {
            $path = File::exists(base_path($file)) ? base_path($file) : $file;

            if (! File::exists($path)) {
                throw new \RuntimeException("Folder export was not found: {$file}");
            }

            return File::get($path);
        }

        $folderId = trim((string) ($this->option('folder') ?: config('services.google_drive.certificate_folder_id')));

        if ($folderId === '') {
            throw new \RuntimeException('GOOGLE_DRIVE_CERTIFICATE_FOLDER_ID is not configured.');
        }

        if (preg_match('/\A[A-Za-z0-9_-]{20,}\z/', $folderId) !== 1) {
            throw new \RuntimeException('The configured Google Drive folder ID is invalid.');
        }

        return Http::withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
            'User-Agent' => 'Britt-Montalvo-Portfolio/1.0',
        ])
            ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
            ->accept('text/html')
            ->timeout(30)
            ->retry(3, 500)
            ->get("https://drive.google.com/drive/folders/{$folderId}")
            ->throw()
            ->body();
    }

    /**
     * @return array<int, array{id: string, filename: string}>
     */
    private function recordsFrom(string $html): array
    {
        $document = new DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        if (! $loaded) {
            return [];
        }

        $nodes = (new DOMXPath($document))->query('//*[@data-id and @data-tooltip]');
        $records = [];

        foreach ($nodes ?: [] as $node) {
            $id = trim($node->getAttribute('data-id'));
            $tooltip = html_entity_decode(trim($node->getAttribute('data-tooltip')), ENT_QUOTES | ENT_HTML5);

            if (preg_match('/\A[A-Za-z0-9_-]{20,}\z/', $id) !== 1) {
                continue;
            }

            if (preg_match('/\A(.+?\.(?:pdf|png|jpe?g|webp))(?=\s|\z)/iu', $tooltip, $match) !== 1) {
                continue;
            }

            $records[$id] = [
                'id' => $id,
                'filename' => trim($match[1]),
            ];
        }

        return array_values($records);
    }

    private function titleFor(string $filename): string
    {
        $title = Str::of($filename)
            ->replaceMatches('/\.(?:pdf|png|jpe?g|webp)\z/i', '')
            ->replaceMatches('/(?:v\d+)?(?:Update|Badge)?20\d{6}-\d+-[a-z0-9]+\z/i', '')
            ->replace(['_', '-'], ' ')
            ->replaceMatches('/\bv\d+(?:\.\d+)*\z/i', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        $title = str_replace(
            ['Googel', 'Digitial', 'ANALYSTICS', 'ESENTIALS', 'CISCO'],
            ['Google', 'Digital', 'Analytics', 'Essentials', 'Cisco'],
            $title,
        );

        $title = match ($title) {
            '(Bulk) NWMC Women Adding Value through Maximization of Online Platforms for Optimum Opportunities byElwin Argana BRITT(signed)' => 'Women Adding Value through Maximization of Online Platforms for Optimum Opportunities',
            'AIatWorkAnalyzeCustomerReviews' => 'AI at Work Analyze Customer Reviews',
            'CERTS' => 'Cyber Emergency Response Team',
            'CERT TRAINING' => 'Foundations of Cyber Emergency Response Team',
            'CreateDigitalContent' => 'Creating Digital Content',
            'ECERT MS365 Training BRITT KRISTOFF BALINSUHE MONTALVO' => 'Microsoft 365 Future Ready Skills',
            'E COMMERCE', 'Ecommerce' => 'Ecommerce Businesses Training',
            'EnglishforIT1' => 'English for IT 1',
            'EnglishforIT2' => 'English for IT 2',
            'EthicalHacker' => 'Ethical Hacker',
            default => Str::ucfirst($title),
        };

        return $this->titleCase($title);
    }

    private function titleCase(string $title): string
    {
        $title = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $title) ?? $title;
        $title = mb_convert_case($title, MB_CASE_TITLE, 'UTF-8');
        $title = preg_replace_callback(
            '/\b(?:And|At|By|For|From|In|Of|On|The|Through|To|Using|With)\b/u',
            fn (array $match): string => Str::lower($match[0]),
            $title,
        ) ?? $title;

        foreach (['5G', '6G', 'AI', 'API', 'AWS', 'DICT', 'DICTR5', 'DPA', 'GPS', 'IT', 'IoT', 'MS365', 'NKOC', 'QGIS', 'SAP'] as $abbreviation) {
            $title = preg_replace('/\b'.preg_quote($abbreviation, '/').'\b/i', $abbreviation, $title) ?? $title;
        }

        return $title;
    }

    private function issuerFor(string $filename): ?string
    {
        $value = Str::upper($filename);

        return match (true) {
            Str::contains($value, ['SANGFOR', 'ITDEPOT']) => 'ITDEPOT / Sangfor',
            Str::contains($value, ['CISCO', 'NETWORK BASICS', 'ETHICAL HACKER', 'ETHICALHACKER', 'ENDPOINT SECURITY', 'ENDPOINTSECURITY', 'CYBER THREAT MANAGEMENT', 'CYBERTHREATMANAGEMENT', 'ENGLISH FOR IT', 'ENGLISHFORIT', 'AI AT WORK', 'AIATWORK']) => 'Cisco',
            Str::contains($value, ['DEPARTMENT OF HEALTH', 'HEALTH FACILIT', 'QGIS', 'GPS DEVICES']) => 'Department of Health',
            Str::contains($value, ['GOOGLE', 'GOOGEL']) => 'Google / Coursera',
            Str::contains($value, ['SAP BUSINESS', 'SAP CERTIFIED']) => 'SAP',
            Str::contains($value, 'ANTHROPIC') => 'Anthropic',
            Str::contains($value, ['AWS', 'AMAZON WEB SERVICES']) => 'Amazon Web Services',
            Str::contains($value, ['MICROSOFT', 'MS365']) => 'Microsoft',
            Str::contains($value, ['DICT', 'CYBER RANGE', 'CYBERSECURITY COMPETENCY', 'CERT TRAINING', 'CERTS', 'CREATEDIGITALCONTENT', 'CREATE DIGITAL CONTENT', 'ECOMMERCE', 'E-COMMERCE', 'NWMC', 'DPA FRAMEWORK']) => 'Department of Information and Communications Technology',
            default => null,
        };
    }

    private function dateFor(string $filename): ?string
    {
        if (preg_match('/\b(20\d{2})(0[1-9]|1[0-2])([0-2]\d|3[01])\b/', $filename, $match) !== 1) {
            return null;
        }

        try {
            return CarbonImmutable::createSafe((int) $match[1], (int) $match[2], (int) $match[3])->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
