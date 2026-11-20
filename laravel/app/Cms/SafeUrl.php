<?php

namespace App\Cms;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Allowed link destinations: site-relative paths ("/about/", "#section"),
 * https/http URLs, tel: and mailto:. Rejects javascript:, data:, vbscript:,
 * protocol-relative URLs and anything with control characters or spaces.
 */
class SafeUrl implements ValidationRule
{
    public static function isAllowed(?string $url): bool
    {
        $url = trim((string) $url);
        if ($url === '') {
            return true;
        }
        if (preg_match('/[\s\x00-\x1F\x7F<>"\'`]/', $url)) {
            return false;
        }
        if (str_starts_with($url, '#')) {
            return true;
        }
        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//') && ! str_starts_with($url, '/\\');
        }
        if (preg_match('/^tel:\+?[0-9()\-.]{3,20}$/', $url)) {
            return true;
        }
        if (preg_match('/^mailto:[^@\s]+@[^@\s]+$/', $url)) {
            return true;
        }

        return (bool) preg_match('#^https?://[a-z0-9.-]+\.[a-z]{2,}(:\d+)?(/|\?|\#|$)#i', $url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isAllowed(is_string($value) ? $value : null)) {
            $fail('Use a page path starting with "/" (for example /contact/), a full https:// address, or a tel: link.');
        }
    }
}
