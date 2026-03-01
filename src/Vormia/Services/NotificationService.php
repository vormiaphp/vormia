<?php

namespace Vormia\Vormia\Services;

use Illuminate\Support\Str;

class NotificationService
{
    public const TYPE_SUCCESS = 'success';

    public const TYPE_ERROR = 'error';

    public const TYPE_WARNING = 'warning';

    public const TYPE_INFO = 'info';

    public const STYLE_ALERT = 'alert';

    public const STYLE_TOAST = 'toast';

    public static function create($type, $message = null, $style = self::STYLE_ALERT, $key = null, $component = null): array
    {
        if (empty($message)) {
            $message = self::getDefaultMessage($type);
        }

        if (empty($key)) {
            $key = 'notification_' . Str::random(10);
        }

        $notification = [
            'key' => $key,
            'type' => $type,
            'message' => $message,
            'style' => $style,
            'timestamp' => now()->timestamp,
        ];

        session()->flash($key, $notification);

        if ($component) {
            self::dispatchToComponent($notification, $component);
        }

        return $notification;
    }

    public static function success($message = null, $style = self::STYLE_ALERT, $key = null, $component = null): array
    {
        return self::create(self::TYPE_SUCCESS, $message, $style, $key, $component);
    }

    public static function error($message = null, $style = self::STYLE_ALERT, $key = null, $component = null): array
    {
        return self::create(self::TYPE_ERROR, $message, $style, $key, $component);
    }

    public static function warning($message = null, $style = self::STYLE_ALERT, $key = null, $component = null): array
    {
        return self::create(self::TYPE_WARNING, $message, $style, $key, $component);
    }

    public static function info($message = null, $style = self::STYLE_ALERT, $key = null, $component = null): array
    {
        return self::create(self::TYPE_INFO, $message, $style, $key, $component);
    }

    public static function get($key, $clear = true): ?array
    {
        $notification = session()->get($key);

        if ($clear && $notification !== null) {
            session()->forget($key);
        }

        return $notification;
    }

    public static function render($notification): string
    {
        if (empty($notification)) {
            return '';
        }

        if (is_string($notification)) {
            $key = $notification;
            $notification = self::get($key);

            if (empty($notification)) {
                return '';
            }
        }

        if ($notification['style'] === self::STYLE_TOAST) {
            return self::renderToast($notification);
        }

        return self::renderAlert($notification);
    }

    protected static function renderAlert($notification): string
    {
        $type = $notification['type'];
        $message = $notification['message'];
        $title = self::getDefaultTitle($type);
        $styles = self::getAlertStyles($type);
        $titleHtml = ! empty($title) ? "<strong>{$title}</strong> " : '';

        return "
            <div role='alert' class='relative flex items-start w-full border rounded-md p-2 {$styles['bg']} {$styles['border']} {$styles['text']}' data-notification-key='{$notification['key']}'>
                <div class='w-full text-sm font-sans leading-none m-1.5'>
                    {$titleHtml}{$message}
                </div>
            </div>
        ";
    }

    protected static function getAlertStyles(string $type): array
    {
        return match ($type) {
            self::TYPE_SUCCESS => [
                'bg' => 'bg-green-100',
                'border' => 'border-green-100',
                'text' => 'text-green-800',
            ],
            self::TYPE_ERROR => [
                'bg' => 'bg-red-100',
                'border' => 'border-red-100',
                'text' => 'text-red-800',
            ],
            self::TYPE_WARNING => [
                'bg' => 'bg-yellow-100',
                'border' => 'border-yellow-100',
                'text' => 'text-yellow-800',
            ],
            self::TYPE_INFO,
            default => [
                'bg' => 'bg-blue-100',
                'border' => 'border-blue-100',
                'text' => 'text-blue-700',
            ],
        };
    }

    protected static function renderToast($notification): string
    {
        $type = $notification['type'];
        $message = $notification['message'];
        $title = self::getDefaultTitle($type);
        $styles = self::getAlertStyles($type);

        return "
            <div class='toast relative rounded-md overflow-hidden' role='alert' aria-live='assertive' aria-atomic='true' data-notification-key='{$notification['key']}'>
                <div class='toast-header flex items-center justify-between p-2 {$styles['bg']} {$styles['text']}'>
                    <strong class='mr-auto'>{$title}</strong>
                    <button type='button' class='ml-2 mb-1 text-{$styles['text']} opacity-80 hover:opacity-100' data-dismiss='toast' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>
                <div class='toast-body p-3 bg-white border-t-0 {$styles['border']}'>
                    {$message}
                </div>
            </div>
        ";
    }

    protected static function getDefaultTitle(string $type): string
    {
        return match ($type) {
            self::TYPE_SUCCESS => 'Success!',
            self::TYPE_ERROR => 'Error!',
            self::TYPE_WARNING => 'Warning!',
            self::TYPE_INFO => 'Info!',
            default => 'Notice!',
        };
    }

    protected static function getDefaultMessage(string $type): string
    {
        return match ($type) {
            self::TYPE_SUCCESS => 'Operation was successful.',
            self::TYPE_ERROR => 'Something went wrong. Please try again.',
            self::TYPE_WARNING => 'This action cannot be undone.',
            self::TYPE_INFO => 'Please note this information.',
            default => '',
        };
    }

    public static function flash($type, $message = null, $style = self::STYLE_ALERT, $component = null): void
    {
        $notification = self::create($type, $message, $style, null, $component);
        session()->flash('notification', $notification['key']);
    }

    public static function current(): ?array
    {
        $key = session('notification');

        if (! $key) {
            return null;
        }

        return self::get($key);
    }

    protected static function dispatchToComponent(array $notification, string $component): void
    {
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\Livewire::dispatch('notificationReceived', [
                'component' => $component,
                'notification' => $notification,
            ]);
        }
    }
}
