<?php

namespace App\Facades\Vrm;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array create(string $type, string|null $message = null, string $style = 'alert', string|null $key = null, string|null $component = null)
 * @method static array success(string|null $message = null, string $style = 'alert', string|null $key = null, string|null $component = null)
 * @method static array error(string|null $message = null, string $style = 'alert', string|null $key = null, string|null $component = null)
 * @method static array warning(string|null $message = null, string $style = 'alert', string|null $key = null, string|null $component = null)
 * @method static array info(string|null $message = null, string $style = 'alert', string|null $key = null, string|null $component = null)
 * @see \App\Services\Vrm\NotificationService
 */
class Notification extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'notification';
    }
}
