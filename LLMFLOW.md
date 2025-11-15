# Vormia Package - LLM & AI Assistant Guide

## 🎯 **Purpose of This Document**

This document is designed for **Large Language Models (LLMs)** and **AI assistants** (ChatGPT, Claude, Gemini, etc.) to understand the Vormia Laravel package architecture, functionality, and implementation patterns. Use this guide when providing assistance to developers working with Vormia.

## 🏗️ **Package Architecture Overview**

### **Core Concept**

Vormia is a **modular Laravel package** that extends Laravel's functionality with enterprise-grade features for user management, content organization, media processing, and API development.

### **Design Philosophy**

- **Modular Architecture**: Each feature is self-contained with clear interfaces
- **Trait-Based Extensions**: Models gain functionality through composable traits
- **Service-Oriented**: Business logic is separated into dedicated service classes
- **Namespace Isolation**: All Vormia components use `App\Vrm\` namespace prefix
- **Database Abstraction**: Graceful handling of database connection states

## 📁 **File Structure & Organization**

```
src/stubs/                           # Package source files
├── models/                          # Eloquent models
│   ├── User.php                    # Enhanced User model with Vormia traits
│   └── Vrm/                        # Vormia-specific models
│       ├── Taxonomy.php            # Hierarchical content organization
│       ├── TaxonomyMeta.php        # Taxonomy metadata storage
│       ├── UserMeta.php            # User metadata storage
│       ├── Role.php                # User role definitions
│       ├── Permission.php          # Permission definitions
│       └── AuthToken.php           # Authentication tokens
├── traits/                         # Reusable functionality
│   └── Vrm/Model/
│       ├── HasUserMeta.php         # User metadata management
│       ├── HasTaxonomyMeta.php     # Taxonomy metadata management
│       └── HasSlugs.php            # URL slug generation
├── services/                       # Business logic services
│   └── Vrm/
│       ├── UtilityService.php      # Application settings management
│       ├── GlobalDataService.php   # Global data sharing
│       ├── TokenService.php        # Token generation/validation
│       └── MediaForgeService.php   # Image processing
├── providers/                      # Service providers
│   └── Vrm/
│       ├── GlobalDataServiceProvider.php
│       ├── UtilitiesServiceProvider.php
│       └── TokenServiceProvider.php
├── middleware/                     # HTTP middleware
│   └── Vrm/
│       ├── CheckRole.php           # Role-based access control
│       ├── CheckPermission.php     # Permission-based access control
│       ├── CheckModule.php         # Module-based access control
│       └── ApiAuthenticate.php     # API authentication
├── controllers/                    # HTTP controllers
│   └── Api/V1/                    # API version 1 controllers
│       ├── AuthLoginController.php
│       ├── AuthRegisterController.php
│       └── UserController.php
└── routes/                         # Route definitions
    └── api.php                     # API route definitions
```

## 🔧 **Core Components Deep Dive**

### **1. Models & Traits System**

#### **User Model Enhancement**

```php
// The User model is enhanced with multiple Vormia traits
use App\Traits\Vrm\Model\HasUserMeta;
use App\Traits\Vrm\Model\HasSlugs;

class User extends Authenticatable
{
    use HasUserMeta, HasSlugs;

    // Vormia adds these methods:
    // - setMeta($key, $value, $flag = 1)
    // - getMeta($key, $default = null)
    // - deleteMeta($key)
    // - hasRole($role)
    // - hasPermission($permission)
    // - hasModule($module)
}
```

#### **Trait Functionality**

- **HasUserMeta**: Provides `setMeta()`, `getMeta()`, `deleteMeta()` methods
- **HasSlugs**: Provides automatic URL slug generation and management
- **HasTaxonomyMeta**: Provides taxonomy-specific metadata management

### **2. Service Layer Architecture**

#### **UtilityService - Application Settings**

```php
// Manages application-wide settings and configuration
$utilities = app('vrm.utilities');

// Set application settings
$utilities->set('site_name', 'My Site', 'general');
$utilities->set('maintenance_mode', false, 'system');

