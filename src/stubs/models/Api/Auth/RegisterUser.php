<?php

namespace App\Models\Api\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class RegisterUser extends Model
{
    // Todo: User Registration
    public static function user_registration(array $userInfo, int $roleId): int
    {

        try {
            // Take User Info and Register
            $_user = new User();
            $_user->name = $userInfo["name"];
            $_user->username = $userInfo["username"];
            $_user->email = $userInfo["email"];
            $_user->phone = $userInfo["phone"];
            $_user->password = Hash::make($userInfo["password"]);
            $_user->save();

            // Add Roles
            $_user->roles()->attach($roleId);

            return $_user->id;
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('User registration failed: ' . $e->getMessage());

            return false;
        }
    }
}
