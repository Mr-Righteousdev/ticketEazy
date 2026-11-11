<?php

return [
    // Owner phrase: entered once on a phone at /uqr/setup to mark it as the owner. Empty = setup disabled.
    'secret' => env('UNIQUE_QR_SECRET'),
    'fallback_url' => env('UNIQUE_QR_FALLBACK_URL', 'https://www.youtube.com'),
    'cookie_name' => 'uqr_owner',
    // Chrome caps cookie lifetime at 400 days; re-run /uqr/setup yearly.
    'cookie_days' => 400,
];
