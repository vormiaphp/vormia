<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\Rules\Password;
use App\Http\Traits\ApiResponseTrait;
use App\Notifications\VerifyEmail;
use App\Notifications\WelcomeUser;
use App\Services\Vrm\TokenService;
use App\Jobs\V1\SendVerifyEmailJob;
use App\Jobs\V1\SendWelcomeUserJob;

class AuthRegisterController extends Controller
{
    use ApiResponseTrait;
    /**
     * Handle user registration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(4)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                    // ->symbols()
                ],
                'terms' => 'required|accepted',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator->errors()->toArray(), 'Validation errors');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // attach to role
            $user->roles()->attach(2); // 2 for members

            // attach to meta
            $user->setMeta('terms', $request->terms);

            // You can generate a token here if you're using Laravel Sanctum/Passport
            $token = $user->createToken('auth_token')->plainTextToken;

            // Generate verification token and send email
            $tokenService = app(TokenService::class);
            $verificationToken = $tokenService->generateToken($user->id, 'email_verification', 'register', null, 60 * 24); // 24 hours expiry
            $verificationUrl = url('/api/v1/verify-email?t=' . $verificationToken);
            dispatch(new SendVerifyEmailJob($user, $verificationUrl));

            // Return response
            return $this->success([
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'verification_url' => $verificationUrl,
                'verification_token' => $verificationToken,
            ], 'User registered successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to register user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle email verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            't' => 'required|string',
        ]);

        $tokenService = app(TokenService::class);
        $authToken = $tokenService->verifyToken($request->t, 'email_verification', 'register', null, true);

        if (!$authToken) {
            return $this->error('Invalid or expired verification token.', 400);
        }

        $user = \App\Models\User::find($authToken->user_id);
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->success([], 'Email already verified.');
        }

        $user->email_verified_at = now();
        $user->is_active = 1;
        $user->save();

        // Queue welcome email
        dispatch(new SendWelcomeUserJob($user));

        // return success response
        return $this->success([], 'Email verified successfully.');
    }

    /**
     * Resend the verification email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->error('User not found.', 404);
        }

        if ($user->email_verified_at) {
            return $this->success([], 'Email already verified.');
        }

        $tokenService = app(TokenService::class);
        $verificationToken = $tokenService->generateToken($user->id, 'email_verification', 'register', null, 60 * 24); // 24 hours expiry
        $verificationUrl = url('/api/v1/verify-email?token=' . $verificationToken);
        dispatch(new SendVerifyEmailJob($user, $verificationUrl));

        // return success response
        return $this->success([
            'verification_url' => $verificationUrl,
            'verification_token' => $verificationToken,
        ], 'Verification email resent.', 201);
    }
}
