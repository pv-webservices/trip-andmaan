<?php

namespace App\Models;

use App\Cms\Views\Price;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A tour package. Itinerary days and gallery photos belong to this package only,
 * so editing one package never changes another.
 */
class Package extends Model
{
    use Publishable;

    public const PRICE_MODES = [
        'on_request' => 'Price on request',
        'from' => 'Starting price ("From …")',
        'fixed' => 'Fixed price',
    ];

    public const PRICE_UNITS = [
        'per_person' => 'per person',
        'per_couple' => 'per couple',
        'per_group' => 'per group',
        'per_package' => 'per package',
    ];

    public const CURRENCIES = ['INR' => 'INR (₹)', 'USD' => 'USD ($)', 'EUR' => 'EUR (€)'];

    /** Slugs used by fixed pages under /tour-packages/. */
    public const RESERVED_SLUGS = ['customized-trip'];

    protected $guarded = ['id'];

    protected $attributes = [
        'price_mode' => 'on_request',
        'currency' => 'INR',
        'price_unit' => 'per_person',
        'focal' => '50% 50%',
        'status' => 'draft',
        'has_unpublished_changes' => true,
        'is_featured' => false,
        'featured_order' => 0,
        'sort' => 0,
    ];

    protected function casts(): array
    {
        return [
            'stays' => 'array',
            'tags' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'notes' => 'array',
            'published_snapshot' => 'array',
            'price_amount' => 'decimal:2',
            'price_original' => 'decimal:2',
            'price_valid_from' => 'date',
            'price_valid_until' => 'date',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'has_unpublished_changes' => 'boolean',
            'nights' => 'integer',
            'days' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Package $package) {
            if ($package->isDirty() && ! $package->isDirty(['published_snapshot', 'has_unpublished_changes'])) {
                $package->has_unpublished_changes = true;
            }
        });
    }

    public static function urlPrefix(): string
    {
        return '/tour-packages/';
    }

    public function itineraryDays(): HasMany
    {
        return $this->hasMany(PackageDay::class)->orderBy('sort')->orderBy('id');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(PackageMedia::class)->orderBy('sort')->orderBy('id');
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function mobileImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'mobile_image_id');
    }

    public function itineraryPdf(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'itinerary_pdf_id');
    }

    public function liveData(): array
    {
        $this->loadMissing(['itineraryDays', 'gallery']);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'overview' => $this->overview,
            'nights' => (int) $this->nights,
            'days' => (int) $this->days,
            'image_id' => $this->image_id,
            'mobile_image_id' => $this->mobile_image_id,
            'image_alt' => $this->image_alt,
            'focal' => $this->focal ?: '50% 50%',
            'stays' => array_values($this->stays ?? []),
            'tags' => array_values($this->tags ?? []),
            'inclusions' => array_values($this->inclusions ?? []),
            'exclusions' => array_values($this->exclusions ?? []),
            'notes' => array_values($this->notes ?? []),
            'price' => $this->priceData(),
            'itinerary_pdf_id' => $this->itinerary_pdf_id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'is_featured' => (bool) $this->is_featured,
            'featured_order' => (int) $this->featured_order,
            'sort' => (int) $this->sort,
            'itinerary' => $this->itineraryDays->map(fn (PackageDay $d) => [
                'title' => $d->title,
                'paragraphs' => PackageDay::paragraphsOf($d->description),
            ])->values()->all(),
            'gallery' => $this->gallery->map(fn (PackageMedia $m) => [
                'media_id' => $m->media_id,
                'caption' => $m->caption,
                'alt' => $m->alt,
            ])->values()->all(),
        ];
    }

    public function priceData(): array
    {
        return [
            'mode' => $this->price_mode,
            'currency' => $this->currency,
            'amount' => $this->price_amount !== null ? (string) $this->price_amount : null,
            'original' => $this->price_original !== null ? (string) $this->price_original : null,
            'unit' => $this->price_unit,
            'valid_from' => $this->price_valid_from?->toDateString(),
            'valid_until' => $this->price_valid_until?->toDateString(),
            'note' => $this->price_note,
        ];
    }

    public function publishBlockers(): array
    {
        $blockers = [];
        if (blank($this->title) || blank($this->slug) || blank($this->overview)) {
            $blockers[] = 'Add a title, URL slug and overview.';
        }
        if ($this->nights < 1 || $this->days < 1) {
            $blockers[] = 'Set the number of nights and days.';
        }
        if ($this->itineraryDays()->count() === 0) {
            $blockers[] = 'Add at least one itinerary day.';
        }
        if (! $this->image_id) {
            $blockers[] = 'Choose a main image.';
        }
        if ($this->price_mode !== 'on_request' && $this->price_amount === null) {
            $blockers[] = 'Enter a price amount, or switch pricing to "Price on request".';
        }

        return $blockers;
    }

    /** @return list<string> non-blocking consistency warnings */
    public function warnings(): array
    {
        $warnings = [];
        $dayCount = $this->exists ? $this->itineraryDays()->count() : 0;
        if ($this->nights && $this->days && $this->days !== $this->nights + 1) {
            $warnings[] = "The duration is {$this->nights} nights / {$this->days} days. Packages normally have one more day than nights.";
        }
        if ($this->days && $dayCount && $dayCount !== $this->days) {
            $warnings[] = "The itinerary has {$dayCount} days but the duration says {$this->days} days.";
        }
        $stayNights = collect($this->stays ?? [])->sum(fn ($s) => (int) ($s['nights'] ?? 0));
        if ($this->stays && $stayNights !== (int) $this->nights) {
            $warnings[] = "The stays add up to {$stayNights} nights but the package is {$this->nights} nights.";
        }
        if ($this->price_original !== null && $this->price_amount !== null && Price::compare((string) $this->price_original, (string) $this->price_amount) <= 0) {
            $warnings[] = 'The original price should be higher than the price; otherwise it is not shown.';
        }
        if ($this->price_valid_until && $this->price_valid_until->lt(Carbon::today())) {
            $warnings[] = 'The price validity ended on '.$this->price_valid_until->format('j M Y').'; the website shows "Price on request" instead.';
        }

        return $warnings;
    }

    /** Copy as a new unpublished draft with its own itinerary days and gallery. */
    public function duplicate(?User $user): self
    {
        $this->loadMissing(['itineraryDays', 'gallery']);
        $copy = $this->replicate(['slug', 'status', 'published_snapshot', 'published_slug', 'published_at', 'published_by', 'import_key', 'is_featured']);
        $base = $this->slug.'-copy';
        $slug = $base;
        for ($i = 2; static::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        $copy->forceFill([
            'slug' => $slug,
            'title' => $this->title.' (copy)',
            'status' => 'draft',
            'is_featured' => false,
            'updated_by' => $user?->id,
        ])->save();
        foreach ($this->itineraryDays as $day) {
            $copy->itineraryDays()->create($day->only(['sort', 'title', 'description']));
        }
        foreach ($this->gallery as $photo) {
            $copy->gallery()->create($photo->only(['media_id', 'sort', 'caption', 'alt']));
        }

        return $copy;
    }
}
