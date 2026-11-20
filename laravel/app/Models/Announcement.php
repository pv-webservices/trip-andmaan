<?php

namespace App\Models;

use App\Cms\ContentVersion;
use App\Models\Concerns\Targetable;
use Illuminate\Database\Eloquent\Model;

/** A message in the scrolling announcement bar. Saving takes effect immediately. */
class Announcement extends Model
{
    use Targetable;

    protected $guarded = ['id'];

    protected $attributes = ['targeting' => 'all', 'is_enabled' => true, 'sort' => 0];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'page_keys' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => ContentVersion::bump());
        static::deleted(fn () => ContentVersion::bump());
    }
}
