<?php

return [
    // Canonical public origin used for canonical tags, Open Graph URLs and the sitemap.
    'site_url' => env('CMS_SITE_URL', env('APP_URL', 'http://localhost')),

    // When set (e.g. "tripandaman.in"), requests for any other host (such as www.)
    // are redirected there permanently. Leave empty locally.
    'canonical_host' => env('CMS_CANONICAL_HOST'),

    'page_cache' => [
        'enabled' => (bool) env('CMS_PAGE_CACHE', true),
        // Upper bound in seconds; schedules (announcements, popups, prices) shorten it.
        'max_ttl' => (int) env('CMS_PAGE_CACHE_TTL', 3600),
    ],

    'media' => [
        'disk' => 'public',
        'directory' => 'media',
        // Upload limits in megabytes. PHP's upload_max_filesize / post_max_size must allow them.
        'image_max_mb' => (int) env('CMS_IMAGE_MAX_MB', 15),
        'video_max_mb' => (int) env('CMS_VIDEO_MAX_MB', 60),
        'document_max_mb' => (int) env('CMS_DOCUMENT_MAX_MB', 15),
        // Responsive WebP widths generated for uploaded images.
        'image_widths' => [480, 800, 1200, 1600],
        // Largest stored dimension of the re-encoded original.
        'image_max_dimension' => 2400,
        // Refuse images larger than this many pixels (GD needs ~5 bytes per pixel of memory).
        'image_max_pixels' => (int) env('CMS_IMAGE_MAX_PIXELS', 40_000_000),
        'webp_quality' => 80,
        'types' => [
            'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'],
            'video' => ['video/mp4' => 'mp4', 'video/webm' => 'webm'],
            'document' => ['application/pdf' => 'pdf'],
        ],
    ],
];
