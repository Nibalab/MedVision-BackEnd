<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsPatient
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'patient') {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}

