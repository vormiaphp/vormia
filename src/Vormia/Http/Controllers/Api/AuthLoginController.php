<?php

namespace Vormia\Vormia\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Vormia\Vormia\Models\Utility;
use Vormia\Vormia\Support\Helpers;
use Vormia\Vormia\Traits\Model\ApiResponseTrait;

class AuthLoginController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError(
                $validator->errors()->toArray(),
                'Invalid input',
            );
        }

        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return $this->error(
                'Too many login attempts. Please try again later.',
                429,
            );
        }

        $userClass = Helpers::userModel();
        $user = $userClass::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60);

            return $this->error(
                'Invalid email or password.',
                401,
            );
        }

        if (! $user->is_active || ! $user->email_verified_at) {
            return $this->error(
                'Account is not active or email not verified.',
                403,
            );
        }

        $origin = $request->header('Origin') ?: $request->header('Referer');
        $allowedDomainsRecord = Utility::where('key', 'allowed_domains')->value('value');
        $allowedDomains = $allowedDomainsRecord ? (is_string($allowedDomainsRecord) ? json_decode($allowedDomainsRecord, true) : $allowedDomainsRecord) : [];
        if ($origin && $allowedDomains && is_array($allowedDomains)) {
            $domainAllowed = false;
            foreach ($allowedDomains as $domain) {
                if (Str::contains($origin, $domain)) {
                    $domainAllowed = true;
                    break;
                }
            }
            if (! $domainAllowed) {
                return $this->error(
                    'Requests from this domain are not allowed.',
                    403,
                );
            }
        }

        RateLimiter::clear($key);
        $expiresAt = $request->boolean('remember_me') ? now()->addDays(30) : null;
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;
        $userRoles = method_exists($user, 'roles') ? $user->roles()->get()->pluck('slug')->toArray() : [];
        $slug = method_exists($user, 'getSlug') ? $user->getSlug() : ($user->slug ?? null);

        return $this->success(
            [
                'user' => array_merge($user->only(['id', 'name', 'email']), ['slug' => $slug]),
                'access_token' => $token,
                'user_roles' => $userRoles,
                'token_type' => 'Bearer',
            ],
            'Login successful.',
            200,
        );
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
        }

        return $this->success([], 'Logged out successfully.', 200);
    }
}
