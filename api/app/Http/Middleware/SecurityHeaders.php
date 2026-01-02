<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Security headers to apply to all responses.
     */
    protected array $headers = [
        // Prevent MIME type sniffing
        'X-Content-Type-Options' => 'nosniff',

        // Clickjacking protection - prevent embedding in frames
        // Use 'DENY' to prevent all framing, or 'SAMEORIGIN' to allow same-origin only
        'X-Frame-Options' => 'DENY',

        // Enable XSS filter in older browsers (modern browsers have this built-in)
        'X-XSS-Protection' => '1; mode=block',

        // Control referrer information sent with requests
        'Referrer-Policy' => 'strict-origin-when-cross-origin',

        // Prevent IE from executing downloads in site's context
        'X-Download-Options' => 'noopen',

        // Disable client-side caching for API responses by default
        // Individual routes can override this if needed
        'Cache-Control' => 'no-store, no-cache, must-revalidate, proxy-revalidate',
        'Pragma' => 'no-cache',

        // Indicate that the response is from an API
        'X-Content-Type' => 'application/json',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->headers as $key => $value) {
            // Don't override if already set
            if (!$response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        // Add Content-Security-Policy for API responses
        // This is minimal since we're an API, not serving HTML
        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
        }

        // Add Permissions-Policy (formerly Feature-Policy)
        // Disable sensitive browser features for API responses
        if (!$response->headers->has('Permissions-Policy')) {
            $response->headers->set('Permissions-Policy', implode(', ', [
                'accelerometer=()',
                'camera=()',
                'geolocation=()',
                'gyroscope=()',
                'magnetometer=()',
                'microphone=()',
                'payment=()',
                'usb=()',
            ]));
        }

        // For production, you might want to add HSTS
        // Uncomment if your API is served over HTTPS only:
        //
        // if (config('app.env') === 'production') {
        //     $response->headers->set(
        //         'Strict-Transport-Security',
        //         'max-age=31536000; includeSubDomains; preload'
        //     );
        // }

        return $response;
    }
}
