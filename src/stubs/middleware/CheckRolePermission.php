<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $action): Response
    {
        // Get the logged-in user
        $user = Auth::user();

        // Check if the user has the required role for the requested action
        if ($user && $this->hasPermission($user, $action)) {
            return $next($request);
        }

        // ? If not logged in, redirect to login page
        if (!$user) {
            return redirect()->route('account-signin');
        }

        // ? If logged in but does not have the required role, redirect to not authorized page 401
        return response('Unauthorized.', 401);
        //return $next($request);
    }

    /**
     * Check if the user has the required role for the requested action.
     *
     * @param  \App\Models\User  $user
     * @param  string  $action
     * @return bool
     */
    public function hasPermission($user, $action)
    {
        // Eager load the roles relationship
        $user->load('roles');

        // Retrieve the user's assigned role from the role_user table
        $role = $user->roles->first();

        if ($role) {
            // Check if the role's module includes the requested action
            $modules = explode(',', $role->module);

            // ? Trim empty spaces in array $modules
            $modules = array_map('trim', $modules);
            return in_array($action, $modules);
        }

        return false;
    }
}
