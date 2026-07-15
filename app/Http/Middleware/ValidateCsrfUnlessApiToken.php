<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;

/**
 * Enforces CSRF for session-authenticated API calls while letting
 * clients with the valid static API token pass without one.
 */
class ValidateCsrfUnlessApiToken extends ValidateCsrfToken
{
    // Login must stay reachable for external clients that have no session yet
    protected $except = [
        'api/login',
    ];

    // Skips CSRF validation when the request carries the configured API token
    public function handle($request, Closure $next)
    {
        if ($this->hasValidApiToken($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    // Checks the X-Api-Token header or Bearer token against the configured token
    private function hasValidApiToken(Request $request): bool
    {
        $configuredToken = config('app.api_token');
        $providedToken = $request->header('X-Api-Token') ?? $request->bearerToken();

        return $configuredToken && $providedToken && hash_equals($configuredToken, $providedToken);
    }
}
