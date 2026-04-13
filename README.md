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
**MCP/AI Guide:** See [aiguide/CURSOR_CODEX_MCP_GUIDE.md](aiguide/CURSOR_CODEX_MCP_GUIDE.md) for Cursor IDE and Codex agent conventions.

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

## What's New in v5.2.0 🎉

### ✨ Highlights

- **MediaForge URL handling for local + S3/remote disks**: First-class helpers for “URL-or-path” outputs, preview URLs, and an optional proxy preview route.

### ✨ New Features

- **`MediaForge::url($urlOrPath, $disk = null)`**: Normalize a MediaForge `run()` return value into a usable URL when possible (works with Laravel disks and legacy webroot mode).
- **`MediaForge::previewUrl(...)`**: Generate previewable URLs, including signed temporary URLs when supported by the configured disk.
- **Preview proxy mode**: Set `VORMIA_MEDIAFORGE_PREVIEW_MODE=proxy` to enable a streaming preview endpoint at `GET /api/vrm/media/preview?disk=...&path=...`.
- **URL passthrough option**: `VORMIA_MEDIAFORGE_URL_PASSTHROUGH` controls whether `MediaForge::url()` returns existing `http(s)`/`data:` inputs unchanged.

### 🔧 Improvements

- **Remote disk friendliness**: Clearer patterns for displaying MediaForge outputs across local/public disks and private buckets.

### 🐛 Bug Fixes

- **Documentation correctness**: Updated examples and troubleshooting to reflect current MediaForge behavior (URL-or-path) and best practices.

### 📚 Documentation

- **Updated README + guides**: Added MediaForge URL helper docs (`MediaForge::url`) and proxy preview route guidance.

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
- ⚠️ **User model integration:** Add Vormia traits/methods to your `User` model (meta, slugs, roles/permissions). See “User Model Integration” below.

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

### User Model Integration (Recommended)

Vormia ships models/traits inside the package namespace (`Vormia\Vormia\...`). To enable user meta, slugs, and roles/permissions on your app’s `User` model, add the traits and methods below to `app/Models/User.php`.

#### 1) Add traits

```php
use Vormia\Vormia\Traits\Model\HasSlugs;
use Vormia\Vormia\Traits\Model\HasUserMeta;

class User extends Authenticatable
{
    use HasSlugs, HasUserMeta;

    // Keep HasApiTokens for API auth:
    // use Laravel\Sanctum\HasApiTokens;
}
```

#### 2) Add slug methods (if you want /users/{slug})

```php
public function getSluggableField()
{
    return 'name';
}

public function shouldAutoUpdateSlug()
{
    if (app()->environment('local', 'development')) {
        return false;
    }

    return config('vormia.auto_update_slugs', false);
}

public function getRouteKeyName()
{
    return 'slug';
}

public function resolveRouteBinding($value, $field = null)
{
    if ($field === 'slug' || $field === null) {
        return static::findBySlug($value);
    }

    return parent::resolveRouteBinding($value, $field);
}
```

#### 3) Add roles + permissions helpers (explicit methods)

```php
use Illuminate\Support\Collection;
use Vormia\Vormia\Models\Role;

public function roles()
{
    return $this->belongsToMany(
        Role::class,
        config('vormia.table_prefix') . 'role_user',
    );
}

public function hasRole(string $role): bool
{
    return $this->roles()->where('name', $role)->exists();
}

public function hasRoleId(int $roleId): bool
{
    return $this->roles()->where('id', $roleId)->exists();
}

public function isSuperAdmin(): bool
{
    return $this->roles()->where('slug', 'super-admin')->exists();
}

public function isAdminOrSuperAdmin(): bool
{
    return $this->roles()->whereIn('slug', ['super-admin', 'admin'])->exists();
}

public function isMember(): bool
{
    return $this->roles()->where('slug', 'member')->exists();
}

public function hasModule(string $module): bool
{
    $roles = $this->roles()->pluck('module')->toArray();
    $modules = array_map(fn ($module) => explode(',', $module), $roles);
    $modules = array_merge(...$modules);

    return in_array($module, $modules);
}

public function permissions(): Collection
{
    return $this->roles
        ->flatMap(fn ($role) => $role->permissions)
        ->unique('name')
        ->values();
}

public function hasPermission(string $permission): bool
{
    return $this->permissions()->contains('name', $permission);
}
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

#### MediaForge preview route (required for proxy mode)

If you set `VORMIA_MEDIAFORGE_PREVIEW_MODE=proxy`, MediaForge will generate preview URLs that rely on a proxy endpoint. Make sure this route exists in your app’s `routes/api.php`:

```php
use Illuminate\Support\Facades\Route;
use Vormia\Vormia\Http\Controllers\Api\MediaPreviewController;

