<?php

return [

    'paths' => ['api/*', 'login', 'logout', 'register', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',   // Client side
        'http://127.0.0.1:3000',   // Client side
        'http://localhost:5173',   // Admin panel
        'http://127.0.0.1:5173',   // Admin panel
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];