<?php

use App\Http\Controllers\SiteController;
use App\Http\Middleware\PageCache;
use App\Http\Middleware\PublicUrls;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::group([], base_path('routes/site.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('site', [
            SecurityHeaders::class,
            PublicUrls::class,
            PageCache::class,
            SubstituteBindings::class,
        ]);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->redirectGuestsTo('/admin/login');
        // Proxies are trusted only when TRUSTED_PROXIES is set (config/trustedproxy.php),
        // e.g. when a CDN sits in front of the site. Trusting every proxy would let
        // visitors spoof their IP address and evade login throttling.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // Public 404s use the site's own "Page not found" page.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('admin', 'admin/*', 'livewire/*', 'storage/*', 'build/*') || $request->expectsJson()) {
                return null;
            }
            try {
                return app(SiteController::class)->notFound();
            } catch (Throwable) {
                return null;
            }
        });
    })->create();
