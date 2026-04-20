<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user('api')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        if (! $request->user('api')->hasAnyRole($roles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: You do not have the required role',
            ], 403);
        }

        return $next($request);
    }
}
