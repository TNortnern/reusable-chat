<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | The origins that are allowed to make cross-origin requests.
    | For widget embedding, we allow all origins by default ('*').
    | For production, you may want to restrict this to specific domains
    | or use the allowed_origins_patterns option.
    |
    */
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Patterns to match against allowed origins. Useful for allowing
    | subdomains or dynamic origins.
    |
    | Examples:
    | - 'https://*.example.com' - Allow all subdomains of example.com
    | - 'https://*.vercel.app' - Allow all Vercel preview deployments
    |
    */
    'allowed_origins_patterns' => explode(',', env('CORS_ALLOWED_PATTERNS', '')),

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Headers that are allowed to be sent with cross-origin requests.
    | We include common headers plus those needed for:
    | - API key authentication (X-API-Key)
    | - Session token authentication (Authorization)
    | - File uploads (Content-Type)
    |
    */
    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'Origin',
        'X-Requested-With',
        'X-API-Key',
        'X-CSRF-TOKEN',
        'X-Socket-Id',
        'Cache-Control',
        'X-File-Name',
        'X-File-Size',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Headers that should be exposed to the browser. This is useful for
    | rate limiting headers and other custom headers.
    |
    */
    'exposed_headers' => [
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
        'Retry-After',
    ],

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Indicates whether the request can include user credentials like cookies,
    | HTTP authentication, or client-side SSL certificates.
    |
    | Note: If allowed_origins contains '*', supports_credentials must be false.
    |
    */
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),

];
