<?php

namespace App\Cms;

use App\Cms\Views\Place;
use App\Cms\Views\Tour;
use App\Media\MediaUsageRecorder;
use App\Models\Announcement;
use App\Models\Destination;
use App\Models\Media;
use App\Models\Package;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Section;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Request-scoped access to the content being rendered. In preview mode
 * (authenticated admins under /preview) drafts and unpublished edits are
 * used; otherwise only published content is ever read.
 */
class Site
{
    private bool $preview = false;

    /** @var array<string, array<string, array>> */
    private array $pages = [];

    /** @var array<int, Media|null> */
    private array $media = [];

    private ?Collection $tours = null;

    private ?Collection $places = null;

    public function setPreview(bool $preview): void
    {
        if ($this->preview !== $preview) {
            $this->flush();
        }
        $this->preview = $preview;
    }

    public function isPreview(): bool
    {
        return $this->preview;
    }

    public function flush(): void
    {
        $this->pages = [];
        $this->media = [];
        $this->tours = null;
        $this->places = null;
    }

    /**
     * Visible sections of a page, keyed by section key.
     *
     * @return array<string, array>
     */
    public function page(string $key): array
    {
        if (! isset($this->pages[$key])) {
            $page = Page::query()->where('key', $key)->first();
            $sections = $page ? $page->sections()->get() : collect();
            $this->pages[$key] = $sections
                ->mapWithKeys(fn (Section $s) => [$s->key => $this->preview ? $s->draft_data : $s->published_data])
                ->filter(fn ($data) => is_array($data) && ($data['visible'] ?? true))
                ->all();
            $this->preloadMedia($this->pages[$key]);
        }

        return $this->pages[$key];
    }

    public function section(string $page, string $section): ?array
    {
        return $this->page($page)[$section] ?? null;
    }

    /** A global setting group such as "contact" or "footer" (always an array). */
    public function global(string $section): array
    {
        return $this->section('global', $section) ?? [];
    }

    public function g(string $section, string $field, mixed $default = null): mixed
    {
        return data_get($this->global($section), $field, $default);
    }

    public function media(mixed $id): ?Media
    {
        if (! is_numeric($id) || (int) $id < 1) {
            return null;
        }
        $id = (int) $id;
        if (! array_key_exists($id, $this->media)) {
            $this->media[$id] = Media::query()->find($id);
        }

        return $this->media[$id];
    }

    public function preloadMedia(array $data): void
    {
        $ids = array_diff(array_unique(array_values(MediaUsageRecorder::extract($data))), array_keys($this->media));
        if ($ids) {
            $found = Media::query()->whereIn('id', $ids)->get()->keyBy('id');
            foreach ($ids as $id) {
                $this->media[$id] = $found[$id] ?? null;
            }
        }
    }

    // ---- Packages and destinations -------------------------------------

    /** @return Collection<int, Tour> listed packages in display order */
    public function tours(): Collection
    {
        if ($this->tours === null) {
            $query = $this->preview
                ? Package::query()->where('status', 'published')->with(['itineraryDays', 'gallery'])
                : Package::query()->published();
            $this->tours = $query->get()
                ->map(fn (Package $p) => new Tour($this->preview ? $p->liveData() : $p->published_snapshot))
                ->sortBy([['sort', 'asc'], ['nights', 'asc']])
                ->values();
            $this->preloadMedia($this->tours->map(fn (Tour $t) => $t->data)->all());
        }

        return $this->tours;
    }

    public function tour(string $slug): ?Tour
    {
        if ($this->preview) {
            $package = Package::query()->where('slug', $slug)->where('status', '!=', 'archived')->first();

            return $package ? new Tour($package->liveData()) : null;
        }
        $package = Package::query()->published()->where('published_slug', $slug)->first();

        return $package ? new Tour($package->published_snapshot) : null;
    }

    /** @return Collection<int, Tour> featured packages in their featured order */
    public function featuredTours(): Collection
    {
        return $this->tours()->filter(fn (Tour $t) => $t->featured)->sortBy('featuredOrder')->values();
    }

    /** @return Collection<int, Place> */
    public function places(): Collection
    {
        if ($this->places === null) {
            $query = $this->preview
                ? Destination::query()->where('status', 'published')
                : Destination::query()->published();
            $this->places = $query->get()
                ->map(fn (Destination $d) => new Place($this->preview ? $d->liveData() : $d->published_snapshot))
                ->sortBy('sort')
                ->values();
            $this->preloadMedia($this->places->map(fn (Place $p) => $p->data)->all());
        }

        return $this->places;
    }

