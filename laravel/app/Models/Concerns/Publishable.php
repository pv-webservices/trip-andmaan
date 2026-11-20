<?php

namespace App\Models\Concerns;

use App\Cms\ContentVersion;
use App\Media\MediaUsageRecorder;
use App\Models\Redirect;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * Relationally edited content (packages, destinations) that the public site
 * renders from a snapshot taken at publish time. Edits after publishing stay
 * private until the next publish.
 *
 * @property string $status draft|published|archived
 */
trait Publishable
{
    /** Public URL prefix, e.g. "/tour-packages/". */
    abstract public static function urlPrefix(): string;

    /** Data rendered by the public templates. */
    abstract public function liveData(): array;

    /** @return list<string> reasons the item cannot be published */
    abstract public function publishBlockers(): array;

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_snapshot');
    }

    public function isLive(): bool
    {
        return $this->status === 'published' && $this->published_snapshot !== null;
    }

    public function publicPath(?string $slug = null): string
    {
        return static::urlPrefix().($slug ?? $this->slug).'/';
    }

    public function publish(?User $user): void
    {
        if ($blockers = $this->publishBlockers()) {
            throw ValidationException::withMessages(['publish' => $blockers]);
        }
        $oldSlug = $this->published_slug;
        $this->forceFill([
            'status' => 'published',
            'published_snapshot' => $this->liveData(),
            'published_slug' => $this->slug,
            'published_at' => now(),
            'published_by' => $user?->id,
            'has_unpublished_changes' => false,
        ])->saveQuietly();

        if ($oldSlug && $oldSlug !== $this->slug) {
            Redirect::record($this->publicPath($oldSlug), $this->publicPath(), 'Slug changed');
        }
        // A newly published URL must never be shadowed by an old redirect.
        Redirect::query()->where('from_path', $this->publicPath())->delete();
        MediaUsageRecorder::forModel($this);
        ContentVersion::bump();
    }

    public function unpublish(?User $user, string $status = 'draft'): void
    {
        $this->forceFill([
            'status' => $status,
            'published_snapshot' => null,
            'published_slug' => null,
            'published_by' => $user?->id,
            'has_unpublished_changes' => true,
        ])->saveQuietly();
        MediaUsageRecorder::forModel($this);
        ContentVersion::bump();
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->status === 'archived' => 'Archived',
            $this->isLive() && $this->has_unpublished_changes => 'Published · draft changes',
            $this->isLive() => 'Published',
            default => 'Draft',
        };
    }
}
