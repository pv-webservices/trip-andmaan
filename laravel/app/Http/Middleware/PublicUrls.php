<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the existing URL scheme: one canonical host (www redirects to the
 * apex when CMS_CANONICAL_HOST is set) and trailing slashes on page URLs.
 */
class PublicUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = config('cms.canonical_host');
        if ($host && strcasecmp($request->getHost(), $host) !== 0) {
            return new RedirectResponse('https://'.$host.$request->getRequestUri(), 301);
        }

        $path = $request->getPathInfo();
        if (preg_match('#^/(admin|livewire|storage|build|up)(/|$)#', $path)) {
            return $next($request);
        }
        if ($request->isMethodSafe() && $path !== '/' && ! str_ends_with($path, '/')
            && ! str_contains(basename($path), '.')) {
            $query = $request->getQueryString();

            return new RedirectResponse($path.'/'.($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}