    public function place(string $slug): ?Place
    {
        if ($this->preview) {
            $destination = Destination::query()->where('slug', $slug)->where('status', '!=', 'archived')->first();

            return $destination ? new Place($destination->liveData()) : null;
        }
        $destination = Destination::query()->published()->where('published_slug', $slug)->first();

        return $destination ? new Place($destination->published_snapshot) : null;
    }

    // ---- Contact helpers ------------------------------------------------

    public function whatsappNumber(): string
    {
        return preg_replace('/\D/', '', (string) $this->g('contact', 'whatsapp_number', '919646969646'));
    }

    public function tel(): string
    {
        return '+'.$this->whatsappNumber();
    }

    public function phone(): string
    {
        return (string) $this->g('contact', 'phone_display', '+91 9646 96 9646');
    }

    public function whatsappLink(string $text): string
    {
        // Same encoding as JavaScript's encodeURIComponent, which the original links used.
        $encoded = strtr(rawurlencode($text), ['%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')']);

        return 'https://wa.me/'.$this->whatsappNumber().'?text='.$encoded;
    }

    /** "Hello Trip Andaman, I would like to enquire about …." */
    public function enquiryText(string $subject): string
    {
        $greeting = (string) $this->g('contact', 'greeting', 'Hello Trip Andaman, I would like to enquire about');

        return rtrim($greeting).' '.$subject.'.';
    }

    public function whatsappFor(string $subject): string
    {
        return $this->whatsappLink($this->enquiryText($subject));
    }

    public function siteUrl(): string
    {
        return rtrim((string) config('cms.site_url'), '/');
    }

    public function absolute(string $path): string
    {
        return $this->siteUrl().'/'.ltrim($path, '/');
    }

    // ---- Announcements and popups --------------------------------------

    /** @return Collection<int, Announcement> */
    public function announcements(string $pageKey): Collection
    {
        $now = Carbon::now();

        return Announcement::query()->enabled()->orderBy('sort')->orderBy('id')->get()
            ->filter(fn (Announcement $a) => $a->isScheduledAt($now) && $a->targetsPage($pageKey))
            ->values();
    }

    /**
     * Eligible popups in priority order: highest priority first, then the most
     * recent start date, then the newest campaign. The browser shows the first
     * one its visitor has not dismissed — never more than one per page view.
     *
     * @return Collection<int, Popup>
     */
    public function popups(string $pageKey): Collection
    {
        $now = Carbon::now();

        return Popup::query()->enabled()->get()
            ->filter(fn (Popup $p) => $p->isScheduledAt($now) && $p->targetsPage($pageKey))
            ->sort(fn (Popup $a, Popup $b) => [$b->priority, $b->starts_at?->getTimestamp() ?? 0, $b->id]
                <=> [$a->priority, $a->starts_at?->getTimestamp() ?? 0, $a->id])
            ->take(3)
            ->values();
    }

    /**
     * Seconds until the next scheduled change (announcement/popup start or end,
     * price validity edge), so cached pages never outlive a schedule boundary.
     */
    public function secondsUntilNextScheduleChange(): ?int
    {
        $now = Carbon::now();
        $times = collect()
            ->merge(Announcement::query()->enabled()->get(['starts_at', 'ends_at'])->flatMap(fn ($a) => [$a->starts_at, $a->ends_at]))
            ->merge(Popup::query()->enabled()->get(['starts_at', 'ends_at'])->flatMap(fn ($p) => [$p->starts_at, $p->ends_at]))
            ->merge(Package::query()->published()->get(['published_snapshot'])->flatMap(function ($p) {
                $price = $p->published_snapshot['price'] ?? [];

                return [
                    isset($price['valid_from']) ? Carbon::parse($price['valid_from'])->startOfDay() : null,
                    isset($price['valid_until']) ? Carbon::parse($price['valid_until'])->addDay()->startOfDay() : null,
                ];
            }))
            ->filter(fn ($t) => $t && $t->gt($now));

        return $times->isEmpty() ? null : max(1, (int) ceil($now->diffInSeconds($times->min(), true)));
    }
}
