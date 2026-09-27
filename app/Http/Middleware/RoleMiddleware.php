<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user's role matches the required role
        if (!Auth::user()->role || Auth::user()->role->role_name !== $role) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}