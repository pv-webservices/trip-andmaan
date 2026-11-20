<?php

namespace Tests\Feature;

use App\Filament\Resources\Destinations\DestinationResource;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Resources\Packages\PackageResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Sections\SectionResource;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use App\Models\Section;
use Tests\TestCase;

/** Every admin screen opens for a signed-in administrator. */
class AdminScreensTest extends TestCase
{
    public function test_every_admin_screen_loads(): void
    {
        $this->importContent();
        $this->actingAs($this->admin());

        $urls = [
            '/admin',
            PageResource::getUrl(),
            PackageResource::getUrl(),
            PackageResource::getUrl('create'),
            PackageResource::getUrl('edit', ['record' => Package::query()->first()]),
            DestinationResource::getUrl(),
            DestinationResource::getUrl('create'),
            DestinationResource::getUrl('edit', ['record' => Destination::query()->first()]),
            MediaResource::getUrl(),
            MediaResource::getUrl('create'),
            MediaResource::getUrl('edit', ['record' => Media::query()->where('type', 'image')->first()]),
            MediaResource::getUrl('edit', ['record' => Media::query()->where('type', 'video')->first()]),
            MediaResource::getUrl('edit', ['record' => Media::query()->where('type', 'document')->first()]),
            '/admin/announcements',
            '/admin/popups',
            '/admin/redirects',
            '/admin/users',
            '/admin/profile',
        ];
        foreach (Page::query()->get() as $page) {
            $urls[] = PageResource::getUrl('view', ['record' => $page]);
        }
        // Every section editor (each section type's form).
        foreach (Section::query()->get() as $section) {
            $urls[] = SectionResource::getUrl('edit', ['record' => $section]);
        }

        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
        $this->assertGreaterThan(90, count($urls));
    }
}
