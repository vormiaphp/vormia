<?php

namespace App\Facades\Vrm;

use Illuminate\Support\Facades\Facade;

class MediaForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mediaforge';
    }
}
