<?php

namespace App\Cms;

use App\Cms\Sections\CommonSections;
use App\Cms\Sections\GlobalSections;
use App\Cms\Sections\HomeSections;
use App\Cms\Sections\PageSections;
use App\Cms\Sections\SectionType;
use InvalidArgumentException;

/** Every section type the editor knows, keyed by type key. */
class SectionRegistry
{
    /** @var array<string, SectionType>|null */
    private static ?array $types = null;

    /** @return array<string, SectionType> */
    public static function all(): array
    {
        if (self::$types === null) {
            self::$types = [];
            foreach ([CommonSections::class, GlobalSections::class, HomeSections::class, PageSections::class] as $group) {
                foreach ($group::types() as $type) {
                    self::$types[$type->key] = $type;
                }
            }
        }

        return self::$types;
    }

    public static function get(string $key): SectionType
    {
        return self::all()[$key] ?? throw new InvalidArgumentException("Unknown section type [{$key}]");
    }
}
