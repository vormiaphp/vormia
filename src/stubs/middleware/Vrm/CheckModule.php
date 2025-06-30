<?php

namespace App\Http\Middleware\Vrm;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Check if the user has the required role for the requested module
        if (!$request->user() || !$request->user()->hasModule($module)) {
            abort(403, 'Unauthorized: You do not have the required module access.');
        }

        return $next($request);
    }
}
