<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="color-scheme" content="light dark">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml" sizes="any">
        <link rel="preload" href="/fonts/saira-latin.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="/fonts/saira-condensed-600-latin.woff2" as="font" type="font/woff2" crossorigin>
        @php($seo = data_get($page ?? [], 'props.seo'))

        @if ($seo)
        <title data-inertia="">{{ $seo['title'] }}</title>
        <meta data-inertia="description" name="description" content="{{ $seo['description'] }}">
        <meta data-inertia="robots" name="robots" content="{{ $seo['robots'] }}">
        @if ($seo['canonical'])
        <link data-inertia="canonical" rel="canonical" href="{{ $seo['canonical'] }}">
        @endif
        <meta data-inertia="og-type" property="og:type" content="{{ $seo['type'] }}">
        <meta data-inertia="og-site-name" property="og:site_name" content="{{ $seo['site_name'] }}">
        <meta data-inertia="og-locale" property="og:locale" content="{{ $seo['locale'] }}">
        <meta data-inertia="og-title" property="og:title" content="{{ $seo['title'] }}">
        <meta data-inertia="og-description" property="og:description" content="{{ $seo['description'] }}">
        @if ($seo['canonical'])
        <meta data-inertia="og-url" property="og:url" content="{{ $seo['canonical'] }}">
        @endif
        @if ($seo['image'])
        <meta data-inertia="og-image" property="og:image" content="{{ $seo['image'] }}">
        <meta data-inertia="og-image-alt" property="og:image:alt" content="{{ $seo['image_alt'] }}">
        @endif
        <meta data-inertia="twitter-card" name="twitter:card" content="{{ $seo['image'] ? 'summary_large_image' : 'summary' }}">
        <meta data-inertia="twitter-title" name="twitter:title" content="{{ $seo['title'] }}">
        <meta data-inertia="twitter-description" name="twitter:description" content="{{ $seo['description'] }}">
        @if ($seo['image'])
        <meta data-inertia="twitter-image" name="twitter:image" content="{{ $seo['image'] }}">
        @endif
        @if ($seo['schema'])
        <script id="portfolio-structured-data" type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif
        @endif

        <script>
            (() => {
                const savedTheme = window.localStorage.getItem('profile-theme') ?? 'system';
                const useDark = savedTheme === 'dark'
                    || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.dataset.theme = useDark ? 'dark' : 'light';
            })();
        </script>

        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