// Retrieve settings with defaults
$siteName = $utilities->get('site_name', 'Default Site', 'general');
```

##### **⚠️ CRITICAL: Utilities Table Structure Confusion**

**IMPORTANT DISCLAIMER**: There's a **conceptual mismatch** between the table design and service implementation:

```sql
-- The utilities table structure:
utilities table:
- id
- key (e.g., 'theme', 'site_name')           ← Setting identifier
- value (e.g., 'dark', 'My Site')             ← Actual setting value  
- type (e.g., 'string', 'integer', 'boolean') ← Data type, NOT category
- is_public (true/false)                      ← Public visibility flag
- is_active (true/false)                      ← Active status flag
```

**The Problem**: The `->type('public')` method suggests it's filtering by a category, but the table's `type` column stores **data types**, not **categories**.

**Working Usage Patterns**:
```php
// ✅ RECOMMENDED: Direct access with explicit type
$theme = $utilities->get('theme', 'default-theme', 'general');

// ⚠️ CAUTION: Type method may not work as expected
$theme = $utilities->type('general')->get('theme');

// ✅ ALTERNATIVE: Get all utilities of a data type
$allUtilities = $utilities->getByType('string');
```

**Cache Management**:
```php
// Clear cache when utilities aren't working
$utilities->clearCache('general');  // Clear specific type
$utilities->clearCache();           // Clear all cache
$utilities->fresh('theme', 'default', 'general'); // Force fresh data
```

#### **GlobalDataService - View Data Sharing**

```php
// Shares common data across all views
// Automatically handles database connection states
// Provides theme paths, breadcrumbs, and global settings
```

#### **TokenService - Authentication Tokens**

```php
// Generates and validates authentication tokens
// Supports OTP generation for two-factor authentication
// Manages token expiration and cleanup
```

### **3. Middleware System**

#### **Access Control Middleware**

```php
// Role-based access control
Route::middleware(['role:admin'])->group(function () {
    // Admin-only routes
});

// Permission-based access control
Route::middleware(['permission:edit_users'])->group(function () {
    // User editing routes
});

// Module-based access control
Route::middleware(['module:content'])->group(function () {
    // Content management routes
});
```

#### **API Authentication Middleware**

```php
// Protects API routes with Sanctum authentication
Route::middleware(['api-auth'])->group(function () {
    // Protected API endpoints
});
```

### **4. Taxonomy System**

#### **Hierarchical Content Organization**

```php
// Taxonomies can represent categories, tags, or any content classification
$category = Taxonomy::create([
    'name' => 'Technology',
    'type' => 'category',
    'position' => 1
]);

// Hierarchical relationships
$parent = Taxonomy::create(['name' => 'Programming', 'type' => 'category']);
$child = Taxonomy::create([
    'name' => 'PHP',
    'type' => 'category',
    'parent_id' => $parent->id
]);

