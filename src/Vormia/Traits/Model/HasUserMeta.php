<?php

namespace Vormia\Vormia\Traits\Model;

use Vormia\Vormia\Models\UserMeta;

trait HasUserMeta
{
    public static function bootHasUserMeta(): void
    {
        static::created(function ($model) {
            $model->createDefaultUserMeta();
        });

        static::updated(function ($model) {
            $model->syncUserMeta();
        });
    }

    public function meta()
    {
        return $this->hasMany(UserMeta::class, 'user_id');
    }

    public function getMeta($key, $default = null)
    {
        return optional($this->meta->firstWhere('key', $key))->value ?? $default;
    }

    public function setMeta($key, $value, $is_active = 1)
    {
        return $this->meta()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'is_active' => $is_active]
        );
    }

    public function deleteMeta($key)
    {
        return $this->meta()->where('key', $key)->delete();
    }

    public function createDefaultUserMeta(): void
    {
        $defaultMeta = [
            'display_name' => $this->name,
            'full_name' => $this->name,
            'initials' => $this->generateInitials($this->name),
            'avatar_url' => null,
            'bio' => null,
            'location' => null,
            'website' => null,
            'social_links' => [],
            'preferences' => [
                'theme' => 'light',
                'language' => 'en',
                'timezone' => 'UTC',
                'notifications' => [
                    'email' => true,
                    'push' => true,
                    'sms' => false,
                ],
            ],
            'last_login_ip' => null,
            'login_count' => 0,
            'created_at_formatted' => $this->formatDateTime($this->created_at),
            'updated_at_formatted' => $this->formatDateTime($this->updated_at),
        ];

        foreach ($defaultMeta as $key => $value) {
            if ($value !== null || in_array($key, ['bio', 'location', 'website', 'avatar_url', 'last_login_ip'])) {
                $this->setMeta($key, $value);
            }
        }
    }

    public function syncUserMeta(): void
    {
        $syncFields = [
            'display_name' => $this->name,
            'full_name' => $this->name,
            'initials' => $this->generateInitials($this->name),
            'created_at_formatted' => $this->formatDateTime($this->created_at),
            'updated_at_formatted' => $this->formatDateTime($this->updated_at),
        ];

        foreach ($syncFields as $key => $value) {
            $this->setMeta($key, $value);
        }
    }

    protected function formatDateTime($dateTime): ?string
    {
        if ($dateTime === null) {
            return null;
        }

        if (is_object($dateTime) && method_exists($dateTime, 'format')) {
            return $dateTime->format('Y-m-d H:i:s');
        }

        if (is_string($dateTime)) {
            return $dateTime;
        }

        return null;
    }

    protected function generateInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return $initials;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->getMeta('display_name', $this->name);
    }

    public function getInitialsAttribute(): string
    {
        return $this->getMeta('initials', $this->generateInitials($this->name));
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getMeta('avatar_url');
    }

    public function setAvatarUrlAttribute($url): void
    {
        $this->setMeta('avatar_url', $url);
    }

    public function getPreferencesAttribute(): array
    {
        return $this->getMeta('preferences', []);
    }

    public function setPreferencesAttribute($preferences): void
    {
        $this->setMeta('preferences', $preferences);
    }

    public function getSocialLinksAttribute(): array
    {
        return $this->getMeta('social_links', []);
    }

    public function setSocialLinksAttribute($links): void
    {
        $this->setMeta('social_links', $links);
    }

    public function incrementLoginCount(): void
    {
        $currentCount = (int) $this->getMeta('login_count', 0);
        $this->setMeta('login_count', $currentCount + 1);
    }

    public function setLastLoginIp($ip): void
    {
        $this->setMeta('last_login_ip', $ip);
    }
}
