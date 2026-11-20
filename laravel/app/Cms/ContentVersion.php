<?php

namespace App\Cms;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * A counter included in every page-cache key. Each publish increments it, so
 * cached public pages are replaced on the very next request.
 */
class ContentVersion
{
    private static ?int $current = null;

    public static function current(): int
    {
        return self::$current ??= (int) (Setting::query()->whereKey('content_version')->value('value') ?? 1);
    }

    public static function bump(): void
    {
        DB::transaction(function () {
            $row = Setting::query()->lockForUpdate()->find('content_version');
            $next = ((int) ($row?->value ?? 1)) + 1;
            Setting::query()->updateOrCreate(['key' => 'content_version'], ['value' => (string) $next]);
            self::$current = $next;
        });
        app(Site::class)->flush();
    }

    /** Forget the per-request copy (tests, long-running commands). */
    public static function reset(): void
    {
        self::$current = null;
    }
}
