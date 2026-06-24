<?php

declare(strict_types=1);

return [

    'paths' => ['api/*', 'api'],

    'allowed_methods' => ['*'],

    /*
    | Explicit allow-list. Pulled from env so prod can lock it down. Default
    | covers the Vite dev server on both common loopback hostnames — browsers
    | treat http://localhost:5173 and http://127.0.0.1:5173 as different
    | origins, so we list both.
    */
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string)env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')),
    ))),

    /*
    | Pattern allow-list (regex). Loose match for any local dev port so
    | running `vite --port 5174` etc. still works without env tweaks. Empty
    | in production unless CORS_ALLOWED_ORIGIN_PATTERNS is set.
    */
    'allowed_origins_patterns' => array_values(array_filter(array_map(
        'trim',
        explode(
            ',',
            (string)env(
                'CORS_ALLOWED_ORIGIN_PATTERNS',
                '#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#',
            ),
        ),
    ))),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
