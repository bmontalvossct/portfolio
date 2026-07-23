<?php

return [
    'base_url' => rtrim((string) env('SEO_CANONICAL_URL', env('APP_URL', 'https://brittmontalvo.dev')), '/'),
    'site_name' => 'Britt Montalvo',
    'locale' => 'en_PH',
    'default_image' => null,
    'pages' => [
        '/' => [
            'client_title' => 'Digitalization, Systems & Research',
            'title' => 'Britt Montalvo | Digitalization, Systems & Research',
            'description' => 'Portfolio of Britt Kristoff B. Montalvo, MSIT: information systems, digital transformation, data and health informatics, research, networking, and visual communication.',
            'type' => 'profile',
        ],
        '/services' => [
            'client_title' => 'Digital Systems, Data & Consulting Services',
            'title' => 'Digital Systems, Data & Consulting Services | Britt Montalvo',
            'description' => 'Explore digital transformation consulting, custom systems, data analytics, health IT, responsible AI, networking, publications, digital operations, and technical speaking services.',
            'type' => 'website',
        ],
        '/designs' => [
            'client_title' => 'Design & Media Portfolio',
            'title' => 'Design & Media Portfolio | Britt Montalvo',
            'description' => 'Selected digital publications, editorial layouts, campaign graphics, presentations, social content, and other visual communication work by Britt Montalvo.',
            'type' => 'website',
        ],
        '/badges' => [
            'client_title' => 'Verified Professional Badges',
            'title' => 'Verified Professional Badges | Britt Montalvo',
            'description' => 'Verified professional badges across networking, cybersecurity, data analytics, software, responsible AI, enterprise systems, and digital practice.',
            'type' => 'website',
        ],
        '/certifications' => [
            'client_title' => 'Professional Certificates',
            'title' => 'Professional Certificates | Britt Montalvo',
            'description' => 'Professional certificates and continuing education in information technology, cybersecurity, networking, data, project management, AI, and digital transformation.',
            'type' => 'website',
        ],
    ],
];