Route::prefix('vrm')->group(function () {
    Route::get('/media/preview', [MediaPreviewController::class, 'show']);
});
```

With the `vrm` prefix (as shipped by Vormia), the final endpoint is:

- **`/api/vrm/media/preview`**

If this route is missing (or `VORMIA_MEDIAFORGE_PREVIEW_MODE` is not `proxy`), the controller will return `404`.

### MediaForge Image Processing

MediaForge provides comprehensive image processing with resize, conversion, and thumbnail generation:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

// Basic upload with resize and convert
$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    // Use YYYY/MM/DD folders instead of YYYY
    // ->useDateFolders(true)
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

### MediaForge File Upload (no processing)

If you want to upload **any file type** (PDFs, docs, zips, etc.) without image decoding/resizing/conversion, use `uploadFile()`:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$urlOrPath = MediaForge::uploadFile($request->file('document'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('documents')
    ->run();
```

You can also use the fluent flag on the normal upload pipeline:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$urlOrPath = MediaForge::upload($request->file('document'))
    ->isFile()
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('documents')
    ->run();
```

Note: `isFile()` disables all image operations (`resize()`, `convert()`, `thumbnail()`), even if the uploaded file is an image.

**File Naming:**

- Resize: `{baseName}-{width}-{height}.{extension}` (e.g., `abc123-606-606.jpg`)
- Resize + Convert: `{baseName}-{width}-{height}-{format}.{format}` (e.g., `abc123-606-606-webp.webp`)
- Thumbnails: `{baseName}_{suffix}.{extension}` (e.g., `abc123-606-606_thumb.webp`)

**Configuration:**

```env
VORMIA_MEDIAFORGE_STORAGE_RULE=laravel
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DISK=public
VORMIA_MEDIAFORGE_URL_PASSTHROUGH=false
VORMIA_MEDIAFORGE_BASE_DIR=uploads
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false
VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true
VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true
VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false
```

#### Display URL helper (`MediaForge::url`)

`MediaForge::upload(...)->run()` may return either a **URL** or a **storage path/key** (see “URL-or-path” behavior above).\nTo reliably turn either a path/key **or** a URL into something you can put in an `<img src=\"\">`, use:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$src = MediaForge::url($iconPathOrUrl);        // uses configured disk
$src = MediaForge::url($iconPathOrUrl, 'S3');  // optional disk override (case-insensitive)
```

Behavior:
- If `VORMIA_MEDIAFORGE_STORAGE_RULE=laravel`, it uses `Storage::disk($disk)->url($path)` and wraps relative results (like `/storage/...`) with `asset()` to make them absolute.
- If `VORMIA_MEDIAFORGE_STORAGE_RULE=vormia`, it returns `asset($path)` for paths/keys.
- If input is already a URL:
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=true` returns it unchanged
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=false` tries to extract S3-style keys and rebuild via the disk (otherwise returns the URL unchanged)

#### S3 / Remote Disks (Return Value + Failure Handling)

To store MediaForge outputs on S3, set:

```env
# Use Laravel filesystem disks (required for S3)
VORMIA_MEDIAFORGE_STORAGE_RULE=laravel

# The Laravel disk name to use (must exist in config/filesystems.php)
# Common choices: s3, spaces, r2, etc.
VORMIA_MEDIAFORGE_DISK=s3
```

`MediaForge::upload(...)->run()` returns a **string**:

- If the configured Laravel disk supports `url()`, Vormia will return a **URL string** (commonly something like `https://{bucket}.s3.../{key}` or your `AWS_URL` / CloudFront URL).
- If `url()` can’t be generated (or throws), Vormia will return the **storage path/key** (for example `uploads/products/2026/abc.webp`). This is the “URL-or-path” behavior.

To handle upload failures, wrap the call in a `try/catch`:

```php
use Illuminate\Support\Facades\Log;
use VormiaPHP\Vormia\Facades\MediaForge;

try {
    $urlOrPath = MediaForge::upload($request->file('image'))
        ->to('products')
        ->run();
} catch (\Throwable $e) {
    Log::error('MediaForge upload failed', ['error' => $e->getMessage()]);
    throw $e; // or return a user-friendly response
}
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
