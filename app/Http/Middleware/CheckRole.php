<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            if ($request->user()->role === 'guru') {
                return redirect()->route('guru.dashboard');
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
