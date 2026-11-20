<?php

// Comma-separated proxy IPs/CIDRs, or "*" only behind a CDN/load balancer that
// overwrites X-Forwarded-* headers. Empty (default): no proxy is trusted.
return [
    'proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))) ?: null,
];
