<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Page targeting and Asia/Kolkata scheduling shared by announcements and popups.
 * `page_keys` holds stable page keys (e.g. "home", "package_detail").
 */
trait Targetable
{
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function isScheduledAt(CarbonInterface $at): bool
    {
        return (! $this->starts_at || $this->starts_at->lte($at))
            && (! $this->ends_at || $this->ends_at->gt($at));
    }

    public function targetsPage(string $pageKey): bool
    {
        return $this->targeting !== 'selected' || in_array($pageKey, $this->page_keys ?? [], true);
    }

    public function scheduleLabel(): string
    {
        $fmt = fn (?CarbonInterface $d) => $d?->timezone('Asia/Kolkata')->format('j M Y, g:i a');

        return match (true) {
            ! $this->starts_at && ! $this->ends_at => 'Always',
            ! $this->ends_at => 'From '.$fmt($this->starts_at),
            ! $this->starts_at => 'Until '.$fmt($this->ends_at),
            default => $fmt($this->starts_at).' – '.$fmt($this->ends_at),
        };
    }
}
