<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureMobileClient
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->user()->hasRole('staff') &&
            $request->header('X-Client-Type') !== 'mobile'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff can only checkout via mobile POS',
            ], 403);
        }

        return $next($request);
    }
}
