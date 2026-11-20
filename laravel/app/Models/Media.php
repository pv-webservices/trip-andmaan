<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A file in the shared media library: an image (with WebP variants), a video or a PDF. */
class Media extends Model
{
    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'legacy_paths' => 'array',
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }
        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(fn (Builder $q) => $q->where('title', 'like', $like)
            ->orWhere('original_name', 'like', $like)
            ->orWhere('alt', 'like', $like)
            ->orWhere('caption', 'like', $like)
            ->orWhere('legacy_key', 'like', $like));
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public static function urlFor(string $path): string
    {
        return '/storage/'.ltrim($path, '/');
    }

    public function url(): string
    {
        return self::urlFor($this->path);
    }

    /** @return array<int, string> width => URL, smallest first */
    public function variantUrls(): array
    {
        $variants = collect($this->variants ?? [])
            ->mapWithKeys(fn ($path, $width) => [(int) $width => self::urlFor($path)])
            ->sortKeys()
            ->all();

        return $variants ?: [($this->width ?: 1) => $this->url()];
    }

    public function srcset(): string
    {
        return collect($this->variantUrls())
            ->map(fn ($url, $width) => "{$url} {$width}w")
            ->implode(', ');
    }

    /** A derivative at least the requested width, else the largest one. */
    public function urlNear(int $target): string
    {
        $variants = $this->variantUrls();
        foreach ($variants as $width => $url) {
            if ($width >= $target) {
                return $url;
            }
        }

        return end($variants);
    }

    public function largestUrl(): string
    {
        $variants = $this->variantUrls();

        return end($variants);
    }

    /** Intrinsic size of the largest derivative, for width/height attributes. */
    public function displayWidth(): ?int
    {
        if (! $this->width) {
            return null;
        }
        $widths = array_keys($this->variants ?? []);

        return $widths ? min($this->width, max(array_map('intval', $widths))) : $this->width;
    }

    public function displayHeight(): ?int
    {
        if (! $this->width || ! $this->height) {
            return null;
        }

        return (int) round($this->height * $this->displayWidth() / $this->width);
    }

    public function hasCredit(): bool
    {
        return filled($this->credit_author) && filled($this->credit_source_url);
    }

    public function label(): string
    {
        return $this->title ?: ($this->original_name ?: 'Media #'.$this->id);
    }

    public function humanSize(): string
    {
        $size = (float) $this->size;
        foreach (['B', 'KB', 'MB'] as $unit) {
            if ($size < 1024) {
                return ($unit === 'B' ? (int) $size : number_format($size, 1)).' '.$unit;
            }
            $size /= 1024;
        }

        return number_format($size, 1).' GB';
    }

    /** Every stored file of this record (main file + variants). */
    public function allPaths(): array
    {
        return array_values(array_unique(array_filter([$this->path, ...array_values($this->variants ?? [])])));
    }
}
