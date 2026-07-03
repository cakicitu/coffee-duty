<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects API routes with a static token (X-Api-Token header or Bearer token).
 * Token is configured via API_TOKEN in .env. If no token is configured,
 * access is denied (fail closed).
 */
class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Logged-in web users (session) may use the API without a token
        if ($request->user()) {
            return $next($request);
        }

        $configured = config('app.api_token');

        if (! $configured) {
            return response()->json([
                'success' => false,
                'message' => 'API token not configured on server',
            ], 503);
        }

        $provided = $request->header('X-Api-Token') ?? $request->bearerToken();

        if (! $provided || ! hash_equals($configured, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
