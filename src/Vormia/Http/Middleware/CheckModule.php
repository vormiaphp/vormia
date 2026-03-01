<?php

namespace Vormia\Vormia\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $request->user() || ! $request->user()->hasModule($module)) {
            abort(403, 'Unauthorized: You do not have the required module access.');
        }

        return $next($request);
    }
}
