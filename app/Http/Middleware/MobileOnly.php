<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request is from a mobile app (API endpoint)
        // Allow all requests to API endpoints
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Reject non-API requests
        return response()->json([
            'success' => false,
            'message' => 'This endpoint is only accessible from mobile apps',
        ], 403);
    }
}
