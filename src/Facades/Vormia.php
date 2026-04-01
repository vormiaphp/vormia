<?php

namespace VormiaPHP\Vormia\Facades;

use Illuminate\Support\Facades\Facade;

class Vormia extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vormia';
    }
}

