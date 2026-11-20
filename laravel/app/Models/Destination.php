<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An island / destination guide at /destinations/{slug}/. */
class Destination extends Model
{
    use Publishable;

    protected $guarded = ['id'];

    protected $attributes = ['focal' => '50% 50%', 'status' => 'draft', 'has_unpublished_changes' => true, 'sort' => 0, 'secondary_portrait' => false];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'reels' => 'array',
            'published_snapshot' => 'array',
            'published_at' => 'datetime',
            'secondary_portrait' => 'boolean',
            'has_unpublished_changes' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Destination $destination) {
            if ($destination->isDirty() && ! $destination->isDirty(['published_snapshot', 'has_unpublished_changes'])) {
                $destination->has_unpublished_changes = true;
            }
        });
    }

    public static function urlPrefix(): string
    {
        return '/destinations/';
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function liveData(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'official' => $this->official_name,
            'image_id' => $this->image_id,
            'mobile_image_id' => $this->mobile_image_id,
            'image_alt' => $this->image_alt,
            'focal' => $this->focal ?: '50% 50%',
            'secondary_image_id' => $this->secondary_image_id,
            'secondary_alt' => $this->secondary_alt,
            'secondary_portrait' => (bool) $this->secondary_portrait,
            'short' => $this->short,
            'intro' => $this->intro,
            'text' => $this->body,
            'highlights' => array_values($this->highlights ?? []),
            'note' => $this->note,
            'source' => $this->source_url,
            'reels' => array_values($this->reels ?? []),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'sort' => (int) $this->sort,
        ];
    }

    public function publishBlockers(): array
    {
        $blockers = [];
        if (blank($this->name) || blank($this->slug) || blank($this->official_name)) {
            $blockers[] = 'Add a name, official name and URL slug.';
        }
        if (blank($this->short) || blank($this->intro) || blank($this->body)) {
            $blockers[] = 'Add the tagline, introduction and description.';
        }
        if (! $this->image_id) {
            $blockers[] = 'Choose a main image.';
        }

        return $blockers;
    }
}
