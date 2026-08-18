<?php

namespace App\Support\Profile;

class PublicCertificateExposure
{
    public static function verificationUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($scheme !== 'https' || $host === 'drive.google.com' || str_ends_with($host, '.drive.google.com')) {
            return null;
        }

        if (preg_match('/\.(?:pdf|docx?|png|jpe?g|webp)$/i', $path) === 1) {
            return null;
        }

        return $url;
    }
}
