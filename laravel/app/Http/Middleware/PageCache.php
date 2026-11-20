<?php

namespace App\Http\Middleware;

use App\Cms\ContentVersion;
use App\Cms\Site;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Caches rendered public HTML for anonymous visitors. The key contains the
 * content version, so every publish is visible on the next request, and the
 * lifetime never passes the next scheduled change (announcement, popup or
 * price validity). Query strings are ignored: pages never depend on them.
 */
class PageCache
{
    public function __construct(private readonly Site $site) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('cms.page_cache.enabled') || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $key = 'page:'.ContentVersion::current().':'.sha1($request->getHost().$request->getPathInfo());
        if ($cached = Cache::get($key)) {
            return $this->withHeaders(new IlluminateResponse($cached['body'], $cached['status'], $cached['headers']), 'HIT');
        }

        $response = $next($request);
        if ($response->getStatusCode() === 200 && $response instanceof IlluminateResponse) {
            $ttl = (int) config('cms.page_cache.max_ttl', 3600);
            $next = $this->site->secondsUntilNextScheduleChange();
            $ttl = $next !== null ? min($ttl, $next) : $ttl;
            if ($ttl > 0) {
                Cache::put($key, [
                    'body' => $response->getContent(),
                    'status' => $response->getStatusCode(),
                    'headers' => ['Content-Type' => $response->headers->get('Content-Type')],
                ], $ttl);
            }
        }

        return $this->withHeaders($response, 'MISS');
    }

    private function withHeaders(Response $response, string $state): Response
    {
        // Browsers and proxies must revalidate, so a publish shows immediately.
        $response->headers->set('Cache-Control', 'public, no-cache');
        $response->headers->set('X-Page-Cache', $state);

        return $response;
    }
}
