<?php

namespace Tests\Unit;

use App\Cms\Contrast;
use App\Cms\SafeUrl;
use App\Cms\Sanitizer;
use App\Cms\Views\Price;
use App\Media\MediaUsageRecorder;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class CmsUnitTest extends TestCase
{
    public function test_safe_url_allows_site_paths_https_and_tel_only(): void
    {
        foreach (['/contact/', '#water-sports', '/tour-packages/?x=1', 'https://wa.me/919646969646?text=Hi', 'http://example.com', 'tel:+919646969646', 'mailto:a@b.co', ''] as $ok) {
            $this->assertTrue(SafeUrl::isAllowed($ok), $ok);
        }
        foreach (['javascript:alert(1)', 'JAVASCRIPT:alert(1)', 'data:text/html,x', '//evil.com', '/\\evil.com', 'vbscript:x', 'https://exa mple.com', 'ftp://x.y', 'https://x.y/"onmouseover=', "/a\nb"] as $bad) {
            $this->assertFalse(SafeUrl::isAllowed($bad), $bad);
        }
    }

    public function test_sanitizer_cleans_by_key_convention(): void
    {
        $clean = Sanitizer::data([
            'title' => "  Hello\x07 ",
            'url' => 'javascript:alert(1)',
            'image_id' => '12',
            'poster_id' => 'abc',
            'body_html' => '<p>ok <img src=x onerror=alert(1)><a href="https://x.y" onclick="z()">l</a></p>',
            'items' => [['link_url' => 'data:x', 'label' => 'x']],
        ]);
        $this->assertSame('Hello', $clean['title']);
        $this->assertSame('', $clean['url']);
        $this->assertSame(12, $clean['image_id']);
        $this->assertNull($clean['poster_id']);
        $this->assertStringNotContainsString('img', $clean['body_html']);
        $this->assertStringNotContainsString('onclick', $clean['body_html']);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean['body_html']);
        $this->assertSame('', $clean['items'][0]['link_url']);
    }

    public function test_price_formatting_and_comparison(): void
    {
        $this->assertSame('₹25,000', Price::format('25000.00'));
        $this->assertSame('₹1,25,000.50', Price::format('125000.5'));
        $this->assertSame('₹10,00,00,000', Price::format('100000000'));
        $this->assertSame('$1,250,000', Price::format('1250000', 'USD'));
        $this->assertSame(1, Price::compare('100.01', '100'));
        $this->assertSame(0, Price::compare('0100.00', '100'));
        $this->assertSame(-1, Price::compare('99.99', '100.00'));
    }

    public function test_price_labels_and_validity(): void
    {
        $base = ['mode' => 'from', 'currency' => 'INR', 'amount' => '25000.00', 'unit' => 'per_person'];
        $this->assertSame('From ₹25,000 per person', (new Price($base))->label());
        $this->assertSame('Price on request', (new Price(['mode' => 'on_request']))->label());
        $this->assertSame('Price on request', (new Price(['mode' => 'fixed', 'amount' => null]))->label());

        $dated = new Price([...$base, 'valid_from' => '2026-12-01', 'valid_until' => '2026-12-31']);
        $this->assertTrue($dated->isOnRequest(Carbon::parse('2026-11-30', 'Asia/Kolkata')));
        $this->assertFalse($dated->isOnRequest(Carbon::parse('2026-12-31', 'Asia/Kolkata')));
        $this->assertTrue($dated->isOnRequest(Carbon::parse('2027-01-01', 'Asia/Kolkata')));
        $this->assertNull((new Price([...$base, 'original' => '20000']))->original(), 'A lower "original" price is never shown');
        $this->assertSame('₹30,000', (new Price([...$base, 'original' => '30000']))->original());
    }

    public function test_contrast_ratio(): void
    {
        $this->assertEqualsWithDelta(21.0, Contrast::ratio('#000000', '#ffffff'), 0.01);
        $this->assertGreaterThan(4.5, Contrast::ratio('#04203a', '#ffffff'));
        $this->assertLessThan(4.5, Contrast::ratio('#ffffff', '#eeeeee'));
    }

    public function test_media_references_are_found_anywhere_in_section_data(): void
    {
        $refs = MediaUsageRecorder::extract(['image_id' => 3, 'card' => ['image_id' => 4], 'items' => [['video_id' => 5, 'poster_id' => null]], 'title' => 'x', 'count' => 5]);
        $this->assertSame(['image_id' => 3, 'card.image_id' => 4, 'items.0.video_id' => 5], $refs);
    }
}
