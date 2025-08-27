# Vormia - Laravel Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## Introduction

A comprehensive Laravel development package that streamlines media handling, notifications, and role management with a modular, maintainable approach.

VormiaPHP offers robust tools for handling media, managing notifications, and implementing essential features like user roles and permissions. The package is designed with a modular structure, separating concerns through dedicated namespaces for models, services, middleware, and traits.

## Dependencies

### Required Dependencies

- **intervention/image**: Required for MediaForge image processing functionality
  - Used for image resizing, compression, format conversion, watermarking, and avatar generation
  - Install with: `composer require intervention/image`

The package will automatically check for required dependencies during installation and usage, and provide helpful error messages if they're missing.

## Features

- **File & Image Processing**
- **Notification System**
- **Role-Based Access Control**
- **Modular Architecture**
- **Livewire Integration**
- **Database Organization**
- **Uniform Meta Management**
- **Robust Database Handling**
- **API Authentication Middleware**

## Key Improvements

### 🔄 Uniform Meta Methods

All models now use consistent method names for managing meta data:

- **`setMeta($key, $value, $flag = 1)`** - Set or update meta values
- **`getMeta($key, $default = null)`** - Retrieve meta values
- **`deleteMeta($key)`** - Remove meta values

This eliminates confusion between different method names like `setMetaValue` vs `setMeta` across models.

### 🛡️ Database Dependency Protection

Service providers now gracefully handle scenarios where:

- Database doesn't exist yet
- Migrations haven't been run
- Running in console during migrations

This prevents errors when cloning a project before running `php artisan migrate`.

### 🔐 API Authentication Middleware

New `api-auth` middleware for Sanctum-based API authentication:

- Automatically checks user authentication via Sanctum
- Returns proper 401 responses for unauthenticated requests
- Seamlessly integrates with existing Vormia installation

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

**Note:** The MediaForge image processing functionality requires the `intervention/image` package. If you plan to use image processing features, install it with:

```sh
composer require intervention/image
```

The installation process will check for this dependency and provide helpful warnings if it's missing.

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
            'api-auth' => \App\Http\Middleware\Vrm\ApiAuthenticate::class,
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

## Usage

### Meta Data Management

All models with meta support now use uniform methods:

```php
// Set meta values
$user->setMeta('preferences', ['theme' => 'dark']);
$taxonomy->setMeta('description', 'Category description');

// Get meta values
$preferences = $user->getMeta('preferences', []);
$description = $taxonomy->getMeta('description', 'Default description');

// Delete meta values
$user->deleteMeta('preferences');
```

### API Authentication

Use the new `api-auth` middleware for protected API routes:

```php
// In routes/api.php
Route::middleware(['api-auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/update', [UserController::class, 'update']);
});
```

Or apply to individual routes:

```php
Route::get('/protected-endpoint', [Controller::class, 'method'])
    ->middleware('api-auth');
```

## Uninstallation

1. Run the uninstall command:

```
php artisan vormia:uninstall
```

### **7. Troubleshooting Section**

#### **Common Issues**

##### **Database Connection Issues**
**Problem**: Service providers throw database errors before migrations
**Solution**: The package automatically handles this. Ensure migrations are run:
```bash
php artisan migrate
```

##### **Meta Methods Not Working**
**Problem**: `setMeta()` or `getMeta()` methods not found
**Solution**: Ensure your models use the correct traits:
```php
use App\Traits\Vrm\Model\HasUserMeta;

class User extends Authenticatable
{
    use HasUserMeta;
}
```

##### **API Authentication Failing**
**Problem**: 401 errors on protected routes
**Solution**: 
1. Ensure Sanctum is installed: `php artisan install:api`
2. Add `HasApiTokens` trait to User model
3. Check middleware alias: `'api-auth' => \App\Http\Middleware\Vrm\ApiAuthenticate::class`

##### **Utilities Service Not Working**
**Problem**: `app('vrm.utilities')->type('public')->get('theme')` returns unexpected results

**Root Cause**: There's a **conceptual mismatch** between table design and service implementation:
- **Table `type` column**: Stores data types (string, integer, boolean, json)
- **Service `->type('public')` method**: Suggests filtering by category that doesn't exist

**Solutions**:
```php
// ✅ RECOMMENDED: Direct access with explicit type
$utilities = app('vrm.utilities');
$theme = $utilities->get('theme', 'default-theme', 'general');

// ✅ ALTERNATIVE: Get all utilities of a data type
$allUtilities = $utilities->getByType('string');

// ✅ CACHE CLEARING: When utilities aren't working
$utilities->clearCache('general');  // Clear specific type
$utilities->clearCache();           // Clear all cache
$utilities->fresh('theme', 'default', 'general'); // Force fresh data
```

**Debug Utilities**:
```php
// Check what's actually in your utilities table
$tableName = config('vormia.table_prefix', 'vrm_') . 'utilities';

// See all utilities
$allUtilities = DB::table($tableName)->get();
dd('All utilities:', $allUtilities);

// Check specific key
$themeUtility = DB::table($tableName)->where('key', 'theme')->first();
dd('Theme utility:', $themeUtility);
```
