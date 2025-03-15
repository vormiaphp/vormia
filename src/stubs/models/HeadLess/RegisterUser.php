<?php

namespace App\Models\HeadLess;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class RegisterUser extends Model
{
    // Todo: User Registration
    public static function user_registration(array $userInfo, int $roleId): int
    {

        //dd($userInfo);

        // Take User Info and Register
        $_user = new \App\Models\User();
        $_user->name = $userInfo["name"];

        $_user->username = $userInfo["username"];

        $_user->email = $userInfo["email"];
        $_user->phone = $userInfo["phone"];

        $_user->password = Hash::make($userInfo["password"]);
        $_user->save();

        // Add Roles
        $_user->roles()->attach($roleId);

        return $_user->id;
    }
}
