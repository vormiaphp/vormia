<?php

namespace App\Traits\Vrm\Model;

trait HasUserMeta
{
    /**
     * Boot the trait.
     */
    public static function bootHasUserMeta()
    {
        static::created(function ($model) {
            $model->createDefaultUserMeta();
        });

        static::updated(function ($model) {
            $model->syncUserMeta();
        });
    }

    /**
     * Create default meta fields for new users.
     */
    public function createDefaultUserMeta()
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
                    'sms' => false
                ]
            ],
            'last_login_ip' => null,
            'login_count' => 0,
            'created_at_formatted' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at_formatted' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        foreach ($defaultMeta as $key => $value) {
            if ($value !== null || $key === 'bio' || $key === 'location' || $key === 'website' || $key === 'avatar_url' || $key === 'last_login_ip') {
                $this->setMeta($key, $value);
            }
        }
    }

    /**
     * Sync user data with meta fields.
     */
    public function syncUserMeta()
    {
        $syncFields = [
            'display_name' => $this->name,
            'full_name' => $this->name,
            'initials' => $this->generateInitials($this->name),
            'updated_at_formatted' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];

        foreach ($syncFields as $key => $value) {
            $this->setMeta($key, $value);
        }
    }

    /**
     * Generate initials from name.
     *
     * @param string $name
     * @return string
     */
    protected function generateInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return $initials;
    }

    /**
     * Get user display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->getMeta('display_name', $this->name);
    }

    /**
     * Get user initials.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        return $this->getMeta('initials', $this->generateInitials($this->name));
    }

    /**
     * Get user avatar URL.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        return $this->getMeta('avatar_url');
    }

    /**
     * Set user avatar URL.
     *
     * @param string $url
     * @return void
     */
    public function setAvatarUrlAttribute($url)
    {
        $this->setMeta('avatar_url', $url);
    }

    /**
     * Get user preferences.
     *
     * @return array
     */
    public function getPreferencesAttribute()
    {
        return $this->getMeta('preferences', []);
    }

    /**
     * Set user preferences.
     *
     * @param array $preferences
     * @return void
     */
    public function setPreferencesAttribute($preferences)
    {
        $this->setMeta('preferences', $preferences);
    }

    /**
     * Get social links.
     *
     * @return array
     */
    public function getSocialLinksAttribute()
    {
        return $this->getMeta('social_links', []);
    }

    /**
     * Set social links.
     *
     * @param array $links
     * @return void
     */
    public function setSocialLinksAttribute($links)
    {
        $this->setMeta('social_links', $links);
    }

    /**
     * Increment login count.
     *
     * @return void
     */
    public function incrementLoginCount()
    {
        $currentCount = (int) $this->getMeta('login_count', 0);
        $this->setMeta('login_count', $currentCount + 1);
    }

    /**
     * Set last login IP.
     *
     * @param string $ip
     * @return void
     */
    public function setLastLoginIp($ip)
    {
        $this->setMeta('last_login_ip', $ip);
    }
}
