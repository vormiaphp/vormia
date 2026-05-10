# Vormia - Laravel Package

[Packagist](https://packagist.org/packages/vormiaphp/vormia)
[GitHub](https://github.com/vormiaphp/vormia)

## AI Conversion Guides

The `[/aiguide](aiguide/)` folder contains `.mdc` AI-oriented conversion guides (and companion `.md` references) for migrating between React and various Laravel/Next.js stacks. Guides are grouped by topic: `[aiguide/inertia/](aiguide/inertia/)` (Inertia.js v3), `[aiguide/livewire/](aiguide/livewire/)` (Livewire Volt), and `[aiguide/beta/](aiguide/beta/)` (experimental tracks). See `[aiguide/README.md](aiguide/README.md)` for the full index. `.mdc` files with `alwaysApply` are picked up by Cursor and compatible AI assistants.


| Guide                                                                                             | Conversion                                                                         |
| ------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| [reactjs-from-scratch.md](aiguide/reactjs-from-scratch.md)                                        | **From scratch:** learn React (Vite, hooks, steps to routing)                      |
| [vuejs-from-scratch.md](aiguide/vuejs-from-scratch.md)                                            | **From scratch:** learn Vue 3 (create-vue, Composition API, steps to router/Pinia) |
| [svelte-from-scratch.md](aiguide/svelte-from-scratch.md)                                          | **From scratch:** learn Svelte 5 (runes, Vite, when to add SvelteKit)              |
| [inertiajs-operations.md](aiguide/inertia/inertiajs-operations.md)                                | Inertia.js v3 — adapter-agnostic operations (Laravel, visits, Blade, middleware)   |
| [react-to-laravel-livewire-inline.mdc](aiguide/livewire/react-to-laravel-livewire-inline.mdc)     | React → Laravel Blade + Livewire Volt (inline)                                     |
| [react-to-inertia-react.mdc](aiguide/inertia/react-to-inertia-react.mdc)                          | React → Inertia.js + React                                                         |
| [react-to-inertia-vue.mdc](aiguide/inertia/react-to-inertia-vue.mdc)                              | React → Inertia.js + Vue 3                                                         |
| [react-to-inertia-svelte.mdc](aiguide/inertia/react-to-inertia-svelte.mdc)                        | React → Inertia.js + Svelte 5                                                      |
| [laravel-to-nextjs-tanstack.mdc](aiguide/beta/laravel-to-nextjs-tanstack.mdc)                     | Laravel → Next.js (App Router) + TanStack Query                                    |
| [laravel-to-react.mdc](aiguide/beta/laravel-to-react.mdc)                                         | Laravel → Standalone React SPA                                                     |
| [react-to-expo-react-native.mdc](aiguide/beta/react-to-expo-react-native.mdc)                     | React → Expo React Native                                                          |
| [react-to-expo-react-native-gluestack.mdc](aiguide/beta/react-to-expo-react-native-gluestack.mdc) | React → Expo React Native + GlueStack UI v2                                        |


See also: [react-to-laravel-livewire-inline.mdc](aiguide/livewire/react-to-laravel-livewire-inline.mdc) for React → Laravel Blade + Livewire Volt (inline components).

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

## What's New in v5.5.3 🎉

### ✨ Highlights

- **AI guides reorganized by stack**: Inertia, Livewire, and beta conversion tracks now live under clear subfolders; README links and `[aiguide/README.md](aiguide/README.md)` point at the current paths.
- **Inertia.js v3 documentation**: New adapter-agnostic guide `[aiguide/inertia/inertiajs-operations.md](aiguide/inertia/inertiajs-operations.md)` alongside the React/Vue/Svelte conversion `.mdc` files under `[aiguide/inertia/](aiguide/inertia/)`.
- **Installer documentation**: Livewire vs Inertia install paths (`php artisan vormia:install --stack=livewire|inertia`) are documented in README and aligned with `[aiguide/CURSOR_CODEX_MCP_GUIDE.md](aiguide/CURSOR_CODEX_MCP_GUIDE.md)`.

### 📚 Documentation

- **README**: AI Conversion Guides table and “See also” links updated for the new `aiguide/inertia/`, `aiguide/livewire/`, and `aiguide/beta/` layout.

[View Full Changelog](CHANGELOG.md) | [Previous Version](https://github.com/vormiaphp/vormia/releases/tag/v5.4.0)

## Key Improvements

### 🔄 Uniform Meta Methods

All models now use consistent method names for managing meta data:

- `**setMeta($key, $value, $is_active = 1)`** - Set or update meta values
- `**getMeta($key, $default = null)**` - Retrieve meta values
- `**deleteMeta($key)**` - Remove meta values

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

Vormia is installed into an existing Laravel application.

### Install the package

```sh
composer require vormiaphp/vormia
```

**Note:** The `intervention/image` package will be automatically installed during the installation process. This package is required for MediaForge image processing functionality.

### Run the installer

```sh
php artisan vormia:install
```

Then use **arrow keys** to highlight **Install Livewire Vormia Version** or **Install Inertiajs Vormia Version** and press **Enter** (Laravel Prompts; no numeric input).

For non-interactive environments (CI, Docker), use `--no-interaction` and optionally `--stack=livewire` or `--stack=inertia`. If you omit `--stack`, the installer defaults to **Livewire**. For example:

```sh
php artisan vormia:install --no-interaction --stack=inertia
```

This installs Vormia with all files and configurations, including API support. Stack-specific steps are summarized in [Livewire vs Inertia](#livewire-vs-inertia) and [Plugin stylesheet source](#plugin-stylesheet-source-devplugins) below.

### Livewire vs Inertia

The interactive prompt lists two options; move the cursor with the arrow keys and confirm with Enter.

- **Install Livewire Vormia Version** — Copies `[dev/plugins/livewire](dev/plugins/livewire)` into `resources/css/plugins/livewire/` (SCSS sources, compiled `style.min.css`, `incl/`, `select2-dark.css`). `resources/css/app.css` gets Flux plus `@import` for `**livewire/style.min.css`** and `**livewire/select2-dark.css**` only (not `.scss`). Vormia bootstrap in `resources/js/app.js` and npm packages (jQuery, Select2, Flatpickr, SweetAlert2).
- **Install Inertiajs Vormia Version** — Copies `[dev/plugins/style.scss](dev/plugins/style.scss)`, `[style.min.css](dev/plugins/style.min.css)`, and `[dev/plugins/incl/](dev/plugins/incl)` into `resources/css/plugins/` (SCSS is for your build pipeline; `app.css` only `**@import './plugins/style.min.css';`**). No Flux. No `resources/js/app.js` changes; npm install is skipped.


|                         | **Livewire**                                                                 | **Inertia.js**                                                 |
| ----------------------- | ---------------------------------------------------------------------------- | -------------------------------------------------------------- |
| Copied CSS              | `resources/css/plugins/livewire/`**                                          | `resources/css/plugins/style.scss`, `style.min.css`, `incl/**` |
| `resources/css/app.css` | Flux + `./plugins/livewire/style.min.css` + `select2-dark.css`               | `./plugins/style.min.css` only                                 |
| `resources/js/app.js`   | Vormia plugin init (jQuery, Select2, Flatpickr, Livewire hooks, SweetAlert2) | Unchanged                                                      |
| npm packages            | Installed by the installer                                                   | Skipped                                                        |


**Compiled CSS only:** `app.css` does not `@import` Vormia `.scss` files; it imports the shipped `**style.min.css`** (built from `style.scss` in the package repo). Use `sass` only if you choose to `@import` or compile those `.scss` files yourself.

### Plugin stylesheet source (`dev/plugins`)

Maintain two stub trees under `[src/stubs/pkg/css/plugins/](src/stubs/pkg/css/plugins/)` (mirrors dev) before tagging releases:

- **Inertia path:** `[dev/plugins/style.scss](dev/plugins/style.scss)`, `[dev/plugins/style.min.css](dev/plugins/style.min.css)`, `[dev/plugins/incl/](dev/plugins/incl)` — copied to the host as `resources/css/plugins/…` on Inertia installs.
- **Livewire path:** `[dev/plugins/livewire/](dev/plugins/livewire)` — copied to `resources/css/plugins/livewire/…` on Livewire installs.

The package dev sample `[dev/resources/css/app.css](dev/resources/css/app.css)` is unrelated to what the installer writes; the **consuming** app’s `resources/css/app.css` receives only `**@import` of compiled `.min.css`** (plus Flux and `select2-dark.css` on Livewire). Rebuild `style.min.css` from `style.scss` in the package repo before release when you change SCSS.

**Automatically Installed (all stacks):**

- ✅ All Vormia files and directories (models, services, middleware, traits are auto-loaded from the package)
- ✅ All notification stubs copied to `app/Notifications`
- ✅ All jobs in `stubs/jobs/Vrm` copied to `app/Jobs/Vrm`
- ✅ All jobs in `stubs/jobs/V1` copied to `app/Jobs/V1`
- ✅ All API controllers in `stubs/controllers/Api` copied to `app/Http/Controllers/Api`
- ✅ API routes file copied to `routes/api.php` (you may be prompted to overwrite)
- ✅ Postman collection copied to `public/Vormia.postman_collection.json`
- ✅ CSS and JS plugin files: stack-specific plugin tree under `resources/css/plugins` (see table above) plus `resources/js/plugins`
- ✅ `resources/css/app.css` updated per stack (Flux + livewire min/dark CSS, or inertia `style.min.css` only)
- ✅ **Livewire stack only:** `resources/js/app.js` updated with Vormia imports and initialization; **npm packages** installed (jquery, flatpickr, select2, sweetalert2)
- ✅ **intervention/image** package installed via Composer
- ✅ **Laravel Sanctum** installed via `php artisan install:api`
- ✅ **CORS configuration** published via `php artisan config:publish cors`

**Manual Steps Required:**

- ⚠️ **You must add the `HasApiTokens` trait to your `User` model (`app/Models/User.php`) for API authentication.**
- ⚠️ **User model integration:** Add Vormia traits/methods to your `User` model (meta, slugs, roles/permissions). See “User Model Integration” below.

Middleware (`role`, `permission`, `module`, `authority`, `api-auth`) and service providers are auto-registered by the package -- no manual `bootstrap/app.php` changes needed.

1. Configure your `.env` file as needed.
2. Run migrations:

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

#### 4) Attach a role to a user

After you create a user, assign a role on the `role_user` pivot using the `roles()` relationship from step 3. Pass the role’s **primary key** (the `id` column on your roles table):

```php
$_user = User::create([
    'name' => $input['name'],
    'email' => $input['email'],
    'password' => $input['password'],
]);

// Assign the role to the user
$_user->roles()->attach(1);
```

For several roles at once, use `$_user->roles()->attach([1, 2]);`. To replace the user’s roles with an exact set, use `sync([...])` instead of `attach`.

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

- `**/api/vrm/media/preview**`

If this route is missing (or `VORMIA_MEDIAFORGE_PREVIEW_MODE` is not `proxy`), the controller will return `404`.

### MediaForge Image Processing

MediaForge provides comprehensive image processing with resize, conversion, and thumbnail generation:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

// Basic upload with resize and convert
$path = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    // Use YYYY/MM/DD folders instead of YYYY
    // ->useDateFolders(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Resize with background fill color
    ->convert('webp', 90, true, true)     // Convert to WebP with override
    ->run();

// Resize with background fill (exact dimensions with image centered)
$path = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Creates 606x606 with #5a85b9 background
    ->run();

// Thumbnail generation with full control
$path = MediaForge::upload($file)
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
$path = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606)
    ->thumbnail([[500, 500, 'thumb']], true, true) // fromOriginal = true
    ->run();

// Exact thumbnail dimensions (no aspect ratio preservation)
$path = MediaForge::upload($file)
    ->to('products')
    ->thumbnail([[500, 500, 'thumb']], false) // keepAspectRatio = false
    ->run();
```

### MediaForge File Upload (no processing)

If you want to upload **any file type** (PDFs, docs, zips, etc.) without image decoding/resizing/conversion, use `uploadFile()`:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$path = MediaForge::uploadFile($request->file('document'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('documents')
    ->run();
```

You can also use the fluent flag on the normal upload pipeline:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

$path = MediaForge::upload($request->file('document'))
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

`MediaForge::upload(...)->run()` returns a **storage path/key** (example: `uploads/products/2026/04/17/abc.webp`).\nTo turn that (or any prior “URL-or-path” value) into something you can put in an `<img src=\"\">`, use:

```php
use VormiaPHP\Vormia\Facades\MediaForge;

// Public buckets / public CDN URLs:
$src = MediaForge::url($pathOrUrl)->public();        // uses configured disk
$src = MediaForge::url($pathOrUrl, 's3')->public();  // optional disk override (case-insensitive)

// Private buckets (signed / expiring URL):
$src = MediaForge::url($pathOrUrl)->private()->hours(1);
```

Behavior:

- If `VORMIA_MEDIAFORGE_STORAGE_RULE=laravel`, it uses `Storage::disk($disk)->url($path)` and wraps relative results (like `/storage/...`) with `asset()` to make them absolute.
- If `VORMIA_MEDIAFORGE_STORAGE_RULE=vormia`, it returns `asset($path)` for paths/keys.
- If input is already a URL:
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=true` returns it unchanged
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=false` tries to extract S3-style keys and rebuild via the disk (otherwise returns the URL unchanged)
- If `private()` is used, it prefers `Storage::disk($disk)->temporaryUrl($path, $expiresAt)` when supported.

#### S3 / Remote Disks (Return Value + Failure Handling)

To store MediaForge outputs on S3, set:

```env
# Use Laravel filesystem disks (required for S3)
VORMIA_MEDIAFORGE_STORAGE_RULE=laravel

# The Laravel disk name to use (must exist in config/filesystems.php)
# Common choices: s3, spaces, r2, etc.
VORMIA_MEDIAFORGE_DISK=s3
```

`MediaForge::upload(...)->run()` returns a **string path/key** (for example `uploads/products/2026/abc.webp`).

- To display a **public** URL, use `MediaForge::url($path)->public()` (or just `(string) MediaForge::url($path)`).
- To display a **private** (signed, expiring) URL, use `MediaForge::url($path)->private()` and set an expiry:
  - `->minutes(10)`, `->hours(1)`, etc.
  - If you don't set an expiry explicitly, it uses `VORMIA_MEDIAFORGE_PREVIEW_PERIOD` (seconds).
    - If missing: defaults to `86400` (24h)
    - If present but empty (`VORMIA_MEDIAFORGE_PREVIEW_PERIOD=`): defaults to `3600` (1h)

To handle upload failures, wrap the call in a `try/catch`:

```php
use Illuminate\Support\Facades\Log;
use VormiaPHP\Vormia\Facades\MediaForge;

try {
    $path = MediaForge::upload($request->file('image'))
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
- ⚠️ **app.css and app.js**: Remove Vormia `@import` lines from `app.css` (Flux, `./plugins/…`, `./plugins/livewire/…`) and any Vormia block from `app.js` manually if needed
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

