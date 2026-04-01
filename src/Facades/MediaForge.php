<?php

namespace VormiaPHP\Vormia\Facades;

use Illuminate\Support\Facades\Facade;

class MediaForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vrm.mediaforge';
    }
}

