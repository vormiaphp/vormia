# Vormia - Laravel Package

[![Packagist](https://img.shields.io/packagist/v/vormiaphp/vormia.svg)](https://packagist.org/packages/vormiaphp/vormia)
[![GitHub](https://img.shields.io/github/stars/vormiaphp/vormia.svg)](https://github.com/vormiaphp/vormia)

## AI Conversion Guides

The [`/aiguide`](aiguide/) folder contains `.mdc` AI-oriented conversion guides for migrating between React and various Laravel/Next.js stacks. These are automatically applied by Cursor and compatible AI assistants.

| Guide | Conversion |
|-------|-----------|
| [react-to-laravel-livewire-inline.mdc](aiguide/react-to-laravel-livewire-inline.mdc) | React → Laravel Blade + Livewire Volt (inline) |
| [react-to-inertia-react.mdc](aiguide/react-to-inertia-react.mdc) | React → Inertia.js + React |
| [react-to-inertia-vue.mdc](aiguide/react-to-inertia-vue.mdc) | React → Inertia.js + Vue 3 |
| [react-to-inertia-svelte.mdc](aiguide/react-to-inertia-svelte.mdc) | React → Inertia.js + Svelte 5 |
| [laravel-to-nextjs-tanstack.mdc](aiguide/laravel-to-nextjs-tanstack.mdc) | Laravel → Next.js (App Router) + TanStack Query |
| [laravel-to-react.mdc](aiguide/laravel-to-react.mdc) | Laravel → Standalone React SPA |
| [react-to-expo-react-native.mdc](aiguide/react-to-expo-react-native.mdc) | React → Expo React Native |
| [react-to-expo-react-native-gluestack.mdc](aiguide/react-to-expo-react-native-gluestack.mdc) | React → Expo React Native + GlueStack UI v2 |

See also: [references/react-laravel.mdc](references/react-laravel.mdc) for the original React → Blade/Livewire Volt (class-style) guide.

---

## Introduction

A comprehensive Laravel development package that streamlines media handling, notifications, and role management with a modular, maintainable approach.

**Installation:** See [docs/INSTALLATION.md](docs/INSTALLATION.md) for setup with Laravel 12 and Livewire 4.
**MCP/AI Guide:** See [CURSOR_CODEX_MCP_GUIDE.md](CURSOR_CODEX_MCP_GUIDE.md) for Cursor IDE and Codex agent conventions.

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

## What's New in v5.0.0 🎉

### ✨ Highlights

- **Laravel 12 Support** (as of March 1, 2026): Full compatibility with the latest Laravel 12 framework

### ✨ New Features

- **Automated Installation Process**: Complete automation of installation including dependencies and assets
- **NPM Package Management**: Automatic installation and removal of npm packages (jquery, flatpickr, select2, sweetalert2)
- **CSS/JS Asset Integration**: Automatic copying of CSS/JS plugin files and integration into app.css/app.js
- **Dependency Auto-Installation**: Automatic installation of intervention/image, Laravel Sanctum, and CORS configuration
- **Enhanced Uninstallation**: Comprehensive cleanup including npm packages and dependencies
- **MediaForge Enhancements**:
  - Background fill color support for resize operations (exact dimensions with colored background)
  - Advanced thumbnail controls (aspect ratio, source image selection, fill color)
  - Consistent file naming patterns for all processed images
  - Configurable thumbnail defaults via environment variables

### 🔧 Improvements

- **Streamlined Installation**: Removed `--api` flag - API support is now always included by default
- **Frontend Integration**: Seamless integration of Vormia CSS/JS assets into Laravel projects
- **Better Error Handling**: Graceful handling of missing npm or Composer dependencies during installation
- **MediaForge Reliability**:
  - Fixed resize operations to always save in correct directory
  - Fixed background fill implementation (image now visible with proper background)
  - Improved file path handling and naming consistency

### 🐛 Bug Fixes

- **Installation Consistency**: Fixed inconsistencies in installation process
- **Documentation Updates**: Updated all documentation to reflect new automated installation process
- **MediaForge Fixes**:
  - Fixed resize with `override=false` saving to wrong directory
  - Fixed background fill color hiding the image
  - Fixed file naming to return correct paths after operations

### 📚 Documentation

- **Updated README**: Comprehensive installation and uninstallation documentation
- **MCP/AI Guide**: Consolidated into `CURSOR_CODEX_MCP_GUIDE.md`
- **Troubleshooting**: Added new troubleshooting sections for CSS/JS assets and dependencies

[View Full Changelog](CHANGELOG.md) | [Previous Version](https://github.com/vormiaphp/vormia/releases/tag/v4.5.2)

## Key Improvements

### 🔄 Uniform Meta Methods

All models now use consistent method names for managing meta data:

- **`setMeta($key, $value, $is_active = 1)`** - Set or update meta values
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

**Note:** The `intervention/image` package will be automatically installed during the installation process. This package is required for MediaForge image processing functionality.

### Step 3: Run Vormia Installation

```sh
php artisan vormia:install
```

This will automatically install Vormia with all files and configurations, including API support:

**Automatically Installed:**

- ✅ All Vormia files and directories (models, services, middleware, traits are auto-loaded from the package)
- ✅ All notification stubs copied to `app/Notifications`
- ✅ All jobs in `stubs/jobs/Vrm` copied to `app/Jobs/Vrm`
- ✅ All jobs in `stubs/jobs/V1` copied to `app/Jobs/V1`
- ✅ All API controllers in `stubs/controllers/Api` copied to `app/Http/Controllers/Api`
- ✅ API routes file copied to `routes/api.php` (you may be prompted to overwrite)
- ✅ Postman collection copied to `public/Vormia.postman_collection.json`
- ✅ CSS and JS plugin files copied to `resources/css/plugins` and `resources/js/plugins`
- ✅ `app.css` and `app.js` updated with Vormia imports and initialization
- ✅ **intervention/image** package installed via Composer
- ✅ **Laravel Sanctum** installed via `php artisan install:api`
- ✅ **CORS configuration** published via `php artisan config:publish cors`
- ✅ **npm packages** installed: jquery, flatpickr, select2, sweetalert2

**Manual Steps Required:**

- ⚠️ **You must add the `HasApiTokens` trait to your `User` model (`app/Models/User.php`) for API authentication.**
- ⚠️ **Add the `Vormia\Vormia\Traits\HasVormiaRoles` trait and `is_active` field to your User model.**

Middleware (`role`, `permission`, `module`, `authority`, `api-auth`) and service providers are auto-registered by the package -- no manual `bootstrap/app.php` changes needed.

2. Configure your `.env` file as needed.

4. Run migrations:

```
php artisan migrate
```

🟢 **API Support Included**
Vormia installation includes API support by default. Sanctum and CORS configuration are automatically installed during the installation process.

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

### MediaForge Image Processing

MediaForge provides comprehensive image processing with resize, conversion, and thumbnail generation:

```php
use App\Facades\Vrm\MediaForge;

// Basic upload with resize and convert
$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Resize with background fill color
    ->convert('webp', 90, true, true)     // Convert to WebP with override
    ->run();

// Resize with background fill (exact dimensions with image centered)
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Creates 606x606 with #5a85b9 background
    ->run();

// Thumbnail generation with full control
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')
    ->thumbnail(
        [[500, 500, 'thumb'], [400, 267, 'featured'], [400, 300, 'product']],
        true,   // keepAspectRatio: maintain aspect ratio
        false,  // fromOriginal: use processed image (not original)
        '#ffffff' // fillColor: fill empty areas when aspect ratio maintained
    )
    ->run();

// Generate thumbnails from original image (before resize/convert)
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606)
    ->thumbnail([[500, 500, 'thumb']], true, true) // fromOriginal = true
    ->run();

// Exact thumbnail dimensions (no aspect ratio preservation)
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->thumbnail([[500, 500, 'thumb']], false) // keepAspectRatio = false
    ->run();
```

**File Naming:**

- Resize: `{baseName}-{width}-{height}.{extension}` (e.g., `abc123-606-606.jpg`)
- Resize + Convert: `{baseName}-{width}-{height}-{format}.{format}` (e.g., `abc123-606-606-webp.webp`)
- Thumbnails: `{baseName}_{suffix}.{extension}` (e.g., `abc123-606-606_thumb.webp`)

**Configuration:**

```env
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false
VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true
VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true
VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false
```

## Uninstallation

Run the uninstall command:

```sh
php artisan vormia:uninstall
```

**What gets removed automatically:**

- ✅ All Vormia files and directories
- ✅ Configuration files
- ✅ Environment variables
- ✅ CSS and JS plugin files (`resources/css/plugins`, `resources/js/plugins`, `resources/js/helpers`)
- ✅ npm packages (jquery, flatpickr, select2, sweetalert2)
- ✅ intervention/image package (via Composer)
- ✅ Database tables (unless `--keep-data` flag is used)
- ✅ Application caches cleared

**Manual cleanup required:**

- ⚠️ **Laravel Sanctum**: If you want to remove Sanctum, run: `composer remove laravel/sanctum`
- ⚠️ **CORS Config**: If you want to remove CORS config, delete: `config/cors.php`
- ⚠️ **app.css and app.js**: Remove Vormia imports and initialization code manually
- ⚠️ **Composer package**: Run `composer remove vormiaphp/vormia` to completely remove from composer.json

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
use Vormia\Vormia\Traits\Model\HasUserMeta;

class User extends Authenticatable
{
    use HasUserMeta;
}
```

##### **API Authentication Failing**

**Problem**: 401 errors on protected routes
**Solution**:

1. Sanctum is automatically installed during `php artisan vormia:install`
2. Add `HasApiTokens` trait to User model
3. The `api-auth` middleware is auto-registered by the package (`Vormia\Vormia\Http\Middleware\ApiAuthenticate`)

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
