<?php

namespace App\Cms;

/** Font files to preload, looked up in the Vite build manifest. */
class Assets
{
    private const PRELOAD = ['manrope-latin-400-normal', 'cormorant-garamond-latin-700-normal'];

    /** @return list<string> */
    public static function fontPreloads(): array
    {
        static $urls = null;
        if ($urls !== null) {
            return $urls;
        }
        $manifest = public_path('build/manifest.json');
        if (! is_file($manifest)) {
            return $urls = [];
        }
        $files = collect(json_decode((string) file_get_contents($manifest), true) ?: [])
            ->flatMap(fn ($entry) => [$entry['file'] ?? null, ...($entry['assets'] ?? [])])
            ->filter()
            ->unique();

        return $urls = collect(self::PRELOAD)
            ->map(fn ($name) => $files->first(fn ($f) => str_contains($f, $name) && str_ends_with($f, '.woff2')))
            ->filter()
            ->map(fn ($f) => '/build/'.$f)
            ->values()
            ->all();
    }
}
