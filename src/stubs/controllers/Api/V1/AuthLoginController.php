<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\Vrm\Utility;
use Illuminate\Support\Str;

class AuthLoginController extends Controller
{
    /**
     * Handle user login with Sanctum, brute force protection, and domain check.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'remember_me' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        // Brute force protection (5 attempts per 1 minute per email+ip)
        $key = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Too many login attempts. Please try again later.'], 429);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($key, 60); // 1 minute lockout
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        // Check if user is active and verified
        if (!$user->is_active || !$user->email_verified_at) {
            return response()->json(['message' => 'Account is not active or email not verified.'], 403);
        }

        // Check sender domain
        $origin = $request->header('Origin') ?: $request->header('Referer');
        $allowedDomains = Utility::where('type', 'security')->where('key', 'allowed_domains')->value('value');
        $allowedDomains = $allowedDomains ? json_decode($allowedDomains, true) : [];
        if ($origin && $allowedDomains && is_array($allowedDomains)) {
            $domainAllowed = false;
            foreach ($allowedDomains as $domain) {
                if (Str::contains($origin, $domain)) {
                    $domainAllowed = true;
                    break;
                }
            }
            if (!$domainAllowed) {
                return response()->json(['message' => 'Requests from this domain are not allowed.'], 403);
            }
        }

        // Login successful, clear rate limiter
        RateLimiter::clear($key);
        $tokenName = 'auth_token';

        // Set expiration based on remember_me
        $expiresAt = null;
        if ($request->boolean('remember_me')) {
            $expiresAt = now()->addDays(30);
        }

        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Logout the user and revoke the current token.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $token = $user->currentAccessToken();
            if ($token) {
                // $token->delete();
                $user->tokens()->delete();
            }
        }
        return response()->json(['message' => 'Logged out successfully.'], 200);
    }
}
