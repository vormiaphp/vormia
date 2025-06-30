<?php

namespace App\Traits\Vrm\Livewire;

use App\Services\Vrm\NotificationService;

trait WithNotifications
{
    /**
     * The current notification data.
     *
     * @var array|null
     */
    public $notification = null;

    /**
     * The notification style (alert or toast).
     *
     * @var string
     */
    public $notificationStyle = NotificationService::STYLE_ALERT;

    /**
     * Initialize the notification system for this component.
     *
     * @return void
     */
    public function initializeWithNotifications()
    {
        // Add listeners for notification events
        $this->listeners = array_merge($this->listeners ?? [], [
            'notificationReceived' => 'handleNotificationEvent',
        ]);
    }

    /**
     * Handle incoming notification events.
     *
     * @param array $data
     * @return void
     */
    public function handleNotificationEvent($data)
    {
        // Only process notifications intended for this component or broadcast to all
        if (!isset($data['component']) || $data['component'] === '*' || $data['component'] === $this->getComponentClassName()) {
            $this->notification = $data['notification'];
        }
    }

    /**
     * Create and store a new notification.
     *
     * @param string $type
     * @param string|null $message
     * @param string|null $component Target component (or '*' for all components)
     * @return void
     */
    public function notify($type, $message = null, $component = null)
    {
        // Default to current component if none specified
        if (is_null($component)) {
            $component = $this->getComponentClassName();
        }

        // Create the notification
        $notification = NotificationService::create($type, $message, $this->notificationStyle);

        // Set the notification for this component
        $this->notification = $notification;

        // Emit to other components if needed
        if ($component !== $this->getComponentClassName()) {
            $this->dispatch('notificationReceived', [
                'component' => $component,
                'notification' => $notification,
            ]);
        }
    }

    /**
     * Create a success notification.
     *
     * @param string|null $message
     * @param string|null $component
     * @return void
     */
    public function notifySuccess($message = null, $component = null)
    {
        $this->notify(NotificationService::TYPE_SUCCESS, $message, $component);
    }

    /**
     * Create an error notification.
     *
     * @param string|null $message
     * @param string|null $component
     * @return void
     */
    public function notifyError($message = null, $component = null)
    {
        $this->notify(NotificationService::TYPE_ERROR, $message, $component);
    }

    /**
     * Create a warning notification.
     *
     * @param string|null $message
     * @param string|null $component
     * @return void
     */
    public function notifyWarning($message = null, $component = null)
    {
        $this->notify(NotificationService::TYPE_WARNING, $message, $component);
    }

    /**
     * Create an info notification.
     *
     * @param string|null $message
     * @param string|null $component
     * @return void
     */
    public function notifyInfo($message = null, $component = null)
    {
        $this->notify(NotificationService::TYPE_INFO, $message, $component);
    }

    /**
     * Set the notification style (alert or toast).
     *
     * @param string $style
     * @return $this
     */
    public function useNotificationStyle($style)
    {
        $this->notificationStyle = $style;
        return $this;
    }

    /**
     * Use toast style notifications.
     *
     * @return $this
     */
    public function useToasts()
    {
        return $this->useNotificationStyle(NotificationService::STYLE_TOAST);
    }

    /**
     * Use alert style notifications.
     *
     * @return $this
     */
    public function useAlerts()
    {
        return $this->useNotificationStyle(NotificationService::STYLE_ALERT);
    }

    /**
     * Render the current notification.
     *
     * @return string
     */
    public function renderNotification()
    {
        if (empty($this->notification)) {
            return '';
        }

        return NotificationService::render($this->notification);
    }

    /**
     * Clear the current notification.
     *
     * @return void
     */
    public function clearNotification()
    {
        $this->notification = null;
    }

    /**
     * Get the component class name.
     *
     * @return string
     */
    public function getComponentClassName()
    {
        // Get class name without namespace
        $parts = explode('\\', get_class($this));
        return end($parts);
    }
}
