<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\PackageDay;
use App\Models\Section;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportTest extends TestCase
{
    public function test_import_creates_all_content(): void
    {
        $this->importContent();
        $this->assertSame(15, Package::query()->published()->count());
        $this->assertSame(6, Destination::query()->published()->count());
        $this->assertSame(74, Section::query()->count());
        $this->assertSame(0, Section::query()->whereNull('published_data')->count());
        $this->assertSame(59, Media::query()->count());
        $this->assertSame(8, Media::query()->whereNotNull('credit_author')->count(), 'Wikimedia credits are preserved');
        $this->assertSame(112, PackageDay::query()->count(), 'Each package owns its own itinerary days');
    }

    public function test_reimport_never_overwrites_administrator_edits(): void
    {
        $this->importContent();
        $hero = $this->section('about', 'hero');
        $hero->saveDraft([...$hero->draft_data, 'title' => 'Edited by admin'], null);
        $hero->publish(null);
        Package::query()->where('slug', 'island-hopper-6n7d')->update(['title' => 'Edited package']);
        $hero->page->sections()->where('key', 'values')->delete(); // a section removed in the meantime

        $exit = Artisan::call('cms:import', ['--no-files' => true]);

        $this->assertSame(0, $exit);
        $this->assertSame('Edited by admin', $this->section('about', 'hero')->published_data['title']);
        $this->assertSame('Edited package', Package::query()->where('slug', 'island-hopper-6n7d')->value('title'));
        $this->assertSame(15, Package::query()->count());
        $this->assertSame(59, Media::query()->count());
        $this->assertNotNull($this->section('about', 'values'), 'Missing sections are re-created');
    }

    public function test_dry_run_changes_nothing(): void
    {
        Artisan::call('cms:import', ['--no-files' => true, '--dry-run' => true]);
        $this->assertSame(0, Section::query()->count());
        $this->assertSame(0, Media::query()->count());
    }
}
