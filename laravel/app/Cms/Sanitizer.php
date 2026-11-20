<?php

namespace App\Cms;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans section data before it is stored.
 *
 * Conventions in section data:
 *  - keys ending in "_html" hold rich text: sanitised to a small allow-list;
 *  - keys named "url" or ending in "_url" hold links: must pass {@see SafeUrl};
 *  - keys ending in "_id" hold media IDs: cast to int or null;
 *  - every other string: control characters removed and trimmed.
 */
class Sanitizer
{
    private static ?HtmlSanitizer $html = null;

    public static function data(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean[$key] = self::value((string) $key, $value);
        }

        return $clean;
    }

    private static function value(string $key, mixed $value): mixed
    {
        if (is_array($value)) {
            return self::data($value);
        }
        if (str_ends_with($key, '_id')) {
            return is_numeric($value) ? (int) $value : null;
        }
        if (! is_string($value)) {
            return $value;
        }
        if (str_ends_with($key, '_html')) {
            return self::html($value);
        }
        $value = self::text($value);
        if ($key === 'url' || str_ends_with($key, '_url')) {
            return SafeUrl::isAllowed($value) ? $value : '';
        }

        return $value;
    }

    public static function text(string $value): string
    {
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '');
    }

    public static function html(string $value): string
    {
        self::$html ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('b')
                ->allowElement('em')
                ->allowElement('i')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('a', ['href', 'target', 'rel'])
                ->allowLinkSchemes(['https', 'http', 'tel', 'mailto'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener noreferrer'),
        );

        return trim(self::$html->sanitize($value));
    }
}
