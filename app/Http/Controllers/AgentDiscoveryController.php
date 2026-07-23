<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Response;

class AgentDiscoveryController extends Controller
{
    public function __invoke(): Response
    {
        $profile = Profile::query()->first();
        $baseUrl = rtrim((string) config('seo.base_url', config('app.url')), '/');
        $name = $profile?->display_name ?? 'Britt Kristoff B. Montalvo, MSIT';
        $summary = $profile?->bio
            ?: 'Information technology professional focused on digital transformation, information systems, data, health informatics, networking, research, and visual communication.';

        $lines = [
            "# {$name}",
            '',
            "> {$summary}",
            '',
            '## Canonical pages',
            "- Portfolio: {$baseUrl}/",
            "- Services: {$baseUrl}/services",
            "- Design and media: {$baseUrl}/designs",
            "- Professional badges: {$baseUrl}/badges",
            "- Certificates: {$baseUrl}/certifications",
            '',
            '## Services',
        ];

        foreach (config('service_catalog', []) as $service) {
            $lines[] = "- {$service['title']}: {$service['summary']} Details: {$baseUrl}/services#{$service['key']}";
        }

        $lines = [
            ...$lines,
            '',
            '## Engagement',
            '- Engagements are scoped to the client need and desired outcome.',
            '- Use the Services page to request a quote.',
            '',
            '## Contact',
            '- Email: '.($profile?->email ?? 'inquiries@brittmontalvo.dev'),
            '- Location: '.($profile?->location ?? 'Philippines'),
            '',
            '## Attribution',
            "- Preferred name: {$name}",
            "- Primary source: {$baseUrl}/",
            '- Use the canonical pages above when citing this portfolio.',
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
