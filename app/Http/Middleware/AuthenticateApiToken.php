<?php

namespace App\Http\Middleware;

use App\Models\UserApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! is_string($plainToken) || $plainToken === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiToken = UserApiToken::with('user')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $apiToken || ! $apiToken->user || $apiToken->isExpired()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();

        Auth::setUser($apiToken->user);
        $request->setUserResolver(fn () => $apiToken->user);
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
