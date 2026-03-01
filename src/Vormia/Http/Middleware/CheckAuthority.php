<?php

namespace Vormia\Vormia\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthority
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $hasMainAuthority = $user->roles()
            ->where('authority', 'main')
            ->exists();

        if (! $hasMainAuthority) {
            return redirect()->route('/');
        }

        return $next($request);
    }
}
