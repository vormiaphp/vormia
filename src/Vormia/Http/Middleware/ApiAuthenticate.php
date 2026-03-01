<?php

namespace Vormia\Vormia\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Vormia\Vormia\Traits\Model\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (! auth()->guard('sanctum')->check()) {
            return $this->error('Unauthenticated.', 401);
        }

        auth()->guard('sanctum')->setUser(auth()->guard('sanctum')->user());

        return $next($request);
    }
}
