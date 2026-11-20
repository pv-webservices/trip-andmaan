<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One itinerary day. Each package owns its own copies; day numbers come from `sort`. */
class PackageDay extends Model
{
    protected $guarded = ['id'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** @return list<string> paragraphs separated by blank lines */
    public static function paragraphsOf(?string $text): array
    {
        $parts = preg_split('/\R\s*\R/', trim((string) $text)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), 'strlen'));
    }
}
