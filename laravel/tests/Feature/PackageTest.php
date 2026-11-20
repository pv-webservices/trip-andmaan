<?php

namespace Tests\Feature;

use App\Filament\Resources\Packages\Pages\CreatePackage;
use App\Filament\Resources\Packages\Pages\EditPackage;
use App\Models\Media;
use App\Models\Package;
use App\Models\Redirect;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PackageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importContent();
        $this->actingAs($this->admin());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function newPackage(array $overrides = []): Package
    {
        $package = Package::query()->create([
            'slug' => 'monsoon-special-3n4d', 'title' => 'Monsoon Special', 'overview' => 'Three quiet nights.',
            'nights' => 3, 'days' => 4, 'image_id' => Media::query()->where('legacy_key', 'image:hero')->value('id'),
            'stays' => [['place' => 'Port Blair', 'nights' => 3]], 'tags' => ['Rain'], 'status' => 'draft',
            ...$overrides,
        ]);
        foreach (['Arrive', 'Explore', 'Relax', 'Depart'] as $i => $title) {
            $package->itineraryDays()->create(['sort' => $i, 'title' => $title, 'description' => "{$title} paragraph one.\n\n{$title} paragraph two."]);
        }

        return $package;
    }

    public function test_admin_can_create_a_package_through_the_form(): void
    {
        Livewire::test(CreatePackage::class)
            ->fillForm([
                'title' => 'Created In Admin', 'slug' => 'created-in-admin-2n3d', 'overview' => 'Short trip.',
                'nights' => 2, 'days' => 3, 'image_id' => Media::query()->where('legacy_key', 'image:neil')->value('id'),
                'price_mode' => 'on_request',
            ])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertSame('draft', Package::query()->where('slug', 'created-in-admin-2n3d')->value('status'));
    }

    public function test_duplicate_and_reserved_slugs_are_rejected(): void
    {
        Livewire::test(CreatePackage::class)
            ->fillForm(['title' => 'Copy', 'slug' => 'andaman-classic-6n7d', 'overview' => 'x', 'nights' => 1, 'days' => 2])
            ->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreatePackage::class)
            ->fillForm(['title' => 'Copy', 'slug' => 'customized-trip', 'overview' => 'x', 'nights' => 1, 'days' => 2])
            ->call('create')->assertHasFormErrors(['slug']);
        Livewire::test(CreatePackage::class)
            ->fillForm(['title' => 'Copy', 'slug' => 'Bad Slug!', 'overview' => 'x', 'nights' => 1, 'days' => 2])
            ->call('create')->assertHasFormErrors(['slug']);
    }

    public function test_new_package_is_live_after_publishing_and_listed_in_sitemap(): void
    {
        $package = $this->newPackage();
        $this->visit('/tour-packages/monsoon-special-3n4d/')->assertNotFound();
        $this->visit('/sitemap.xml')->assertDontSee('monsoon-special');

        $package->publish(auth()->user());

        $this->visit('/tour-packages/monsoon-special-3n4d/')->assertOk()
            ->assertSee('Monsoon Special')->assertSee('Day 4')->assertSee('Depart paragraph two.');
        $this->visit('/tour-packages/')->assertSee('/tour-packages/monsoon-special-3n4d/');
        $this->visit('/sitemap.xml')->assertSee('https://tripandaman.in/tour-packages/monsoon-special-3n4d/');
    }

    public function test_unpublished_and_archived_packages_disappear(): void
    {
        $package = Package::query()->where('slug', 'island-hopper-6n7d')->firstOrFail();
        $package->unpublish(auth()->user());
        $this->visit('/tour-packages/island-hopper-6n7d/')->assertNotFound();
        $this->visit('/tour-packages/')->assertDontSee('/tour-packages/island-hopper-6n7d/');
        $this->visit('/sitemap.xml')->assertDontSee('island-hopper');

        $package->publish(auth()->user());
        $package->unpublish(auth()->user(), 'archived');
        $this->visit('/tour-packages/island-hopper-6n7d/')->assertNotFound();
    }

    public function test_edits_to_a_published_package_stay_private_until_republished(): void
    {
        $package = Package::query()->where('slug', 'andaman-classic-6n7d')->firstOrFail();
        Livewire::test(EditPackage::class, ['record' => $package->id])
            ->fillForm(['title' => 'Andaman Classic Revised'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertTrue($package->fresh()->has_unpublished_changes);
        auth()->logout();
        $this->visit('/tour-packages/andaman-classic-6n7d/')->assertSee('Andaman Classic')->assertDontSee('Andaman Classic Revised');
        $this->actingAs($this->admin());
        $this->visit('/preview/tour-packages/andaman-classic-6n7d/')->assertSee('Andaman Classic Revised');
    }

    public function test_slug_change_redirects_the_old_address(): void
    {
        $package = Package::query()->where('slug', 'andaman-classic-6n7d')->firstOrFail();
        $package->update(['slug' => 'andaman-classic-week']);
        $this->visit('/tour-packages/andaman-classic-6n7d/')->assertOk(); // still the published version
        $package->publish(auth()->user());

        $this->visit('/tour-packages/andaman-classic-6n7d/')->assertStatus(301)->assertHeader('Location', '/tour-packages/andaman-classic-week/');
        $this->visit('/tour-packages/andaman-classic-week/')->assertOk();

        // A second rename re-points the first redirect, so there is never a chain.
        $package->update(['slug' => 'andaman-classic-7-days']);
        $package->publish(auth()->user());
        $this->assertSame('/tour-packages/andaman-classic-7-days/', Redirect::query()->where('from_path', '/tour-packages/andaman-classic-6n7d/')->value('to_path'));
    }

    public function test_itinerary_reordering_renumbers_days(): void
    {
        $package = $this->newPackage();
        $days = $package->itineraryDays()->get();
        $days[0]->update(['sort' => 3]);
        $days[3]->update(['sort' => 0]);
        $package->publish(auth()->user());

        $html = $this->visit('/tour-packages/monsoon-special-3n4d/')->getContent();
        $this->assertMatchesRegularExpression('#Day 1</span>\s*<span class="itinerary-title">Depart#', $html);
        $this->assertMatchesRegularExpression('#Day 4</span>\s*<span class="itinerary-title">Arrive#', $html);
    }

    public function test_consistency_warnings_and_publish_blockers(): void
    {
        $package = $this->newPackage(['days' => 6]);
        $warnings = implode(' ', $package->warnings());
        $this->assertStringContainsString('one more day than nights', $warnings);
        $this->assertStringContainsString('itinerary has 4 days but the duration says 6 days', $warnings);

        $empty = Package::query()->create(['slug' => 'empty-pkg', 'title' => 'Empty', 'overview' => 'x', 'nights' => 1, 'days' => 2]);
        $this->assertContains('Add at least one itinerary day.', $empty->publishBlockers());
        $this->expectException(ValidationException::class);
        $empty->publish(null);
    }

    public function test_pricing_display_is_decimal_safe_and_respects_validity(): void
    {
        $package = $this->newPackage([
            'price_mode' => 'fixed', 'currency' => 'INR', 'price_amount' => '125000.50', 'price_original' => '150000',
            'price_unit' => 'per_couple', 'price_valid_until' => '2026-12-31', 'price_note' => 'Two adults sharing',
        ]);
        $this->assertSame('125000.50', $package->fresh()->price_amount);
        $package->publish(null);

        Carbon::setTestNow(Carbon::parse('2026-12-31 23:00', 'Asia/Kolkata'));
        $this->visit('/tour-packages/monsoon-special-3n4d/')->assertSee('₹1,25,000.50 per couple')->assertSee('₹1,50,000')->assertSee('Two adults sharing');
        $this->visit('/tour-packages/')->assertSee('₹1,25,000.50 per couple');

        Carbon::setTestNow(Carbon::parse('2027-01-01 00:01', 'Asia/Kolkata'));
        $this->visit('/tour-packages/monsoon-special-3n4d/')->assertDontSee('₹1,25,000')->assertSee('On request, for your dates and hotel choice');
    }

    public function test_imported_packages_are_price_on_request(): void
    {
        $this->assertSame(15, Package::query()->where('price_mode', 'on_request')->count());
        $this->visit('/tour-packages/')->assertDontSee('₹');
    }

    public function test_duplicate_creates_an_independent_draft(): void
    {
        $original = Package::query()->where('slug', 'andaman-classic-6n7d')->firstOrFail();
        $copy = $original->duplicate(auth()->user());
        $this->assertSame('draft', $copy->status);
        $this->assertSame('andaman-classic-6n7d-copy', $copy->slug);
        $this->assertSame($original->itineraryDays()->count(), $copy->itineraryDays()->count());
        $copy->itineraryDays()->first()->update(['title' => 'Copy only']);
        $this->assertNotSame('Copy only', $original->itineraryDays()->first()->title);
        $this->visit('/tour-packages/andaman-classic-6n7d-copy/')->assertNotFound();
    }

    public function test_featured_packages_follow_their_order_on_the_homepage(): void
    {
        $html = $this->visit('/')->getContent();
        $a = strpos($html, '/tour-packages/andaman-island-escape-4n5d/');
        $b = strpos($html, '/tour-packages/andaman-classic-6n7d/');
        $c = strpos($html, '/tour-packages/north-to-south-andaman-10n11d/');
        $this->assertTrue($a < $b && $b < $c);
    }
}
