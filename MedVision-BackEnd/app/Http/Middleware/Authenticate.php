<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if ($jwt = $request->cookie('jwt')) {
            try {
                $request->headers->set('Authorization', 'Bearer ' . $jwt);
                if (!JWTAuth::parseToken()->check()) {
                    return response()->json(['message' => 'Token is invalid'], 401);
                }

            } catch (\Exception $e) {
                return response()->json(['message' => 'Token is invalid or expired'], 401);
            }
        }
        $this->authenticate($request, $guards);

        return $next($request);
    }
}
