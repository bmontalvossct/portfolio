<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['seo.base_url' => 'https://brittmontalvo.dev']);
        $this->withoutVite();
        $this->seed();
    }

    public function test_public_discovery_files_expose_only_the_canonical_public_pages(): void
    {
        $sitemap = $this->withHeader('Host', 'untrusted.example')
            ->get('/sitemap.xml')
            ->assertOk();

        $this->assertStringStartsWith('application/xml', (string) $sitemap->headers->get('Content-Type'));
        preg_match_all('/<loc>([^<]+)<\/loc>/', $sitemap->getContent(), $matches);

        $this->assertSame([
            'https://brittmontalvo.dev/',
            'https://brittmontalvo.dev/services',
            'https://brittmontalvo.dev/designs',
            'https://brittmontalvo.dev/badges',
            'https://brittmontalvo.dev/certifications',
        ], array_map(
            static fn (string $url): string => html_entity_decode($url, ENT_XML1),
            $matches[1],
        ));
        $this->assertStringNotContainsString('<priority>', $sitemap->getContent());
        $this->assertStringNotContainsString('<changefreq>', $sitemap->getContent());
        $this->assertStringNotContainsString('/admin', $sitemap->getContent());
        $this->assertStringNotContainsString('/guestbook', $sitemap->getContent());
        $this->assertStringNotContainsString('/up', $sitemap->getContent());
        $this->assertStringNotContainsString('untrusted.example', $sitemap->getContent());

        $robots = str_replace("\r\n", "\n", (string) file_get_contents(public_path('robots.txt')));
        foreach ([
            '*',
            'OAI-SearchBot',
            'GPTBot',
            'ChatGPT-User',
            'OAI-AdsBot',
            'ClaudeBot',
            'Claude-SearchBot',
            'Claude-User',
            'PerplexityBot',
            'Perplexity-User',
            'Google-Extended',
            'Applebot-Extended',
        ] as $agent) {
            $this->assertStringContainsString("User-agent: {$agent}\nAllow: /", $robots);
        }
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Disallow: /up', $robots);
        $this->assertStringContainsString('Sitemap: https://brittmontalvo.dev/sitemap.xml', $robots);

        $agentGuide = $this->withHeader('Host', 'untrusted.example')
            ->get('/llms.txt')
            ->assertOk();

        $this->assertStringStartsWith('text/plain', (string) $agentGuide->headers->get('Content-Type'));
        $agentGuide->assertSeeText('Digital Transformation & IT Consulting');
        $agentGuide->assertSeeText('Speaking, Workshops & Technical Training');
        $agentGuide->assertSeeText('https://brittmontalvo.dev/services');
        $this->assertStringNotContainsString('₱', $agentGuide->getContent());
        $this->assertStringNotContainsStringIgnoringCase('price:', $agentGuide->getContent());
    }

    public function test_services_page_renders_canonical_metadata_and_price_free_service_schema_in_the_initial_html(): void
    {
        $response = $this->withHeader('Host', 'untrusted.example')
            ->get('/services')
            ->assertOk();
        $xpath = $this->xpathFor($response->getContent());

        $this->assertSame(
            'Digital Systems, Data & Consulting Services | Britt Montalvo',
            trim((string) $xpath->evaluate('string(//head/title)')),
        );
        $this->assertSame(1, $xpath->query('//head/meta[@name="description"]')->length);
        $this->assertSame(
            config('seo.pages./services.description'),
            $this->attributeValue($xpath, '//head/meta[@name="description"]', 'content'),
        );
        $this->assertSame(1, $xpath->query('//head/link[@rel="canonical"]')->length);
        $this->assertSame(
            'https://brittmontalvo.dev/services',
            $this->attributeValue($xpath, '//head/link[@rel="canonical"]', 'href'),
        );
        $this->assertSame(
            'https://brittmontalvo.dev/services',
            $this->attributeValue($xpath, '//head/meta[@property="og:url"]', 'content'),
        );
        $this->assertSame(
            'summary_large_image',
            $this->attributeValue($xpath, '//head/meta[@name="twitter:card"]', 'content'),
        );
        $this->assertSame(
            'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            $this->attributeValue($xpath, '//head/meta[@name="robots"]', 'content'),
        );
        $this->assertStringNotContainsString('untrusted.example', $response->getContent());

        $schemaNodes = $xpath->query('//head/script[@id="portfolio-structured-data" and @type="application/ld+json"]');
        $this->assertSame(1, $schemaNodes->length);
        $schema = json_decode((string) $schemaNodes->item(0)?->textContent, true, flags: JSON_THROW_ON_ERROR);
        $catalog = collect($schema['@graph'])
            ->first(fn (array $node): bool => ($node['@type'] ?? null) === 'ItemList');

        $this->assertNotNull($catalog);
        $this->assertSame(9, $catalog['numberOfItems']);
        $names = collect($catalog['itemListElement'])->pluck('item.name')->all();
        $this->assertContains('Digital Transformation & IT Consulting', $names);
        $this->assertContains('Speaking, Workshops & Technical Training', $names);
        $this->assertContains('Network & Infrastructure Consulting', $names);

        $encodedSchema = strtolower((string) json_encode($schema, JSON_UNESCAPED_SLASHES));
        $this->assertStringNotContainsString('"price"', $encodedSchema);
        $this->assertStringNotContainsString('"offers"', $encodedSchema);
    }

    public function test_homepage_schema_identifies_the_site_profile_and_person(): void
    {
        $response = $this->get('/')->assertOk();
        $xpath = $this->xpathFor($response->getContent());
        $this->assertSame(
            '/favicon.svg',
            $this->attributeValue($xpath, '//head/link[@rel="icon"]', 'href'),
        );
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.svg')));
        $schema = json_decode(
            (string) $xpath->query('//head/script[@id="portfolio-structured-data"]')->item(0)?->textContent,
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $types = collect($schema['@graph'])->pluck('@type')->all();

        $this->assertContains('WebSite', $types);
        $this->assertContains('ProfilePage', $types);
        $this->assertContains('Person', $types);
    }

    public function test_admin_pages_emit_noindex_header_and_server_rendered_meta(): void
    {
        $response = $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $xpath = $this->xpathFor($response->getContent());

        $this->assertSame(
            'noindex, nofollow, noarchive',
            $this->attributeValue($xpath, '//head/meta[@name="robots"]', 'content'),
        );
        $this->assertSame(0, $xpath->query('//head/link[@rel="canonical"]')->length);
        $this->assertSame(0, $xpath->query('//head/script[@type="application/ld+json"]')->length);
    }

    private function xpathFor(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function attributeValue(DOMXPath $xpath, string $query, string $attribute): ?string
    {
        return $xpath->query($query)->item(0)?->attributes?->getNamedItem($attribute)?->nodeValue;
    }
}
