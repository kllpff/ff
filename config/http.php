<?php

return [
    // Maximum allowed request payload in bytes (supports shorthand like 2M). Null uses post_max_size.
    'request_size_limit' => env('APP_REQUEST_SIZE_LIMIT', null),
];
