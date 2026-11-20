<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A public page, a shared template (package/destination detail) or the global settings group. */
class Page extends Model
{
    protected $guarded = ['id'];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('sort');
    }

    public function isGlobal(): bool
    {
        return $this->kind === 'global';
    }

    /** URL that previews this page (templates preview their first item). */
    public function previewUrl(): string
    {
        if ($this->path) {
            return '/preview'.$this->path;
        }

        return match ($this->key) {
            'package_detail' => ($p = Package::query()->where('status', '!=', 'archived')->orderBy('sort')->first())
                ? '/preview/tour-packages/'.$p->slug.'/' : '/preview/tour-packages/',
            'destination_detail' => ($d = Destination::query()->where('status', '!=', 'archived')->orderBy('sort')->first())
                ? '/preview/destinations/'.$d->slug.'/' : '/preview/destinations/',
            default => '/preview/',
        };
    }
}
