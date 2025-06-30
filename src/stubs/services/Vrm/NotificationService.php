<?php

namespace App\Services\Vrm;

use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Notification types
     */
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_WARNING = 'warning';
    const TYPE_INFO = 'info';

    /**
     * Notification styles
     */
    const STYLE_ALERT = 'alert';
    const STYLE_TOAST = 'toast';

    /**
     * Create a new notification
     * 
     * @param string $type Notification type (success, error, warning, info)
     * @param string|null $message Custom message
     * @param string $style The notification style (alert, toast)
     * @param string|null $key A unique key for the notification
     * @param string|null $component Target component name (for Livewire)
     * @return array The notification data
     */
    public static function create($type, $message = null, $style = self::STYLE_ALERT, $key = null, $component = null)
    {
        // Determine message if none provided
        if (empty($message)) {
            $message = self::getDefaultMessage($type);
        }

        // Create unique key if none provided
        if (empty($key)) {
            $key = 'notification_' . Str::random(10);
        }

        // Create notification data
        $notification = [
            'key' => $key,
            'type' => $type,
            'message' => $message,
            'style' => $style,
            'timestamp' => now()->timestamp,
        ];

        // Store in session
        session()->flash($key, $notification);

        // Handle component targeting for Livewire if needed
        if ($component) {
            self::dispatchToComponent($notification, $component);
        }

        return $notification;
    }

    /**
     * Create a success notification
     * 
     * @param string|null $message Custom message
     * @param string $style Notification style
     * @param string|null $key A unique key for the notification
     * @param string|null $component Target component name (for Livewire)
     * @return array
     */
    public static function success($message = null, $style = self::STYLE_ALERT, $key = null, $component = null)
    {
        return self::create(self::TYPE_SUCCESS, $message, $style, $key, $component);
    }

    /**
     * Create an error notification
     * 
     * @param string|null $message Custom message
     * @param string $style Notification style
     * @param string|null $key A unique key for the notification
     * @param string|null $component Target component name (for Livewire)
     * @return array
     */
    public static function error($message = null, $style = self::STYLE_ALERT, $key = null, $component = null)
    {
        return self::create(self::TYPE_ERROR, $message, $style, $key, $component);
    }

    /**
     * Create a warning notification
     * 
     * @param string|null $message Custom message
     * @param string $style Notification style
     * @param string|null $key A unique key for the notification
     * @param string|null $component Target component name (for Livewire)
     * @return array
     */
    public static function warning($message = null, $style = self::STYLE_ALERT, $key = null, $component = null)
    {
        return self::create(self::TYPE_WARNING, $message, $style, $key, $component);
    }

    /**
     * Create an info notification
     * 
     * @param string|null $message Custom message
     * @param string $style Notification style
     * @param string|null $key A unique key for the notification
     * @param string|null $component Target component name (for Livewire)
     * @return array
     */
    public static function info($message = null, $style = self::STYLE_ALERT, $key = null, $component = null)
    {
        return self::create(self::TYPE_INFO, $message, $style, $key, $component);
    }

    /**
     * Get a notification by key
     * 
     * @param string $key The notification key
     * @param bool $clear Whether to clear the notification after retrieving
     * @return array|null
     */
    public static function get($key, $clear = true)
    {
        $notification = session()->get($key);

        if ($clear && !is_null($notification)) {
            session()->forget($key);
        }

        return $notification;
    }

    /**
     * Render a notification as HTML
     * 
     * @param array $notification The notification data
     * @return string HTML output
     */
    public static function render($notification)
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

        // Render based on style
        if ($notification['style'] === self::STYLE_TOAST) {
            return self::renderToast($notification);
        }

        return self::renderAlert($notification);
    }

    /**
     * Render an alert notification with modern styling
     * 
     * @param array $notification The notification data
     * @return string HTML output
     */
    protected static function renderAlert($notification)
    {
        $type = $notification['type'];
        $message = $notification['message'];
        $title = self::getDefaultTitle($type);

        // Get styling classes based on notification type
        $styles = self::getAlertStyles($type);
        $titleHtml = !empty($title) ? "<strong>{$title}</strong> " : '';

        return "
            <div role='alert' class='relative flex items-start w-full border rounded-md p-2 {$styles['bg']} {$styles['border']} {$styles['text']}' data-notification-key='{$notification['key']}'>
                <div class='w-full text-sm font-sans leading-none m-1.5'>
                    {$titleHtml}{$message}
                </div>
            </div>
        ";
    }

    /**
     * Get modern styling classes for alert notifications
     * 
     * @param string $type The notification type
     * @return array Array of style classes
     */
    protected static function getAlertStyles($type)
    {
        switch ($type) {
            case self::TYPE_SUCCESS:
                return [
                    'bg' => 'bg-green-100',
                    'border' => 'border-green-100',
                    'text' => 'text-green-800'
                ];
            case self::TYPE_ERROR:
                return [
                    'bg' => 'bg-red-100',
                    'border' => 'border-red-100',
                    'text' => 'text-red-800'
                ];
            case self::TYPE_WARNING:
                return [
                    'bg' => 'bg-yellow-100',
                    'border' => 'border-yellow-100',
                    'text' => 'text-yellow-800'
                ];
            case self::TYPE_INFO:
            default:
                return [
                    'bg' => 'bg-blue-100',
                    'border' => 'border-blue-100',
                    'text' => 'text-blue-700'
                ];
        }
    }

    /**
     * Render a toast notification with modern styling
     * 
     * @param array $notification The notification data
     * @return string HTML output
     */
    protected static function renderToast($notification)
    {
        $type = $notification['type'];
        $message = $notification['message'];
        $title = self::getDefaultTitle($type);

        // Get styling classes based on notification type
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

    /**
     * Get the default title for a notification type
     * 
     * @param string $type The notification type
     * @return string
     */
    protected static function getDefaultTitle($type)
    {
        switch ($type) {
            case self::TYPE_SUCCESS:
                return 'Success!';
            case self::TYPE_ERROR:
                return 'Error!';
            case self::TYPE_WARNING:
                return 'Warning!';
            case self::TYPE_INFO:
                return 'Info!';
            default:
                return 'Notice!';
        }
    }

    /**
     * Get the default message for a notification type
     * 
     * @param string $type The notification type
     * @return string
     */
    protected static function getDefaultMessage($type)
    {
        switch ($type) {
            case self::TYPE_SUCCESS:
                return 'Operation was successful.';
            case self::TYPE_ERROR:
                return 'Something went wrong. Please try again.';
            case self::TYPE_WARNING:
                return 'This action cannot be undone.';
            case self::TYPE_INFO:
                return 'Please note this information.';
            default:
                return '';
        }
    }

    /**
     * Helper for regular Laravel controllers
     * 
     * @param string $type Notification type
     * @param string|null $message Custom message
     * @param string $style Notification style
     * @param string|null $component Target component name (for Livewire)
     * @return void
     */
    public static function flash($type, $message = null, $style = self::STYLE_ALERT, $component = null)
    {
        $notification = self::create($type, $message, $style, null, $component);
        session()->flash('notification', $notification['key']);
    }

    /**
     * Get the current notification from session (for controllers)
     * 
     * @return array|null
     */
    public static function current()
    {
        $key = session('notification');

        if (!$key) {
            return null;
        }

        return self::get($key);
    }

    /**
     * Dispatch a notification to a Livewire component
     * 
     * @param array $notification The notification data
     * @param string $component The target component or '*' for all
     * @return void
     */
    protected static function dispatchToComponent($notification, $component)
    {
        // Use Livewire's emit method if available
        if (class_exists('\Livewire\Livewire')) {
            \Livewire\Livewire::emit('notificationReceived', [
                'component' => $component,
                'notification' => $notification,
            ]);
        }
    }
}
