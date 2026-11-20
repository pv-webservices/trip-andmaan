<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Package;
use Tests\TestCase;

/** Changing one page, section or package never changes another. */
class IndependenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importContent();
    }

    public function test_about_hero_edit_does_not_touch_homepage_hero(): void
    {
        $about = $this->section('about', 'hero');
        $about->saveDraft([...$about->draft_data, 'title' => 'Changed about heading'], null);
        $about->publish(null);

        $this->visit('/')->assertSee('<h1>Andaman</h1>', false);
        $this->visit('/about/')->assertSee('Changed about heading');
        $this->visit('/contact/')->assertSee('Let’s talk about your trip');
    }

    public function test_choosing_a_new_image_in_one_section_does_not_change_other_sections(): void
    {
        $sunset = Media::query()->where('legacy_key', 'image:sunset')->firstOrFail();
        $other = Media::query()->where('legacy_key', 'image:ross')->firstOrFail();
        $contactHero = $this->section('contact', 'hero');
        $this->assertSame($sunset->id, $contactHero->published_data['image_id']);

        $contactHero->saveDraft([...$contactHero->draft_data, 'image_id' => $other->id], null);
        $contactHero->publish(null);

        // The shared enquiry banner and the homepage collage still use the sunset photo.
        $this->assertSame($sunset->id, $this->section('global', 'enquiry_banner')->published_data['image_id']);
        $this->assertSame($sunset->id, $this->section('home', 'story')->published_data['wide2_image_id']);
        $this->assertTrue($sunset->usages()->exists());
    }

    public function test_package_itineraries_are_independent_copies(): void
    {
        $a = Package::query()->where('slug', 'andaman-island-escape-4n5d')->firstOrFail();
        $b = Package::query()->where('slug', 'havelock-neil-getaway-5n6d')->firstOrFail();
        $sharedTitle = $b->itineraryDays()->first()->title;
        $this->assertSame($sharedTitle, $a->itineraryDays()->first()->title, 'Both start from the same shared day block');

        $a->itineraryDays()->first()->update(['title' => 'Edited only in package A']);
        $a->publish(null);

        $this->assertSame($sharedTitle, $b->itineraryDays()->first()->title);
        $this->visit('/tour-packages/havelock-neil-getaway-5n6d/')->assertDontSee('Edited only in package A');
        $this->visit('/tour-packages/andaman-island-escape-4n5d/')->assertSee('Edited only in package A');
    }

    public function test_one_package_price_does_not_change_another(): void
    {
        $a = Package::query()->where('slug', 'andaman-classic-6n7d')->firstOrFail();
        $a->update(['price_mode' => 'from', 'price_amount' => '25000', 'price_unit' => 'per_person']);
        $a->publish(null);

        $this->visit('/tour-packages/andaman-classic-6n7d/')->assertSee('From ₹25,000 per person');
        // Island Hopper keeps its own price; its "Similar packages" cards show Andaman Classic's new price consistently.
        $html = $this->visit('/tour-packages/island-hopper-6n7d/')->assertSee('From ₹25,000 per person')->getContent();
        $this->assertMatchesRegularExpression('#<dt>Price</dt>\s*<dd>On request, for your dates and hotel choice</dd>#', $html);
        $this->assertSame('on_request', Package::query()->where('slug', 'island-hopper-6n7d')->value('price_mode'));
    }
}
