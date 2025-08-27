<?php

namespace App\Http\Middleware\Vrm;

use Closure;
use Illuminate\Http\Request;
use App\Traits\Vrm\Model\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        // Check if user is authenticated via Sanctum
        if (!auth()->guard('sanctum')->check()) {
            return $this->error(
                "Unauthenticated.",
                401,
            );
        }

        // Set the authenticated user for the request
        auth()->guard('sanctum')->setUser(auth()->guard('sanctum')->user());

        return $next($request);
    }
}
