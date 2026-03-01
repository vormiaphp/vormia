<?php

namespace Vormia\Vormia\Support;

class Helpers
{
    public static function userModel(): string
    {
        return config('vormia.user_model') ?? config('auth.providers.users.model');
    }
}
