<?php

use App\Http\Controllers\SiteController;
use App\Http\Middleware\PreviewMode;
use Illuminate\Support\Facades\Route;

// Authenticated draft preview of every public page: /preview/<public path>.
// Runs in the "web" group (sessions) so the administrator's login is known.
$pages = require __DIR__.'/pages.php';

Route::middleware(PreviewMode::class)->prefix('preview')->name('preview.')->group(function () use ($pages) {
    $pages();
    Route::get('{any}', [SiteController::class, 'fallback'])->where('any', '.*')->name('fallback');
});
