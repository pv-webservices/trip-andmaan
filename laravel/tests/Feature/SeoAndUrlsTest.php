<?php

namespace Tests\Feature;

use App\Cms\PageMap;
use Tests\TestCase;

class SeoAndUrlsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importContent();
    }

    public function test_every_public_page_renders_with_its_seo_metadata(): void
    {
        foreach (PageMap::pages() as $page) {
            if (! $page['path']) {
                continue;
            }
            $response = $this->visit($page['path'])->assertOk();
            $html = $response->getContent();
            $this->assertSame(1, substr_count($html, '<h1'), "{$page['path']} has one h1");
            if ($page['index']) {
                $response->assertSee('<link rel="canonical" href="https://tripandaman.in'.$page['path'].'">', false)
                    ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);
            } else {
                $response->assertSee('<meta name="robots" content="noindex, follow">', false)->assertDontSee('rel="canonical"', false);
            }
            preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
            foreach ($m[1] as $json) {
                $this->assertIsArray(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
                $this->assertStringContainsString('"@context":"https://schema.org"', $json);
            }
        }
    }

    public function test_titles_and_descriptions_match_the_existing_site(): void
    {
        $this->visit('/')->assertSee('<title>Andaman Holidays &amp; Customised Island Trips | Trip Andaman</title>', false);
        $this->visit('/tour-packages/andaman-classic-6n7d/')
            ->assertSee('<title>Andaman Classic — 6 Nights / 7 Days Andaman Tour | Trip Andaman</title>', false);
        $this->visit('/tour-packages/')->assertSee('"@type":"FAQPage"', false);
        $this->visit('/destinations/neil-island/')->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $sitemap = $this->visit('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8')->getContent();
        $this->assertSame(31, substr_count($sitemap, '<url>'));
        $this->assertStringNotContainsString('image-credits', $sitemap);
        $this->assertStringContainsString('<lastmod>', $sitemap);
        $this->visit('/robots.txt')->assertOk()->assertSee('Sitemap: https://tripandaman.in/sitemap.xml')->assertSee('Disallow: /admin/');
    }

    public function test_trailing_slash_index_html_and_404(): void
    {
        $this->get('/about')->assertStatus(301)->assertHeader('Location', '/about/');
        $this->get('/contact?from=x')->assertStatus(301)->assertHeader('Location', '/contact/?from=x');
        $this->get('/index.html')->assertStatus(301)->assertHeader('Location', '/');
        $this->get('/gallery/index.html')->assertStatus(301)->assertHeader('Location', '/gallery/');
        $this->visit('/does-not-exist/')->assertNotFound()->assertSee('Let’s get you back to paradise.')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_canonical_host_redirect_when_configured(): void
    {
        config(['cms.canonical_host' => 'tripandaman.in']);
        $this->get('http://www.tripandaman.in/about/')->assertStatus(301)->assertHeader('Location', 'https://tripandaman.in/about/');
    }

    public function test_security_headers_and_public_pages_set_no_cookies(): void
    {
        $response = $this->visit('/');
        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertSame([], $response->headers->getCookies(), 'Visitors get no session cookie');
    }

    public function test_whatsapp_and_phone_links_are_unchanged(): void
    {
        $html = $this->visit('/tour-packages/andaman-classic-6n7d/')->getContent();
        preg_match_all('#href="(https://wa\.me/[^"]+)"#', $html, $links);
        $this->assertNotEmpty($links[1]);
        foreach ($links[1] as $link) {
            $this->assertStringStartsWith('https://wa.me/919646969646?text=Hello%20Trip%20Andaman%2C%20I%20would%20like%20to%20enquire%20about%20', html_entity_decode($link));
        }
        $this->assertStringContainsString('enquire%20about%20the%20Andaman%20Classic%20(6%20Nights%20%2F%207%20Days)%20package%20and%20would%20like%20a%20quote.', $html);
        $this->assertStringContainsString('href="tel:+919646969646"', $html);

        $contact = $this->visit('/contact/')->getContent();
        $this->assertStringContainsString('<form method="dialog" class="whatsapp-composer needs-js" data-whatsapp="919646969646" data-source="Contact page"', $contact);
        $this->assertStringNotContainsString('action=', $contact, 'The composer never posts data');
    }
}
