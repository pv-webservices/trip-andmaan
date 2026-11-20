<?php

namespace Tests;

use App\Cms\ContentVersion;
use App\Cms\Site;
use App\Models\Page;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        ContentVersion::reset();
        Storage::fake('public');
    }

    /** Keep trailing slashes: the site's URLs end in "/" (Laravel's default trims them). */
    protected function prepareUrlForRequest($uri)
    {
        $url = parent::prepareUrlForRequest($uri);

        return str_ends_with((string) $uri, '/') && ! str_ends_with($url, '/') ? $url.'/' : $url;
    }

    /** Import the real site content (media rows only; files are not needed for HTML tests). */
    protected function importContent(): void
    {
        Artisan::call('cms:import', ['--no-files' => true]);
        ContentVersion::reset();
        app(Site::class)->flush();
    }

    protected function admin(array $attributes = []): User
    {
        return User::factory()->create(['is_active' => true, ...$attributes]);
    }

    protected function section(string $page, string $key): Section
    {
        return Page::query()->where('key', $page)->firstOrFail()->sections()->where('key', $key)->firstOrFail();
    }

    /** Fresh request state between simulated visits (the Site object is request-scoped). */
    protected function visit(string $uri): TestResponse
    {
        app(Site::class)->flush();
        ContentVersion::reset();

        return $this->get($uri);
    }
}
