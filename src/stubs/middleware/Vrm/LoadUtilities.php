<?php

namespace App\Http\Middleware\Vrm;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LoadUtilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Load private utilities for global scope
        $globalUtilities = Cache::remember('vrm.global.utilities', 3600, function () {
            $utilities = \App\Models\Vrm\Utility::where('is_active', true)
                ->where('type', 'global')
                ->get();

            $globalUtilities = [];

            if ($utilities->isNotEmpty()) {
                foreach ($utilities as $utility) {
                    $globalUtilities[$utility->key] = $utility->value;
                }
            }

            return $globalUtilities;
        });

        View::share([
            'vrm_global_utilities' => $globalUtilities,
        ]);

        // Load private settings only for authenticated users
        if (Auth::check()) {
            $privateUtilities = Cache::remember('vrm.private.utilities', 3600, function () {
                $utilities = \App\Models\Vrm\Utility::where('is_active', true)
                    ->where('type', 'private')
                    ->get();

                $privateUtilities = [];

                if ($utilities->isNotEmpty()) {
                    foreach ($utilities as $utility) {
                        $privateUtilities[$utility->key] = $utility->value;
                    }
                }

                return $privateUtilities;
            });

            View::share([
                'vrm_private_utilities' => $privateUtilities,
            ]);
        }

        return $next($request);
    }
}
