# Vormia - Laravel Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## Introduction

A comprehensive Laravel development package that streamlines media handling, notifications, and role management with a modular, maintainable approach.

VormiaPHP offers robust tools for handling media, managing notifications, and implementing essential features like user roles and permissions. The package is designed with a modular structure, separating concerns through dedicated namespaces for models, services, middleware, and traits.

## Features

- **File & Image Processing**
- **Notification System**
- **Role-Based Access Control**
- **Modular Architecture**
- **Livewire Integration**
- **Database Organization**

## Installation

Before installing Vormia, ensure you have Laravel installed. **Note:** Inertia is not yet supported.

### Step 1: Install Laravel

```sh
composer create-project laravel/laravel myproject
cd myproject
```

### OR Using Laravel Installer

```sh
laravel new myproject
cd myproject
```

### Step 2: Install Vormia

```sh
composer require vormiaphp/vormia
```

### Step 3: Run Vormia Installation

```sh
php artisan vormia:install
```

- All notification stubs will be copied to `app/Notifications`.
- All jobs in `stubs/jobs/Vrm` will be copied to `app/Jobs/Vrm`.

If you want API support, run:

```sh
php artisan vormia:install --api
```

- All notification stubs will be copied to `app/Notifications`.
- All jobs in `stubs/jobs/Vrm` will be copied to `app/Jobs/Vrm`.
- All jobs in `stubs/jobs/V1` will be copied to `app/Jobs/V1`.
- All API controllers in `stubs/controllers/Api` will be copied to `app/Http/Controllers/Api`.
- The API routes file in `stubs/routes/api.php` will be copied to `routes/api.php` (you may be prompted to overwrite).
- The Postman collection in `stubs/public/Vormia.postman_collection.json` will be copied to `public/Vormia.postman_collection.json`.
- **You must add the `HasApiTokens` trait to your `User` model (`app/Models/User.php`) for API authentication.**

Then, you must install Sanctum yourself:

```sh
php artisan install:api
```

This will install Laravel Sanctum and set up API authentication.

2. If you see a message like:

```
Some middleware aliases or providers could not be added automatically. Please add them manually to bootstrap/app.php:
Add these to your middleware aliases array:
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'role' => \App\Http\Middleware\Vrm\CheckRole::class,
            'module' => \App\Http\Middleware\Vrm\CheckModule::class,
            'permission' => \App\Http\Middleware\Vrm\CheckPermission::class,
        ]);
    })
```

then open `bootstrap/app.php` and add the above lines to the appropriate arrays.

```
Add these to your providers array bootstrap/providers.php:

    App\Providers\Vrm\NotificationServiceProvider::class,
    App\Providers\Vrm\TokenServiceProvider::class,
    App\Providers\Vrm\MediaForgeServiceProvider::class,
    App\Providers\Vrm\UtilitiesServiceProvider::class,
    App\Providers\Vrm\GlobalDataServiceProvider::class,
```

open `bootstrap/providers.php` and add the above lines to the appropriate arrays.

3. Configure your `.env` file as needed.

4. Run migrations:

```
php artisan migrate
```

🟢 **API-first Vormia version**
If you want to bootstrap your project with API support, use:

```sh
php artisan vormia:install --api
```

Then, install Sanctum manually:

```sh
php artisan install:api
```

## Uninstallation

1. Run the uninstall command:

```
php artisan vormia:uninstall
```
