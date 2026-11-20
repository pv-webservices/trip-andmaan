<?php

namespace Tests\Feature;

use App\Filament\Resources\Sections\Pages\EditSection;
use Livewire\Livewire;
use Tests\TestCase;

class PublishingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importContent();
    }

    public function test_saved_drafts_are_private_until_published(): void
    {
        $admin = $this->admin();
        $hero = $this->section('about', 'hero');

        $this->actingAs($admin);
        Livewire::test(EditSection::class, ['record' => $hero->id])
            ->fillForm(['title' => 'A brand new heading'])
            ->call('save')
            ->assertHasNoFormErrors();

        $hero->refresh();
        $this->assertSame('A brand new heading', $hero->draft_data['title']);
        $this->assertSame('A little closer to Andaman', $hero->published_data['title']);
        $this->assertSame($admin->id, $hero->updated_by);

        auth()->logout();
        $this->visit('/about/')->assertOk()->assertSee('A little closer to Andaman')->assertDontSee('A brand new heading');

        // The preview shows the draft to administrators, never to search engines or caches.
        $this->actingAs($admin);
        $this->visit('/preview/about/')->assertOk()->assertSee('A brand new heading')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
        $this->assertStringContainsString('no-store', $this->visit('/preview/about/')->headers->get('Cache-Control'));
    }

    public function test_publishing_updates_the_cached_public_page_immediately(): void
    {
        $this->visit('/about/')->assertHeader('X-Page-Cache', 'MISS');
        $this->visit('/about/')->assertHeader('X-Page-Cache', 'HIT');

        $admin = $this->admin();
        $this->actingAs($admin);
        Livewire::test(EditSection::class, ['record' => $this->section('about', 'hero')->id])
            ->fillForm(['title' => 'Published heading'])
            ->callAction('publish')
            ->assertHasNoFormErrors();
        auth()->logout();

        $this->visit('/about/')->assertHeader('X-Page-Cache', 'MISS')->assertSee('Published heading');
        $section = $this->section('about', 'hero');
        $this->assertSame($admin->id, $section->published_by);
        $this->assertNotNull($section->published_at);
    }

    public function test_discarding_a_draft_restores_the_published_content(): void
    {
        $this->actingAs($this->admin());
        $hero = $this->section('about', 'hero');
        Livewire::test(EditSection::class, ['record' => $hero->id])
            ->fillForm(['title' => 'Temporary'])->call('save')
            ->callAction('discard');

        $this->assertSame('A little closer to Andaman', $hero->fresh()->draft_data['title']);
        $this->assertFalse($hero->fresh()->hasUnpublishedChanges());
    }

    public function test_hidden_sections_leave_no_markup(): void
    {
        $section = $this->section('home', 'testimonials');
        $this->assertSame([], $section->published_data['items']);
        $this->visit('/')->assertDontSee('Our Happy');

        $gallery = $this->section('home', 'gallery');
        $gallery->saveDraft([...$gallery->draft_data, 'visible' => false], null);
        $gallery->publish(null);
        $this->visit('/')->assertDontSee('Glimpses of Andaman');
    }

    public function test_unsafe_links_and_rich_text_are_cleaned_on_save(): void
    {
        $this->actingAs($this->admin());
        $hero = $this->section('about', 'hero');
        Livewire::test(EditSection::class, ['record' => $hero->id])
            ->fillForm(['primary' => ['label' => 'Click', 'url' => 'javascript:alert(1)', 'visible' => true]])
            ->call('save')
            ->assertHasFormErrors(['primary.url']);

        $body = $this->section('privacy', 'body');
        $data = $body->draft_data;
        $data['blocks'][0]['body_html'] = '<p onclick="x()">Hi<script>alert(1)</script><a href="javascript:alert(1)">x</a></p>';
        Livewire::test(EditSection::class, ['record' => $body->id])->fillForm($data)->call('save');
        $saved = $body->fresh()->draft_data['blocks'][0]['body_html'];
        $this->assertStringNotContainsString('script', $saved);
        $this->assertStringNotContainsString('onclick', $saved);
        $this->assertStringNotContainsString('javascript:', $saved);
    }

    public function test_announcement_colours_must_be_readable(): void
    {
        $this->actingAs($this->admin());
        $bar = $this->section('global', 'announcement_bar');
        Livewire::test(EditSection::class, ['record' => $bar->id])
            ->fillForm(['background' => '#ffffff', 'text_color' => '#eeeeee', 'speed' => 50])
            ->call('save');
        $this->assertSame('#04203a', $bar->fresh()->draft_data['background']);
    }
}
