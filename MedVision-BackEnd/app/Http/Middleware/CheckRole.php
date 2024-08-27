<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        Log::info('CheckRole middleware triggered', ['role' => $role]);
        if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
