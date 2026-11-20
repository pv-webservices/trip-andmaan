<?php

namespace App\Models;

use App\Cms\ContentVersion;
use App\Media\MediaUsageRecorder;
use App\Models\Concerns\Targetable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A popup banner campaign. `campaign_version` changes whenever the visible
 * content changes, so a visitor's dismissal of an earlier campaign never hides
 * a different one.
 */
class Popup extends Model
{
    use Targetable;

    public const FREQUENCIES = [
        'session' => 'Once per browser session',
        'day' => 'Once per day',
        'week' => 'Once per week',
        'once' => 'Only once (until the campaign changes)',
    ];

    /** Fields whose change makes this a new campaign for visitors. */
    private const CAMPAIGN_FIELDS = ['image_id', 'image_alt', 'heading', 'description', 'button_label', 'button_url', 'frequency', 'starts_at', 'ends_at'];

    protected $guarded = ['id'];

    protected $attributes = ['targeting' => 'all', 'frequency' => 'session', 'delay_seconds' => 5, 'priority' => 10, 'is_enabled' => false];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'page_keys' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'delay_seconds' => 'integer',
            'priority' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Popup $popup) {
            if (! $popup->campaign_version || $popup->isDirty(self::CAMPAIGN_FIELDS)) {
                $popup->campaign_version = substr(bin2hex(random_bytes(8)), 0, 12);
            }
        });
        static::saved(function (Popup $popup) {
            MediaUsageRecorder::forModel($popup);
            ContentVersion::bump();
        });
        static::deleting(fn (Popup $popup) => MediaUsageRecorder::clear($popup));
        static::deleted(fn () => ContentVersion::bump());
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }
}
