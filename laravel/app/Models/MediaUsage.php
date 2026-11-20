<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Where a media item is used. Rebuilt from content every time its owner is saved. */
class MediaUsage extends Model
{
    protected $guarded = ['id'];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
