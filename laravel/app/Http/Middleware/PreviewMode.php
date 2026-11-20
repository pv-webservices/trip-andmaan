<?php

namespace App\Http\Middleware;

use App\Cms\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /preview/... renders drafts for signed-in, active administrators only. The
 * response is never cached or indexed.
 */
class PreviewMode
{
    public function __construct(private readonly Site $site) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->is_active) {
            return redirect()->guest('/admin/login');
        }
        $this->site->setPreview(true);
        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        if ($response->isRedirection() && ($location = $response->headers->get('Location'))) {
            $path = parse_url($location, PHP_URL_PATH) ?? '/';
            if (! str_starts_with($path, '/preview') && ! str_starts_with($path, '/admin') && ! parse_url($location, PHP_URL_HOST)) {
                $response->headers->set('Location', '/preview'.$location);
            }
        }

        return $response;
    }
}
