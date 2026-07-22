<?php

namespace App\Support\Profile;

use App\Models\Certificate;
use Illuminate\Support\Str;

class CertificateProvider
{
    /** @return array{name: string, logo_url: ?string, initials: string} */
    public static function for(Certificate $certificate): array
    {
        $value = Str::upper(trim(implode(' ', array_filter([$certificate->issuer, $certificate->title]))));

        [$name, $logoUrl] = match (true) {
            Str::contains($value, 'AGILE PROJECT MANAGEMENT') => ['Google', 'https://api.iconify.design/logos/google-icon.svg'],
            Str::contains($value, ['SANGFOR', 'ITDEPOT']) => ['ITDEPOT / Sangfor', '/storage/portfolio/organizations/sangfor.png'],
            Str::contains($value, ['OMADA', 'TP-LINK', 'OCNA']) => ['Omada by TP-Link', '/storage/portfolio/organizations/tp-link.svg'],
            Str::contains($value, 'HIKVISION') => ['Hikvision', '/storage/portfolio/organizations/hikvision.svg'],
            Str::contains($value, 'ALISON') => ['Alison', 'https://cdn01.alison-static.net/public/html/vendor/img/favicon/apple-touch-icon.png'],
            Str::contains($value, 'ANTHROPIC') => ['Anthropic', 'https://cdn.simpleicons.org/anthropic?viewbox=auto'],
            Str::contains($value, ['AMAZON WEB SERVICES', 'AWS']) => ['Amazon Web Services', 'https://api.iconify.design/logos/aws.svg'],
            Str::contains($value, ['MICROSOFT', 'MS365']) => ['Microsoft', 'https://api.iconify.design/logos/microsoft-icon.svg'],
            Str::contains($value, ['DEPARTMENT OF HEALTH', 'HEALTH FACILITIES', 'QGIS', 'GPS DEVICES']) => ['Department of Health', '/storage/portfolio/organizations/department-of-health.png'],
            Str::contains($value, ['CISCO', 'ENDPOINT SECURITY', 'CYBER THREAT MANAGEMENT', 'ETHICAL HACKER', 'ENGLISH FOR IT', 'AI AT WORK']) => ['Cisco', 'https://cdn.simpleicons.org/cisco?viewbox=auto'],
            Str::contains($value, ['SAP', 'END-TO-END BUSINESS', 'END TO END BUSINESS']) => ['SAP', '/images/brands/sap.svg'],
            Str::contains($value, ['GOOGLE', 'DATA ANALYTICS ASK DATA DRIVEN DECISIONS', 'CONFIGURATION MANAGEMENT AND THE CLOUD']) => ['Google / Coursera', 'https://api.iconify.design/logos/google-icon.svg'],
            Str::contains($value, ['5G CYBERSECURITY', '6G NEW TECHNOLOGY', 'ENTERPRISE SECURITY GOVERNANCE']) => ['Huawei', 'https://cdn.simpleicons.org/huawei?viewbox=auto'],
            Str::contains($value, ['DEPARTMENT OF INFORMATION AND COMMUNICATIONS TECHNOLOGY', 'CYBER RANGE', 'CYBERSECURITY COMPETENCY', 'DPA FRAMEWORK', 'CYBER EMERGENCY RESPONSE TEAM', 'CREATING DIGITAL CONTENT', 'ECOMMERCE BUSINESSES', 'WOMEN ADDING VALUE']) => ['DICT', '/storage/portfolio/organizations/dict.svg'],
            default => [$certificate->issuer ?: 'Professional Training', null],
        };

        return [
            'name' => $name,
            'logo_url' => $logoUrl,
            'initials' => self::initials($name),
        ];
    }

    private static function initials(string $name): string
    {
        return Str::of($name)
            ->replaceMatches('/[^A-Za-z0-9]+/', ' ')
            ->explode(' ')
            ->filter()
            ->take(3)
            ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }
}