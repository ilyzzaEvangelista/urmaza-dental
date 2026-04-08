<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\SanctumBearerToken;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional Bearer auth: when a valid Sanctum token is present, stores the user id on the request
 * so downstream code (e.g. audit logs) can attribute actions without requiring auth:sanctum on the route.
 */
class ResolveSanctumUserFromBearerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->attributes->has('sanctum_user_id')) {
            return $next($request);
        }

        $token = SanctumBearerToken::normalize($request->bearerToken());
        if ($token === '') {
            return $next($request);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $model = $accessToken?->tokenable;

        if ($model instanceof User) {
            $request->attributes->set('sanctum_user_id', $model->id);
        }

        return $next($request);
    }
}