// Metadata support
$category->setMeta('description', 'Tech articles');
$category->setMeta('icon', 'fas fa-laptop');
$category->setMeta('color', '#3B82F6');
```

## 🔄 **Data Flow Patterns**

### **1. User Authentication Flow**

```
1. User submits credentials → AuthController
2. Validation and authentication → Laravel Sanctum
3. Token generation → TokenService
4. User data retrieval → User model with Vormia traits
5. Response with user data and meta → JSON response
```

### **2. Content Management Flow**

```
1. Content creation → Taxonomy model
2. Metadata assignment → setMeta() methods
3. Slug generation → HasSlugs trait
4. Hierarchy management → parent_id relationships
5. Storage → Database with proper relationships
```

### **3. Access Control Flow**

```
1. Route request → Middleware stack
2. Authentication check → Sanctum guard
3. Role/permission verification → User model methods
4. Access granted/denied → Response or redirect
```

## 🛡️ **Error Handling & Resilience**

### **Database Connection Protection**

```php
// Service providers gracefully handle missing database
try {
    if (app()->runningInConsole()) return;

    DB::connection()->getPdo();

    if (Schema::hasTable(config('vormia.table_prefix') . 'utilities')) {
        // Proceed with database operations
    }
} catch (\Exception $e) {
    // Gracefully handle database unavailability
}
```

### **Graceful Degradation**

- **Missing dependencies**: Clear error messages and installation instructions
- **Database unavailable**: Service providers skip database operations
- **Configuration missing**: Sensible defaults are applied

### **Installation Process**

The `php artisan vormia:install` command automatically:
- Installs all Vormia files and directories
- Copies CSS/JS plugin files to `resources/css/plugins` and `resources/js/plugins`
- Updates `app.css` with Vormia imports (`@import '../../vendor/livewire/flux/dist/flux.css'`, `@import './plugins/style.min.css'`)
- Updates `app.js` with Vormia initialization code (jQuery, Select2, Flatpickr, SweetAlert2, Livewire hooks)
- Installs intervention/image via `composer require intervention/image`
- Installs Laravel Sanctum via `php artisan install:api`
- Publishes CORS configuration via `php artisan config:publish cors`
- Installs npm packages: jquery, flatpickr, select2, sweetalert2

**Note**: API support is always included - no `--api` flag needed.

### **Uninstallation Process**

The `php artisan vormia:uninstall` command automatically:
- Removes all Vormia files and directories
- Removes CSS/JS plugin files from `resources/css/plugins` and `resources/js/plugins`
- Removes npm packages: jquery, flatpickr, select2, sweetalert2
- Removes intervention/image via `composer remove intervention/image`
- Cleans up bootstrap/app.php configurations
- Removes environment variables
- Optionally removes database tables (unless `--keep-data` flag is used)

**Manual cleanup required:**
- Laravel Sanctum: `composer remove laravel/sanctum` (if desired)
- CORS config: Delete `config/cors.php` (if desired)
- app.css and app.js: Manually remove Vormia imports and initialization code
- Composer package: `composer remove vormiaphp/vormia`

## 🔌 **Integration Points**

### **1. Laravel Integration**

- **Service Providers**: Automatically registered during installation
- **Middleware**: Added to application middleware stack
- **Models**: Enhanced with Vormia functionality through traits
- **Routes**: API routes automatically included
- **CSS/JS Assets**: Automatically copied to resources directory and integrated into app.css/app.js
- **Dependencies**: intervention/image, Sanctum, and npm packages automatically installed

### **2. Sanctum Integration**

- **Authentication**: Built-in support for Laravel Sanctum
- **Token Management**: Automatic token generation and validation
- **API Protection**: Middleware for protecting API endpoints

### **3. Database Integration**

- **Migrations**: Automatic table creation and structure management
- **Relationships**: Proper foreign key constraints and relationships
- **Meta Storage**: Flexible metadata storage system

## 📊 **Configuration Management**

### **Environment Variables**

```env
# Core Configuration
VORMIA_TABLE_PREFIX=vrm_
VORMIA_AUTO_UPDATE_SLUGS=false
VORMIA_SLUG_APPROVAL_REQUIRED=true

# MediaForge Configuration
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false
VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true
VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true
VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false
```

### **Configuration File**

```php
// config/vormia.php
return [
    'table_prefix' => env('VORMIA_TABLE_PREFIX', 'vrm_'),
    'auto_update_slugs' => env('VORMIA_AUTO_UPDATE_SLUGS', false),
    'slug_approval_required' => env('VORMIA_SLUG_APPROVAL_REQUIRED', true),
    'mediaforge' => [
        'driver' => env('VORMIA_MEDIAFORGE_DRIVER', 'auto'),
        'default_quality' => env('VORMIA_MEDIAFORGE_DEFAULT_QUALITY', 85),
        'default_format' => env('VORMIA_MEDIAFORGE_DEFAULT_FORMAT', 'webp'),
        'auto_override' => env('VORMIA_MEDIAFORGE_AUTO_OVERRIDE', false),
        'preserve_originals' => env('VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS', true),
        'thumbnail_keep_aspect_ratio' => env('VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO', true),
        'thumbnail_from_original' => env('VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL', false),
    ],
];
```

## 🚀 **Common Use Cases & Patterns**

### **1. User Management**

```php
// Create user with metadata
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password')
]);

// Set user preferences
$user->setMeta('theme', 'dark');
$user->setMeta('timezone', 'UTC');
$user->setMeta('preferences', ['notifications' => true]);

// Check user capabilities
if ($user->hasRole('admin')) {
    // Admin functionality
}

if ($user->hasPermission('edit_users')) {
    // User editing capability
}
```

### **2. Content Organization**

```php
// Create content categories
$techCategory = Taxonomy::create([
    'name' => 'Technology',
    'type' => 'category'
]);

$programmingCategory = Taxonomy::create([
    'name' => 'Programming',
    'type' => 'category',
    'parent_id' => $techCategory->id
]);

// Add metadata
$techCategory->setMeta('description', 'Technology articles and tutorials');
$techCategory->setMeta('icon', 'fas fa-microchip');
$techCategory->setMeta('color', '#3B82F6');
```

### **3. MediaForge Image Processing**

```php
use App\Facades\Vrm\MediaForge;

