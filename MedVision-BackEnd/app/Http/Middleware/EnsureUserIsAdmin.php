<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
   /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
       // Check if the authenticated user has the admin role
       if (Auth::check() && Auth::user()->role === 'admin') {
        return $next($request);
    }

    // If not an admin, return a 403 Forbidden response
    return response()->json(['message' => 'Forbidden - You are not authorized to access this resource'], 403);
}
}
