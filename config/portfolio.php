<?php

return [
    'admin_token' => env('PORTFOLIO_ADMIN_TOKEN'),
    'review_notification_email' => env('PORTFOLIO_REVIEW_NOTIFICATION_EMAIL', 'inquiries@brittmontalvo.dev'),
    'github_cache_hours' => (int) env('PORTFOLIO_GITHUB_CACHE_HOURS', 6),
];
