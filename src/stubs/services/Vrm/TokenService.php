<?php

namespace App\Services\Vrm;

use App\Models\Vrm\AuthToken;
use Illuminate\Support\Str;

class TokenService
{
    /**
     * Todo: Generate token
     *
     * @param int $userId
     * @param string $type
     * @param string $name
     * @param string|null $token
     * @param int $expiryMinutes
     * @return string
     */
    public function generateToken(int $userId, string $type, string $name = 'default', ?string $token = null, int $expiryMinutes = 60): string
    {
        $token = $token ?? Str::random(60);

        AuthToken::create([
            'user_id'    => $userId,
            'type'       => $type,
            'name'       => strtolower($name),
            'token'      => $token,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return $token;
    }

    /**
     * Todo: Verify token
     *
     * @param string $token
     * @param string $type
     * @param string $name
     * @param int|null $userId
     * @param bool $destroyOnUse
     * @return bool|AuthToken
     */
    public function verifyToken(string $token, string $type, string $name = 'default', ?int $userId = null, bool $destroyOnUse = true): bool|AuthToken
    {
        $query = AuthToken::where('token', $token)
            ->where('type', $type)
            ->where('name', strtolower($name))
            ->where('expires_at', '>', now());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $record = $query->first();

        if (!$record) {
            return false;
        }

        if ($destroyOnUse) {
            $record->delete();
        }

        return $record;
    }

    /**
     * Todo: Generate numeric OTP
     *
     * @param int $length
     * @param int $maxDigit
     * @return int
     */
    public function generateNumericOtp(int $length = 6, int $maxDigit = 9): int
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, $maxDigit);
        }

        return (int) $otp;
    }
}
