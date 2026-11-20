<?php

namespace App\Console\Commands;

use App\Cms\ContentVersion;
use App\Cms\PageMap;
use App\Media\MediaUsageRecorder;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Imports the Astro site's content. Safe to re-run: it only creates records
 * that do not exist yet (matched by stable keys) and never changes records an
 * administrator may have edited.
 */
#[Signature('cms:import
    {--source= : Folder holding the original images/, videos/ and itineraries/ (default: database/content/media, then ../public)}
    {--dry-run : Report what would be created without changing anything}
    {--no-files : Register media records without copying files (automated tests only)}')]
#[Description('Import the existing website content (create-only; never overwrites edits)')]
class ImportContent extends Command
{
    private array $created = [];

    private array $skipped = [];

    private bool $dry = false;

    private array $mediaCache = [];

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        $content = require database_path('content/pages.php');
        $source = $this->sourceDir();
        $this->info(($this->dry ? '[dry run] ' : '').'Importing from '.$source);

        $run = function () use ($content, $source) {
            $this->importMedia($source);
            $this->importPages($content);
            $this->importDestinations($content);
            $this->importPackages($content);
        };
        $this->dry ? $run() : DB::transaction($run);

        if (! $this->dry && $this->created) {
            ContentVersion::bump();
        }
        foreach ($this->created as $type => $count) {
            $this->line(sprintf('  %-12s %d created', $type, $count));
        }
        foreach ($this->skipped as $type => $count) {
            $this->line(sprintf('  %-12s %d already present (left unchanged)', $type, $count));
        }

