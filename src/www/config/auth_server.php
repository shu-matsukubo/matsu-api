<?php

return [
    'issuer' => env('AUTH_SERVER_ISSUER', 'http://localhost:18081'),
    'audience' => env('AUTH_SERVER_AUDIENCE', 'matsu-api'),
    'jwks_url' => env('AUTH_SERVER_JWKS_URL', 'http://host.docker.internal:18081/.well-known/jwks.json'),
    'jwks_cache_seconds' => env('AUTH_SERVER_JWKS_CACHE_SECONDS', 3600),
    'cache_store' => env('AUTH_SERVER_CACHE_STORE', 'file'),
];
