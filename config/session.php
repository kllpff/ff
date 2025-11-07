<?php

return [
    'lifetime' => (int)env('SESSION_LIFETIME', 120), // minutes
    'timeout' => (int)env('SESSION_TIMEOUT', 7200),   // seconds of inactivity
    'expire_on_close' => (bool)env('SESSION_EXPIRE_ON_CLOSE', false),
    'secure' => (bool)env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => env('SESSION_SAME_SITE', 'Lax'),
    'domain' => env('SESSION_DOMAIN', ''),
];
