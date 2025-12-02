<?php

namespace App\Http\Middleware\Vrm;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthority
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // Check if user has any role with 'main' authority
        $hasMainAuthority = $user->roles()
            ->where('authority', 'main')
            ->exists();

        // If user doesn't have 'main' authority, redirect to account page
        if (!$hasMainAuthority) {
            return redirect()->route('/');
        }

        return $next($request);
    }
}
