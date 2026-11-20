<?php

use App\Cms\PageMap;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/** Registers the public page routes (used for the live site and for /preview). */
return function (): void {
    foreach (PageMap::pages() as $key => $page) {
        if ($page['path'] && $page['view']) {
            Route::get($page['path'], [SiteController::class, 'page'])->defaults('key', $key)->name($key);
        }
    }
    Route::get('/tour-packages/{slug}/', [SiteController::class, 'package'])->where('slug', '[a-z0-9-]+')->name('package');
    Route::get('/destinations/{slug}/', [SiteController::class, 'destination'])->where('slug', '[a-z0-9-]+')->name('destination');
};
