<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="description" content="Portfolio of Britt Kristoff B. Montalvo, MSIT: information systems, research, publications, credentials, and visual design.">

        <script>
            (() => {
                const savedTheme = window.localStorage.getItem('profile-theme') ?? 'system';
                const useDark = savedTheme === 'dark'
                    || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.dataset.theme = useDark ? 'dark' : 'light';
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
