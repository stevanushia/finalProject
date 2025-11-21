<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsFirebaseAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is logged in (Firebase UID exists in session)
        if (!session('firebase_uid')) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        // 2. Check if the 'firebase_is_admin' session key is strictly TRUE
        if (session('firebase_is_admin') === true) {
             return $next($request); // Allowed
        }

        // 3. If not admin, redirect home
        return redirect('/')->with('error', 'Unauthorized: Admin access only.');
    }
}