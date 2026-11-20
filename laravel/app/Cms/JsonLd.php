<?php

namespace App\Cms;

/**
 * Serialises a schema.org graph for a <script type="application/ld+json"> tag.
 * Built in PHP because Blade would treat "@context" in a template as a directive.
 * "<" and ">" are escaped so content can never close the script element.
 */
class JsonLd
{
    public static function graph(array $graph): string
    {
        return json_encode(['@context' => 'https://schema.org', '@graph' => array_values($graph)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }
}
