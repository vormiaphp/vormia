<?php

namespace Vormia\Vormia\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Vormia\Vormia\Models\Utility;

class LoadUtilities
{
    public function handle(Request $request, Closure $next): Response
    {
        $globalUtilities = Cache::remember('vrm.global.utilities', 3600, function () {
            $utilities = Utility::where('is_active', true)
                ->where('is_public', true)
                ->get();

            $globalUtilities = [];

            if ($utilities->isNotEmpty()) {
                foreach ($utilities as $utility) {
                    $globalUtilities[$utility->key] = $utility->value;
                }
            }

            return $globalUtilities;
        });

        View::share(['vrm_global_utilities' => $globalUtilities]);

        if (Auth::check()) {
            $privateUtilities = Cache::remember('vrm.private.utilities', 3600, function () {
                $utilities = Utility::where('is_active', true)
                    ->where('is_public', false)
                    ->get();

                $privateUtilities = [];

                if ($utilities->isNotEmpty()) {
                    foreach ($utilities as $utility) {
                        $privateUtilities[$utility->key] = $utility->value;
                    }
                }

                return $privateUtilities;
            });

            View::share(['vrm_private_utilities' => $privateUtilities]);
        }

        return $next($request);
    }
}
