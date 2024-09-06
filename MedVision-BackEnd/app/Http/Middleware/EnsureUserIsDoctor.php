<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsDoctor
{
    public function handle($request, Closure $next)
    {
        dd(Auth::user());
        if (Auth::check() && Auth::user()->role === 'doctor') {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}

