<?php

namespace App\Support\Profile;

use App\Models\Achievement;

class RecognitionCertificate
{
    public static function previewUrl(Achievement $achievement): ?string
    {
        if ($achievement->title !== 'Scam Ready ASEAN Master Trainer'
            || $achievement->issuer !== 'SmartCT / ASEAN Foundation') {
            return null;
        }

        // Only this approved first-page image is public; never expose source files or audit trails.
        return '/images/recognitions/scam-ready-asean-certificate.jpg';
    }
}
