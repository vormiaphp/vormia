<?php

namespace App\Services;

use \Illuminate\Auth\Access\Tokens\PersonalAccessToken;
use \Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Models\UserToken;
use App\Models\User;

class TokenService
{

    /**
     * Todo:: Generate Token
     */
    public static function createToken(User $user, string $name, array $scopes, \DateTimeInterface $expiresAt)
    {

        // Create token
        $plainTextToken = $user->createToken($name, $scopes, $expiresAt)->plainTextToken;
        // Encrypt
        $encryptedToken = Crypt::encryptString($plainTextToken);
        // Save to database
        $userToken = UserToken::updateOrCreate(
            ['user' => $user->id, 'name' => $name],
            ['token' => $encryptedToken]
        );

        // Return
        return $encryptedToken;
    }

    /**
     * Todo:: Fetch User Token
     *
     */
    public static function fetchUserToken(int $userid, string $name)
    {
        // Fetch
        $userToken = UserToken::where('user', $userid)->where('name', $name)->first();
        // If found decrypt String
        return ($userToken) ? $userToken->token : null;
    }
}
