<?php

namespace Vormia\Vormia\Services;

use Illuminate\Support\Str;
use Vormia\Vormia\Models\AuthToken;

class TokenService
{
    public function generateToken(int $userId, string $type, string $name = 'default', ?string $token = null, int $expiryMinutes = 60): string
    {
        $token = $token ?? Str::random(60);

        AuthToken::create([
            'user_id' => $userId,
            'type' => $type,
            'name' => strtolower($name),
            'token' => $token,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return $token;
    }

    public function verifyToken(string $token, string $type, string $name = 'default', ?int $userId = null, bool $destroyOnUse = true): AuthToken|bool
    {
        $query = AuthToken::where('token', $token)
            ->where('type', $type)
            ->where('name', strtolower($name))
            ->where('expires_at', '>', now());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $record = $query->first();

        if (! $record) {
            return false;
        }

        if ($destroyOnUse) {
            $record->delete();
        }

        return $record;
    }

    public function generateNumericOtp(int $length = 6, int $maxDigit = 9): int
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= rand(0, $maxDigit);
        }

        return (int) $otp;
    }
}
