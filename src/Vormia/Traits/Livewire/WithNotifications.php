<?php

namespace Vormia\Vormia\Traits\Livewire;

use Livewire\Attributes\On;
use Vormia\Vormia\Services\NotificationService;

/**
 * Livewire 4 trait for flash notifications.
 * Use with Livewire components: use Vormia\Vormia\Traits\Livewire\WithNotifications;
 */
trait WithNotifications
{
    public ?array $notification = null;

    public string $notificationStyle = NotificationService::STYLE_ALERT;

    #[On('notificationReceived')]
    public function handleNotificationEvent($data): void
    {
        if (is_array($data) && (! isset($data['component']) || $data['component'] === '*' || $data['component'] === $this->getComponentClassName())) {
            $this->notification = $data['notification'] ?? $data;
        }
    }

    public function notify(string $type, ?string $message = null, ?string $component = null): void
    {
        if ($component === null) {
            $component = $this->getComponentClassName();
        }

        $notification = NotificationService::create($type, $message, $this->notificationStyle);

        $this->notification = $notification;

        if ($component !== $this->getComponentClassName()) {
            $this->dispatch('notificationReceived', [
                'component' => $component,
                'notification' => $notification,
            ]);
        }
    }

    public function notifySuccess(?string $message = null, ?string $component = null): void
    {
        $this->notify(NotificationService::TYPE_SUCCESS, $message, $component);
    }

    public function notifyError(?string $message = null, ?string $component = null): void
    {
        $this->notify(NotificationService::TYPE_ERROR, $message, $component);
    }

    public function notifyWarning(?string $message = null, ?string $component = null): void
    {
        $this->notify(NotificationService::TYPE_WARNING, $message, $component);
    }

    public function notifyInfo(?string $message = null, ?string $component = null): void
    {
        $this->notify(NotificationService::TYPE_INFO, $message, $component);
    }

    public function useNotificationStyle(string $style): static
    {
        $this->notificationStyle = $style;

        return $this;
    }

    public function useToasts(): static
    {
        return $this->useNotificationStyle(NotificationService::STYLE_TOAST);
    }

    public function useAlerts(): static
    {
        return $this->useNotificationStyle(NotificationService::STYLE_ALERT);
    }

    public function renderNotification(): string
    {
        if (empty($this->notification)) {
            return '';
        }

        return NotificationService::render($this->notification);
    }

    public function clearNotification(): void
    {
        $this->notification = null;
    }

    public function getComponentClassName(): string
    {
        $parts = explode('\\', get_class($this));

        return end($parts);
    }
}
