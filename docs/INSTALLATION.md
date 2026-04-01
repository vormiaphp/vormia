# Vormia Package Installation Guide

## Overview

Vormia provides user management, roles, permissions, taxonomies, slugs, and utilities. Models live in the package (`Vormia\Vormia\Models\*`) — no files are copied from vendor to `app/Models`.

## Installation

```bash
composer require vormiaphp/vormia
php artisan vormia:install
```

## Post-Install Steps

### 1. User Model Setup

Update your app `User` model (`app/Models/User.php`) to include Vormia integration (roles/permissions + meta). For Laravel 13, a typical `User.php` looks like this:

```php
<?php

namespace App\Models;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasVormiaRoles, HasApiTokens;

    // Laravel 13 attributes style (optional)
    // #[Fillable(['name', 'email', 'password', 'is_active'])]
    // #[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
```

**Imports you’ll typically need:**

```php
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Vormia\Vormia\Traits\HasVormiaRoles;
```

**Notes:**

- `HasVormiaRoles` includes user meta support (via `HasUserMeta`), so you’ll have: `meta()`, `getMeta()`, `setMeta()`, `deleteMeta()`.
- Add `is_active` to your user migration/fillable and cast it to boolean (as shown).
- If you want user slugs (`/users/{slug}`), also add `use Vormia\Vormia\Traits\Model\HasSlugs;` and implement the slug methods (see the package `README.md` “User Model Integration” section).

### 2. Assign Role on Registration

In `app/Actions/Fortify/CreateNewUser.php` (or your registration logic), attach a default role after creating the user:

```php
use Vormia\Vormia\Models\Role;

public function create(array $input): User
{
    $user = User::create([...]);

    $defaultRole = Role::where('name', 'user')->first();
    if ($defaultRole) {
        $user->roles()->attach($defaultRole);
    }

    return $user;
}
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Seed Roles (Optional)

```bash
php artisan db:seed --class=RoleSeeder
```

## Using Package Models

Reference models directly from the package:

```php
use Vormia\Vormia\Models\Role;
use Vormia\Vormia\Models\Permission;
use Vormia\Vormia\Models\Taxonomy;
use Vormia\Vormia\Models\UserMeta;
```

## Livewire 4

For Livewire components, use the `WithNotifications` trait:

```php
use Livewire\Component;
use Vormia\Vormia\Traits\Livewire\WithNotifications;

class MyComponent extends Component
{
    use WithNotifications;

    public function save()
    {
        $this->notifySuccess('Saved!');
    }
}
```

## API Routes

The package registers these API routes under `/api`:

- `POST /api/v1/login`
- `POST /api/v1/logout` (auth:sanctum)
- `GET /api/vrm/roles`, `POST /api/vrm/roles`, etc.
- `GET /api/vrm/permissions`, ...
- `POST /api/vrm/users/{id}/roles`, ...

Ensure Laravel Sanctum is installed for API auth: `php artisan install:api`

## MediaForge (Image Processing)

Vormia ships MediaForge as a package-first service + facade. You can call it directly via the facade alias.

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')
    ->convert('webp', 90, true, true)
    ->thumbnail([[500, 500, 'thumb']])
    ->run();
```

### MediaForge configuration

```env
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DISK=public
VORMIA_MEDIAFORGE_BASE_DIR=uploads
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false
VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true
VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true
VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false
```