        return self::SUCCESS;
    }

    private function sourceDir(): string
    {
        $candidates = array_filter([$this->option('source'), database_path('content/media'), base_path('../public')]);
        foreach ($candidates as $dir) {
            if (is_dir($dir.'/images') && is_dir($dir.'/videos')) {
                return rtrim(realpath($dir), '/\\');
            }
        }
        throw new RuntimeException('Original media not found. Pass --source=<folder containing images/, videos/ and itineraries/>.');
    }

    private function count(string $type, bool $created): void
    {
        $bucket = $created ? 'created' : 'skipped';
        $this->{$bucket}[$type] = ($this->{$bucket}[$type] ?? 0) + 1;
    }

    // ---- Media -----------------------------------------------------------

    private function importMedia(string $source): void
    {
        $manifest = json_decode(file_get_contents(database_path('content/image-manifest.json')), true);
        $credits = collect(json_decode(file_get_contents(database_path('content/credits.json')), true))->keyBy('name');
        $disk = Storage::disk(config('cms.media.disk'));
        // Credited photos first, in the order of the credits page.
        $manifest = array_replace(array_intersect_key(array_flip($credits->keys()->all()), $manifest), $manifest);

        foreach ($manifest as $name => $info) {
            if (Media::query()->where('legacy_key', "image:{$name}")->exists()) {
                $this->count('images', false);

                continue;
            }
            $variants = [];
            $legacy = [];
            $size = 0;
            foreach ($info['widths'] as $width) {
                $from = "{$source}/images/{$info['folder']}/{$name}-{$width}.webp";
                if (! is_file($from)) {
                    throw new RuntimeException("Missing image file {$from}");
                }
                $to = "media/imported/{$info['folder']}/{$name}-{$width}.webp";
                $variants[(string) $width] = $to;
                $legacy[] = "/images/{$info['folder']}/{$name}-{$width}.webp";
                $size = filesize($from);
                if (! $this->dry && ! $this->option('no-files')) {
                    $disk->put($to, file_get_contents($from));
                }
            }
            $credit = $credits->get($name);
            if (! $this->dry) {
                Media::query()->create([
                    'type' => 'image', 'disk' => config('cms.media.disk'),
                    'path' => end($variants), 'original_name' => "{$name}.webp", 'mime' => 'image/webp', 'size' => $size,
                    'width' => $info['width'], 'height' => $info['height'], 'variants' => $variants,
                    'title' => $name, 'legacy_key' => "image:{$name}", 'legacy_paths' => $legacy,
                    'credit_title' => $credit['title'] ?? null, 'credit_author' => $credit['author'] ?? null,
                    'credit_license' => $credit['license'] ?? null, 'credit_license_url' => $credit['licenseUrl'] ?? null,
                    'credit_source_url' => $credit['source'] ?? null, 'credit_changes' => $credit['changes'] ?? null,
                ]);
            }
            $this->count('images', true);
        }

        foreach (glob("{$source}/videos/*.mp4") as $file) {
            $this->importFile($file, 'video', 'video/mp4', 'videos', '/videos/');
        }
        foreach (glob("{$source}/itineraries/*.pdf") as $file) {
            $this->importFile($file, 'document', 'application/pdf', 'itineraries', '/itineraries/');
        }
    }

    private function importFile(string $file, string $type, string $mime, string $folder, string $legacyPrefix): void
    {
        $base = basename($file);
        $key = $type === 'video' ? 'video:'.pathinfo($base, PATHINFO_FILENAME) : "document:{$base}";
        if (Media::query()->where('legacy_key', $key)->exists()) {
            $this->count($folder, false);

            return;
        }
        $to = "media/imported/{$folder}/{$base}";
        if (! $this->dry) {
            if (! $this->option('no-files')) {
                Storage::disk(config('cms.media.disk'))->put($to, fopen($file, 'rb'));
            }
            Media::query()->create([
                'type' => $type, 'disk' => config('cms.media.disk'), 'path' => $to, 'original_name' => $base,
                'mime' => $mime, 'size' => filesize($file), 'title' => pathinfo($base, PATHINFO_FILENAME),
                'legacy_key' => $key, 'legacy_paths' => [$legacyPrefix.$base],
            ]);
        }
        $this->count($folder, true);
    }

    /** Resolve "img:name" / "vid:name" / "doc:file" references in keys ending in "_id". */
    private function resolve(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->resolve($value);
            } elseif (is_string($value) && str_ends_with((string) $key, '_id')) {
                $data[$key] = $this->mediaId($value);
            }
        }

        return $data;
    }

    private function mediaId(?string $ref): ?int
    {
        if (! $ref) {
            return null;
        }
        [$prefix, $name] = explode(':', $ref, 2);
        $key = ['img' => 'image', 'vid' => 'video', 'doc' => 'document'][$prefix].':'.$name;

        return $this->mediaCache[$key] ??= Media::query()->where('legacy_key', $key)->value('id')
            ?? ($this->dry ? null : throw new RuntimeException("Unknown media reference {$ref}"));
    }

    // ---- Pages and sections ---------------------------------------------

    private function importPages(array $content): void
    {
        $sort = 0;
        foreach (PageMap::pages() as $key => $definition) {
            $page = Page::query()->where('key', $key)->first();
            if (! $page && ! $this->dry) {
                $page = Page::query()->create(['key' => $key, 'title' => $definition['title'], 'path' => $definition['path'], 'kind' => $definition['kind'], 'sort' => $sort]);
            }
            $sort++;
            $sectionSort = 0;
            foreach ($definition['sections'] as $sectionKey => [$type, $label]) {
                $sectionSort++;
                if ($page && Section::query()->where('page_id', $page->id)->where('key', $sectionKey)->exists()) {
                    $this->count('sections', false);

                    continue;
                }
                $data = $content[$key][$sectionKey] ?? null;
                if ($data === null) {
                    $this->warn("  No initial content for {$key}.{$sectionKey}; it starts empty and unpublished.");
                }
                if (! $this->dry) {
                    $data = $data === null ? null : $this->resolve($data);
                    $section = Section::query()->create([
                        'page_id' => $page->id, 'key' => $sectionKey, 'type' => $type, 'label' => $label, 'sort' => $sectionSort,
                        'draft_data' => $data ?? [], 'published_data' => $data,
                        'draft_updated_at' => now(), 'published_at' => $data === null ? null : now(),
                    ]);
                    MediaUsageRecorder::forSection($section);
                }
                $this->count('sections', true);
            }
        }
    }

    // ---- Destinations and packages --------------------------------------

    private function importDestinations(array $content): void
    {
        $extras = $content['_destination_extras'];
        foreach (json_decode(file_get_contents(database_path('content/destinations.json')), true) as $i => $d) {
            if (Destination::query()->where('import_key', 'destination:'.$d['slug'])->exists()) {
                $this->count('destinations', false);

                continue;
            }
            if (! $this->dry) {
                $extra = $extras[$d['slug']] ?? [];
                $destination = Destination::query()->create([
                    'slug' => $d['slug'], 'name' => $d['name'], 'official_name' => $d['official'],
                    'image_id' => $this->mediaId('img:'.$d['image']),
                    'secondary_image_id' => isset($extra['secondary']) ? $this->mediaId('img:'.$extra['secondary'][0]) : null,
                    'secondary_alt' => $extra['secondary'][1] ?? null,
                    'secondary_portrait' => $extra['secondary'][2] ?? false,
                    'short' => $d['short'], 'intro' => $d['intro'], 'body' => $d['text'],
                    'highlights' => $d['highlights'], 'note' => $d['note'], 'source_url' => $d['source'],
                    'reels' => isset($extra['reels']) ? $this->resolve($extra['reels']) : [],
                    'sort' => $i, 'import_key' => 'destination:'.$d['slug'],
                ]);
                $destination->publish(null);
            }
            $this->count('destinations', true);
        }
    }

    private function importPackages(array $content): void
    {
        $captions = $content['_moment_captions'];
        $featured = array_flip($content['_featured']);
        foreach (json_decode(file_get_contents(database_path('content/packages.json')), true) as $i => $p) {
            if (Package::query()->where('import_key', 'package:'.$p['slug'])->exists()) {
                $this->count('packages', false);

                continue;
            }
            if (! $this->dry) {
                $notes = collect($p['itinerary'])->contains(fn ($d) => str_contains($d['title'], 'Baratang'))
                    ? ['For Baratang, departure is very early to join the first convoy from Jirkatang at 6 am.'] : [];
                $package = Package::query()->create([
                    'slug' => $p['slug'], 'title' => $p['title'], 'overview' => $p['overview'],
                    'nights' => $p['nights'], 'days' => $p['days'],
                    'image_id' => $this->mediaId('img:'.$p['image']), 'image_alt' => $p['alt'],
                    'stays' => $p['stays'], 'tags' => $p['tags'], 'inclusions' => [], 'exclusions' => [], 'notes' => $notes,
                    // Prices were not supplied by the client: every package is "price on request".
                    'price_mode' => 'on_request', 'currency' => 'INR', 'price_unit' => 'per_person',
                    'itinerary_pdf_id' => isset($p['itineraryPdf']) ? $this->mediaId('doc:'.basename($p['itineraryPdf'])) : null,
                    'is_featured' => isset($featured[$p['slug']]), 'featured_order' => $featured[$p['slug']] ?? 0,
                    'sort' => $i, 'status' => 'draft', 'import_key' => 'package:'.$p['slug'],
                ]);
                foreach ($p['itinerary'] as $n => $day) {
                    $package->itineraryDays()->create(['sort' => $n, 'title' => $day['title'], 'description' => implode("\n\n", $day['description'])]);
                }
                foreach ($p['moments'] as $n => $moment) {
                    $package->gallery()->create(['media_id' => $this->mediaId('img:'.$moment), 'sort' => $n, 'caption' => $captions[$moment] ?? null, 'alt' => $captions[$moment] ?? null]);
                }
                $package->publish(null);
            }
            $this->count('packages', true);
        }
    }
}