// Basic upload with resize and convert
$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Resize with background fill
    ->convert('webp', 90, true, true)     // Convert to WebP with override
    ->run();

// Resize with background fill color
// When aspect ratio is maintained, empty areas are filled with the specified color
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // 606x606 with #5a85b9 background
    ->run();

// Thumbnail generation with full control
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')
    ->thumbnail(
        [[500, 500, 'thumb'], [400, 267, 'featured'], [400, 300, 'product']],
        true,   // keepAspectRatio: true = maintain, false = exact dimensions
        false,  // fromOriginal: false = from processed, true = from original
        '#ffffff' // fillColor: fill empty areas when aspect ratio maintained
    )
    ->run();

// Thumbnail from original image (before resize/convert)
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606)
    ->thumbnail([[500, 500, 'thumb']], true, true) // fromOriginal = true
    ->run();

// Exact thumbnail dimensions (no aspect ratio)
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->thumbnail([[500, 500, 'thumb']], false) // keepAspectRatio = false
    ->run();
```

**File Naming Convention:**
- Resize only: `{baseName}-{width}-{height}.{extension}` (e.g., `image-606-606.jpg`)
- Resize + Convert: `{baseName}-{width}-{height}-{format}.{format}` (e.g., `image-606-606-webp.webp`)
- Thumbnails: `{baseName}_{suffix}.{extension}` (e.g., `image-606-606_thumb.webp`)

**Key Features:**
- **Background Fill**: Resize with fill color creates exact dimensions with image centered on colored background
- **Thumbnail Controls**: Control aspect ratio, source image (original vs processed), and fill color
- **Consistent Naming**: Files always saved with predictable naming patterns
- **Directory Structure**: All processed files (resized, converted, thumbnails) saved in same directory

### **4. API Development**

```php
// Protected API routes
Route::middleware(['api-auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::put('/user/profile', [UserController::class, 'update']);
    Route::get('/user/meta', [UserController::class, 'getMeta']);
});

// Role-based API access
Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
    Route::post('/admin/users', [AdminController::class, 'createUser']);
});
```

## 🔍 **Troubleshooting Patterns**

### **1. Common Issues**

- **Meta methods not found**: Ensure models use correct traits
- **Database errors**: Check migrations and database connection
- **Authentication failures**: Verify Sanctum configuration and middleware
- **Permission denied**: Check user roles and permissions

### **2. Debugging Steps**

```php
// Check user capabilities
dd($user->roles->pluck('name'));
dd($user->permissions->pluck('name'));

// Check taxonomy hierarchy
dd($taxonomy->path); // Breadcrumb path
dd($taxonomy->descendants); // All child categories

// Check service availability
dd(app('vrm.utilities')->get('site_name'));
```

## 📈 **Performance Considerations**

### **1. Database Optimization**

- **Eager Loading**: Use `with()` for related data
- **Indexing**: Proper indexes on frequently queried fields
- **Caching**: Utility service includes caching mechanisms

### **2. Memory Management**

- **Lazy Loading**: Traits load functionality only when needed
- **Service Caching**: Services cache frequently accessed data
- **Efficient Queries**: Optimized database queries with proper relationships

## 🎯 **Best Practices for AI Assistance**

### **1. When Helping Developers**

- **Always check namespace**: Ensure `App\Vrm\` prefix is used
- **Verify traits**: Confirm models use correct Vormia traits
- **Check middleware**: Ensure proper middleware registration
- **Validate configuration**: Verify environment variables and config files

### **2. Code Generation Patterns**

- **Use proper imports**: Include all necessary use statements
- **Follow naming conventions**: Use Vormia's established patterns
- **Include error handling**: Add try-catch blocks where appropriate
- **Document assumptions**: Explain any configuration requirements

### **3. Problem-Solving Approach**

1. **Identify the component**: Which Vormia feature is involved?
2. **Check dependencies**: Are all required services available?
3. **Verify configuration**: Is the package properly configured?
4. **Test functionality**: Does the basic feature work?
5. **Debug step-by-step**: Use logging and debugging tools

---

**This guide provides LLMs and AI assistants with comprehensive understanding of the Vormia package architecture, enabling them to provide accurate, helpful assistance to developers implementing Vormia in their Laravel projects.**
