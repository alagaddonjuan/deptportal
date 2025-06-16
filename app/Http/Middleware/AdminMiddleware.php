<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Make sure Auth facade is imported
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a user is authenticated and if that user has the 'admin' role.
        // This assumes you have a hasRole() method in your User model.
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return $next($request); // User is admin, proceed with the request
        }

        // User is not an admin or not authenticated
        return response()->json(['message' => 'Access Denied: Administrator access required.'], 403); // 403 Forbidden
    }
}