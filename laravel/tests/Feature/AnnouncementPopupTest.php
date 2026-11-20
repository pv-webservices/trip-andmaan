<?php

namespace Tests\Feature;

use App\Cms\Site;
use App\Models\Announcement;
use App\Models\Popup;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnnouncementPopupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importContent();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_no_announcements_means_no_bar_markup(): void
    {
        $this->visit('/')->assertDontSee('class="announce"', false);
    }

    public function test_global_and_page_targeted_announcements(): void
    {
        Announcement::query()->create(['text' => 'Everywhere notice', 'targeting' => 'all', 'is_enabled' => true, 'sort' => 1]);
        Announcement::query()->create(['text' => 'Packages only notice', 'link_url' => '/tour-packages/', 'targeting' => 'selected', 'page_keys' => ['tour_packages'], 'is_enabled' => true, 'sort' => 2]);
        Announcement::query()->create(['text' => 'Disabled notice', 'targeting' => 'all', 'is_enabled' => false, 'sort' => 3]);

        $this->visit('/')->assertSee('Everywhere notice')->assertDontSee('Packages only notice')->assertDontSee('Disabled notice')
            ->assertSee('aria-label="Pause announcements"', false);
        $this->visit('/tour-packages/')->assertSee('Everywhere notice')->assertSee('Packages only notice');
    }

    public function test_announcement_schedule_uses_india_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 09:59', 'Asia/Kolkata'));
        Announcement::query()->create([
            'text' => 'Winter sale', 'targeting' => 'all', 'is_enabled' => true,
            'starts_at' => Carbon::parse('2026-12-01 10:00', 'Asia/Kolkata'),
            'ends_at' => Carbon::parse('2026-12-02 10:00', 'Asia/Kolkata'),
        ]);
        $this->visit('/')->assertDontSee('Winter sale');

        Carbon::setTestNow(Carbon::parse('2026-12-01 10:00', 'Asia/Kolkata'));
        $this->visit('/')->assertSee('Winter sale');

        Carbon::setTestNow(Carbon::parse('2026-12-02 10:00', 'Asia/Kolkata'));
        $this->visit('/')->assertDontSee('Winter sale');
    }

    public function test_cached_pages_expire_at_the_next_schedule_boundary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-01 09:00', 'Asia/Kolkata'));
        Announcement::query()->create(['text' => 'Soon', 'targeting' => 'all', 'is_enabled' => true, 'starts_at' => Carbon::parse('2026-12-01 09:30', 'Asia/Kolkata')]);
        $this->assertSame(1800, app(Site::class)->secondsUntilNextScheduleChange());
    }

    public function test_popup_targeting_scheduling_and_priority(): void
    {
        Popup::query()->create(['name' => 'Low', 'heading' => 'Low priority offer', 'is_enabled' => true, 'priority' => 1]);
        Popup::query()->create(['name' => 'High', 'heading' => 'High priority offer', 'is_enabled' => true, 'priority' => 50]);
        Popup::query()->create(['name' => 'Contact only', 'heading' => 'Contact page offer', 'is_enabled' => true, 'priority' => 99, 'targeting' => 'selected', 'page_keys' => ['contact']]);
        Popup::query()->create(['name' => 'Off', 'heading' => 'Disabled offer', 'is_enabled' => false, 'priority' => 100]);
        Popup::query()->create(['name' => 'Expired', 'heading' => 'Expired offer', 'is_enabled' => true, 'priority' => 100, 'ends_at' => now()->subMinute()]);

        $html = $this->visit('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('Disabled offer', $html);
        $this->assertStringNotContainsString('Expired offer', $html);
        $this->assertStringNotContainsString('Contact page offer', $html);
        // Highest priority first; the browser shows only the first one not yet dismissed.
        $this->assertLessThan(strpos($html, 'Low priority offer'), strpos($html, 'High priority offer'));

        $contact = $this->visit('/contact/')->getContent();
        $this->assertLessThan(strpos($contact, 'High priority offer'), strpos($contact, 'Contact page offer'));
    }

    public function test_changing_a_campaign_gives_it_a_new_dismissal_key(): void
    {
        $popup = Popup::query()->create(['name' => 'Offer', 'heading' => 'First', 'is_enabled' => true]);
        $first = $popup->campaign_version;

        $popup->update(['priority' => 20]);
        $this->assertSame($first, $popup->fresh()->campaign_version, 'Priority alone is not a new campaign');

        $popup->update(['heading' => 'Second']);
        $this->assertNotSame($first, $popup->fresh()->campaign_version);
        $this->visit('/')->assertSee('data-popup-key="ta-popup:'.$popup->id.':'.$popup->fresh()->campaign_version.'"', false);
    }

    public function test_popup_is_an_accessible_dialog(): void
    {
        Popup::query()->create(['name' => 'Offer', 'heading' => 'Monsoon offer', 'description' => 'Details', 'button_label' => 'See packages', 'button_url' => '/tour-packages/', 'is_enabled' => true, 'delay_seconds' => 3, 'frequency' => 'day']);
        $this->visit('/')
            ->assertSee('<dialog class="promo-dialog"', false)
            ->assertSee('aria-labelledby="promo-title-', false)
            ->assertSee('class="icon-button promo-close" type="button" aria-label="Close"', false)
            ->assertSee('data-frequency="day"', false)
            ->assertSee('data-delay="3"', false);
    }
}
