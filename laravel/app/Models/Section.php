<?php

namespace App\Models;

use App\Cms\ContentVersion;
use App\Cms\SectionRegistry;
use App\Cms\Sections\SectionType;
use App\Media\MediaUsageRecorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One editable section of a page. Administrators edit `draft_data`; the public
 * site renders only `published_data`. Publishing copies the draft across.
 */
class Section extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
            'published_data' => 'array',
            'draft_updated_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function definition(): SectionType
    {
        return SectionRegistry::get($this->type);
    }

    public function hasUnpublishedChanges(): bool
    {
        return $this->published_data === null || $this->draft_data != $this->published_data;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->published_data === null => 'Never published',
            $this->hasUnpublishedChanges() => 'Draft changes',
            default => 'Published',
        };
    }

    public function saveDraft(array $data, ?User $user): void
    {
        $this->forceFill([
            'draft_data' => $data,
            'draft_updated_at' => now(),
            'updated_by' => $user?->id,
        ])->save();
        MediaUsageRecorder::forSection($this);
    }

    public function publish(?User $user): void
    {
        $this->forceFill([
            'published_data' => $this->draft_data,
            'published_at' => now(),
            'published_by' => $user?->id,
        ])->save();
        MediaUsageRecorder::forSection($this);
        ContentVersion::bump();
    }

    public function discardDraft(?User $user): void
    {
        if ($this->published_data === null) {
            return;
        }
        $this->forceFill([
            'draft_data' => $this->published_data,
            'draft_updated_at' => now(),
            'updated_by' => $user?->id,
        ])->save();
        MediaUsageRecorder::forSection($this);
    }
}
