<?php

namespace App\Http\Controllers;

use App\Cms\PageMap;
use App\Cms\Site;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Server-rendered public pages, sitemap, robots and legacy URL redirects. */
class SiteController extends Controller
{
    public function __construct(private readonly Site $site) {}

    public function page(string $key): Response
    {
        $definition = PageMap::pages()[$key] ?? abort(404);

        return $this->render($key, $definition['view'], ['noindex' => ! $definition['index']]);
    }

    public function package(string $slug): Response|RedirectResponse
    {
        $tour = $this->site->tour($slug);
        if (! $tour) {
            return $this->redirectOrNotFound(request());
        }
        $this->site->preloadMedia($tour->data);

        return $this->render('package_detail', 'pages.package', ['tour' => $tour]);
    }

    public function destination(string $slug): Response|RedirectResponse
    {
        $place = $this->site->place($slug);
        if (! $place) {
            return $this->redirectOrNotFound(request());
        }
        $this->site->preloadMedia($place->data);

        return $this->render('destination_detail', 'pages.destination', ['place' => $place]);
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /preview/\n\nSitemap: {$this->site->absolute('/sitemap.xml')}\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function sitemap(): Response
    {
        $pages = Page::query()->with('sections')->get()->keyBy('key');
        $lastmod = function (array $keys) use ($pages): ?string {
            $dates = collect($keys)->flatMap(fn ($k) => $pages[$k]?->sections->pluck('published_at') ?? [])->filter();

            return $dates->max()?->toDateString();
        };

        $urls = [];
        foreach (PageMap::pages() as $key => $page) {
            if ($page['path'] && $page['index']) {
                $urls[] = [$page['path'], $lastmod([$key])];
            }
        }
        $template = $lastmod(['package_detail']);
        foreach (Package::query()->published()->orderBy('sort')->get(['published_slug', 'published_at']) as $p) {
            $urls[] = ['/tour-packages/'.$p->published_slug.'/', collect([$p->published_at?->toDateString(), $template])->filter()->max()];
        }
        $template = $lastmod(['destination_detail']);
        foreach (Destination::query()->published()->orderBy('sort')->get(['published_slug', 'published_at']) as $d) {
            $urls[] = ['/destinations/'.$d->published_slug.'/', collect([$d->published_at?->toDateString(), $template])->filter()->max()];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$path, $date]) {
            $xml .= '<url><loc>'.htmlspecialchars($this->site->absolute($path), ENT_XML1).'</loc>'.($date ? "<lastmod>{$date}</lastmod>" : '')."</url>\n";
        }

        return response($xml.'</urlset>'."\n", 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    /** Old static URLs (/images/…, /videos/…, /itineraries/…) of imported media. */
    public function legacyMedia(Request $request): RedirectResponse|Response
    {
        $path = $request->path();
        $media = null;
        $width = null;
        if (preg_match('#^images/[a-z-]+/([a-z0-9-]+)-(\d+)\.webp$#', $path, $m)) {
            $media = Media::query()->where('legacy_key', 'image:'.$m[1])->first();
            $width = (int) $m[2];
        } elseif (preg_match('#^videos/([a-z0-9-]+)\.mp4$#', $path, $m)) {
            $media = Media::query()->where('legacy_key', 'video:'.$m[1])->first();
        } elseif (preg_match('#^itineraries/([a-z0-9-]+\.pdf)$#', $path, $m)) {
            $media = Media::query()->where('legacy_key', 'document:'.$m[1])->first();
        }
        if (! $media) {
            return $this->notFound();
        }

        return new RedirectResponse($width ? $media->urlNear($width) : $media->url(), 301);
    }

    public function fallback(Request $request): RedirectResponse|Response
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');
        if (preg_match('#^(.*/)index\.html$#', $path, $m)) {
            return new RedirectResponse($this->prefix().$m[1], 301);
        }

        return $this->redirectOrNotFound($request);
    }

    private function redirectOrNotFound(Request $request): RedirectResponse|Response
    {
        $path = '/'.trim($request->getPathInfo(), '/');
        $path = str_starts_with($path, '/preview') ? substr($path, 8) ?: '/' : $path;
        $redirect = Redirect::query()->whereIn('from_path', [$path, rtrim($path, '/').'/'])->first();
        if ($redirect) {
            return new RedirectResponse($this->prefix().$redirect->to_path, $redirect->status_code);
        }

        return $this->notFound();
    }

    public function notFound(): Response
    {
        return $this->render('not_found', 'pages.not-found', ['noindex' => true], 404);
    }

    private function prefix(): string
    {
        return $this->site->isPreview() ? '/preview' : '';
    }

    private function render(string $key, string $view, array $data = [], int $status = 200): Response
    {
        $sections = $this->site->page($key);

        return response()->view($view, [
            'pageKey' => $key,
            's' => $sections,
            'meta' => $sections['meta'] ?? [],
            'noindex' => $data['noindex'] ?? false,
            ...$data,
        ], $status);
    }
}
