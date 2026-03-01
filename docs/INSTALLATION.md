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

Add the `HasVormiaRoles` trait and `is_active` to your User model:

```php
use Vormia\Vormia\Traits\HasVormiaRoles;

class User extends Authenticatable
{
    use HasVormiaRoles, HasApiTokens, ...;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

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
