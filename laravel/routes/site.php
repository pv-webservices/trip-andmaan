<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

// Public website. The "site" middleware group has no sessions, cookies or
// CSRF: visitors are anonymous and pages are served from the page cache.
$pages = require __DIR__.'/pages.php';

Route::middleware('site')->group(function () use ($pages) {
    $pages();
    Route::get('/robots.txt', [SiteController::class, 'robots']);
    Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
    // Old static media URLs from the Astro site redirect to the media library.
    Route::get('/images/{path}', [SiteController::class, 'legacyMedia'])->where('path', '.*');
    Route::get('/videos/{path}', [SiteController::class, 'legacyMedia'])->where('path', '.*');
    Route::get('/itineraries/{path}', [SiteController::class, 'legacyMedia'])->where('path', '.*');
    Route::fallback([SiteController::class, 'fallback']);
});
