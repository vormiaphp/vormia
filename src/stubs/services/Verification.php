<?php

namespace App\Services;

use Illuminate\Support\Str;

class Verification
{
    public function __construct()
    {
        //
    }

    /**
     * Todo: Generate account verification code.
     * ? Pass user Id
     * @param int $userId
     *
     * return string
     */
    public function generateVerificationCode(int $userId): string
    {
        // ? Token
        $token = Str::random(60);

        // ? Save in database, table `verifications`
        \App\Models\Vrm\ActivationToken::create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => now()->addMinutes(60),
        ]);

        // ? Save user id in session for 65 minutes
        session(['token_user' => $userId]);
        // Set the OTP to expire after 65 minutes
        session(['token_expiry' => now()->addMinutes(65)]);

        // ? Token
        return $token;
    }

    /**
     * Todo: Verify account verification code.
     * ? Pass token
     * @param string $token
     *
     */
    public function verifyVerificationCode(string $token)
    {
        // ? Get user id from session
        $userId = session('token_user');

        // ? If user id is not found
        if (!$userId) {
            return false;
        }

        // ? Check if token has expired
        if (session('token_expiry') < now()) {
            return false;
        }

        // ? Get token from database
        $verification = \App\Models\Vrm\ActivationToken::where('user_id', $userId)
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        // ? If token is not found
        if (!$verification) {
            return false;
        }

        // ? Delete token from database
        $verification->delete();

        // ? Remove user id from session
        session()->forget('token_user');
        session()->forget('token_expiry');

        return $userId;
    }
}
