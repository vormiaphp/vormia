# Vormia Package - AI Documentation Guide

## 🎯 **Purpose of This Document**

This document serves as a **guideline for AI assistants** (like Cursor) to write comprehensive, accurate, and user-friendly developer documentation for the Vormia Laravel package. Use this guide when creating documentation for developers who have installed Vormia in their Laravel projects.

## 📚 **Package Overview**

**Vormia** is a comprehensive Laravel development package that provides:

- **Media handling and image processing** (MediaForge)
- **User management with meta data support**
- **Role-based access control (RBAC)**
- **Taxonomy management system**
- **Notification system**
- **API authentication middleware**
- **Modular architecture with namespaced components**

## 🏗️ **Architecture Understanding**

### **Namespace Structure**

```
App\
├── Models\Vrm\          # Vormia models (User, Taxonomy, etc.)
├── Services\Vrm\        # Business logic services
├── Providers\Vrm\       # Service providers
├── Middleware\Vrm\      # Custom middleware
├── Traits\Vrm\Model\    # Reusable model traits
├── Controllers\Api\V1\  # API controllers
└── Jobs\Vrm\           # Background job classes
```

### **Key Components**

- **Models**: Extend Laravel Eloquent with Vormia functionality
- **Traits**: Provide reusable functionality (HasUserMeta, HasTaxonomyMeta, HasSlugs)
- **Services**: Handle business logic (UtilityService, GlobalDataService, TokenService)
- **Middleware**: Route protection and authentication
- **Providers**: Service registration and bootstrapping

## 📝 **Documentation Writing Guidelines**

### **1. Structure and Organization**

#### **Required Sections**

- [ ] **Installation & Setup**
- [ ] **Configuration**
- [ ] **Basic Usage**
- [ ] **Advanced Features**
- [ ] **API Reference**
- [ ] **Examples & Code Snippets**
- [ ] **Troubleshooting**
- [ ] **Contributing**

#### **Code Example Format**

```php
// ✅ GOOD: Clear, contextual examples
use App\Models\Vrm\Taxonomy;

// Create a new taxonomy with meta
$category = Taxonomy::create([
    'name' => 'Technology',
    'type' => 'category'
]);

$category->setMeta('description', 'Technology-related content');
$category->setMeta('icon', 'fas fa-laptop');

// ❌ BAD: Unclear, missing context
$category->setMeta('key', 'value');
```

### **2. Installation Documentation**

#### **Prerequisites Section**

```markdown
## Prerequisites

- Laravel 10+
- PHP 8.1+
- Database (MySQL/PostgreSQL/SQLite)
- Composer
- Intervention/Image (for MediaForge features)
```

#### **Installation Steps**

````markdown
## Installation

1. **Install via Composer**
   ```bash
   composer require vormiaphp/vormia
   ```
````

2. **Run Installation Command**

   ```bash
   php artisan vormia:install
   ```

   This automatically installs Vormia with all files and configurations, including API support:

   **Automatically Installed:**

   - All Vormia files and directories
   - CSS and JS plugin files to `resources/css/plugins` and `resources/js/plugins`
   - `app.css` and `app.js` updated with Vormia imports
   - intervention/image package (via Composer)
   - Laravel Sanctum (via `php artisan install:api`)
   - CORS configuration (via `php artisan config:publish cors`)
   - npm packages: jquery, flatpickr, select2, sweetalert2

3. **Run Migrations**
   ```bash
   php artisan migrate
   ```

````

### **3. Configuration Documentation**

#### **Environment Variables**
```markdown
## Environment Configuration

Add these to your `.env` file:

```env
# Vormia Core
VORMIA_TABLE_PREFIX=vrm_
VORMIA_AUTO_UPDATE_SLUGS=false
VORMIA_SLUG_APPROVAL_REQUIRED=true

# MediaForge (Image Processing)
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
````

````

#### **Configuration File**
```markdown
## Configuration File

The package publishes `config/vormia.php`. Key sections:

- `table_prefix`: Database table prefix
- `auto_update_slugs`: Automatic slug updates
- `mediaforge`: Image processing settings
````

#### **CSS and JavaScript Assets**

```markdown
## Frontend Assets

During installation, Vormia automatically:

1. **Copies CSS/JS files** to `resources/css/plugins` and `resources/js/plugins`
2. **Updates app.css** with required imports:
   - `@import '../../vendor/livewire/flux/dist/flux.css';`
   - `@import './plugins/style.min.css';`
3. **Updates app.js** with:
   - jQuery plugin import
   - Select2 initialization (loadSelect2, initSelect2, safeReinitializeSelect2)
   - Flatpickr initialization
   - Livewire hooks setup
   - SweetAlert2 global assignment

**Installed npm packages:**

- jquery
- flatpickr
- select2
- sweetalert2

These are automatically installed during `php artisan vormia:install`.
```

### **4. Usage Documentation**

#### **Model Usage Examples**

````markdown
## User Management

### Basic User Operations

```php
use App\Models\User;

$user = User::find(1);

// Set user meta data
$user->setMeta('preferences', ['theme' => 'dark']);
$user->setMeta('avatar_url', 'https://example.com/avatar.jpg');

// Get user meta data
$preferences = $user->getMeta('preferences', []);
$avatar = $user->getMeta('avatar_url');
```
````

### User Roles and Permissions

```php
// Check user roles
if ($user->hasRole('admin')) {
    // Admin functionality
}

// Check module access
if ($user->hasModule('content')) {
    // Content module access
}
```

````

#### **Taxonomy Management**
```markdown
## Taxonomy System

### Creating Taxonomies
```php
use App\Models\Vrm\Taxonomy;

$category = Taxonomy::create([
    'name' => 'Technology',
    'type' => 'category',
    'position' => 1
]);

// Set taxonomy meta
$category->setMeta('description', 'Technology articles');
$category->setMeta('icon', 'fas fa-laptop');
$category->setMeta('color', '#3B82F6');
````

### Hierarchical Taxonomies

```php
// Create parent-child relationships
$parent = Taxonomy::create(['name' => 'Programming', 'type' => 'category']);
$child = Taxonomy::create([
    'name' => 'PHP',
    'type' => 'category',
    'parent_id' => $parent->id
]);

// Get hierarchy
$descendants = $parent->descendants;
$path = $child->path; // Breadcrumb path
```

````

### **5. API Documentation**

#### **Authentication**
```markdown
## API Authentication

### Middleware Usage
```php
// In routes/api.php
Route::middleware(['api-auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/user/update', [UserController::class, 'update']);
});
````

### Sanctum Integration

```php
// Ensure User model has HasApiTokens trait
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    // ... other traits
}
```

````

#### **API Endpoints**
```markdown
## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout

### User Management
- `GET /api/user/profile` - Get user profile
- `PUT /api/user/profile` - Update user profile
- `GET /api/user/meta` - Get user meta data
````

### **6. Advanced Features**

#### **MediaForge (Image Processing)**

````markdown
## MediaForge - Image Processing

MediaForge provides comprehensive image processing capabilities including resizing, format conversion, compression, watermarking, and thumbnail generation with full control over aspect ratios, background fills, and source images.

### Basic Image Operations

```php
use App\Facades\Vrm\MediaForge;

// Basic upload with resize
$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606)
    ->run();
```

### Resize with Background Fill

When maintaining aspect ratio, you can fill empty areas with a background color:

```php
// Resize with background fill color
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // Creates exact 606x606 with #5a85b9 background
    ->run();

// The image is scaled to fit within 606x606 while maintaining aspect ratio
// Empty areas are filled with the specified color (#5a85b9)
// Final image is exactly 606x606 pixels
```

### Format Conversion

```php
// Convert to WebP with quality control
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')
    ->convert('webp', 90, true, true)  // format, quality, progressive, override
    ->run();
```

### Thumbnail Generation with Full Control

Thumbnails can be generated with precise control over aspect ratio, source image, and fill color:

```php
// Thumbnail with all controls
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')
    ->thumbnail(
        [[500, 500, 'thumb'], [400, 267, 'featured'], [400, 300, 'product']],
        true,   // keepAspectRatio: true = maintain, false = exact dimensions
        false,  // fromOriginal: false = from processed, true = from original uploaded
        '#ffffff' // fillColor: fill empty areas when aspect ratio maintained
    )
    ->run();

// Generate thumbnails from original image (before resize/convert)
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

### File Naming Convention

MediaForge uses consistent naming patterns:

- **Resize only**: `{baseName}-{width}-{height}.{extension}`
  - Example: `abc123-606-606.jpg`
  
- **Resize + Convert**: `{baseName}-{width}-{height}-{format}.{format}`
  - Example: `abc123-606-606-webp.webp`
  
- **Thumbnails**: `{baseName}_{suffix}.{extension}`
  - Example: `abc123-606-606_thumb.webp`
  - Suffix can be custom name or `{width}x{height}`

### Configuration Options

```env
# MediaForge Configuration
VORMIA_MEDIAFORGE_DRIVER=auto
VORMIA_MEDIAFORGE_DEFAULT_QUALITY=85
VORMIA_MEDIAFORGE_DEFAULT_FORMAT=webp
VORMIA_MEDIAFORGE_AUTO_OVERRIDE=false
VORMIA_MEDIAFORGE_PRESERVE_ORIGINALS=true
VORMIA_MEDIAFORGE_THUMBNAIL_KEEP_ASPECT_RATIO=true
VORMIA_MEDIAFORGE_THUMBNAIL_FROM_ORIGINAL=false
```

### Key Features

- **Background Fill**: Resize with fill color creates exact dimensions with image centered on colored background
- **Thumbnail Controls**: Control aspect ratio, source image (original vs processed), and fill color
- **Consistent Naming**: Files always saved with predictable naming patterns
- **Directory Structure**: All processed files (resized, converted, thumbnails) saved in same directory
- **Driver Support**: Automatic detection and switching between GD and Imagick drivers

````

#### **Utility System**

##### **Understanding the Utilities Table Structure**
```php
// The utilities table has this structure:
Schema::create(config('vormia.table_prefix') . 'utilities', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();           // The setting key (e.g., 'theme', 'site_name')
    $table->text('value')->nullable();         // The actual value
    $table->string('type')->default('string'); // Data type: string, integer, boolean, json
    $table->boolean('is_public')->default(false); // Public visibility flag
    $table->boolean('is_active')->default(true);  // Active status flag
    $table->timestamps();
});
```

##### **Important: Type System Confusion**
⚠️ **CRITICAL CLARIFICATION**: There's a conceptual mismatch in the current implementation:

- **Table `type` column**: Stores data types (string, integer, boolean, json)
- **Service `->type('public')` method**: Looks for a category/grouping that doesn't exist in the table

This means the current `->type('public')` method is **not working as intended** and may return unexpected results.

##### **Working Usage Patterns**
```php
// ✅ CORRECT: Get a specific setting by key
$utilities = app('vrm.utilities');

// Method 1: Direct access (recommended)
$theme = $utilities->get('theme', 'default-theme', 'general');

// Method 2: Using the type method (may not work as expected)
$theme = $utilities->type('general')->get('theme');

// Method 3: Get all utilities of a specific type
$allUtilities = $utilities->getByType('general');
```

##### **Cache Management**
```php
// Clear Vormia utilities cache
$utilities = app('vrm.utilities');

// Clear specific type cache
$utilities->clearCache('general');

// Clear all cache
$utilities->clearCache();

// Force fresh data from database
$theme = $utilities->fresh('theme', 'default', 'general');
```

##### **Troubleshooting Utilities**
```php
// Debug: Check what's actually in your utilities table
$tableName = config('vormia.table_prefix', 'vrm_') . 'utilities';

// See all utilities
$allUtilities = DB::table($tableName)->get();
dd('All utilities:', $allUtilities);

// Check specific key
$themeUtility = DB::table($tableName)->where('key', 'theme')->first();
dd('Theme utility:', $themeUtility);

// Check available types (data types, not categories)
$types = DB::table($tableName)->select('type')->distinct()->pluck('type');
dd('Available types:', $types);
```

### **7. Troubleshooting Section**

#### **Common Issues**
```markdown
## Troubleshooting

### Database Connection Issues
**Problem**: Service providers throw database errors before migrations
**Solution**: The package automatically handles this. Ensure migrations are run:
```bash
php artisan migrate
````

### Meta Methods Not Working

**Problem**: `setMeta()` or `getMeta()` methods not found
**Solution**: Ensure your models use the correct traits:

```php
use App\Traits\Vrm\Model\HasUserMeta;

class User extends Authenticatable
{
    use HasUserMeta;
}
```

### API Authentication Failing

**Problem**: 401 errors on protected routes
**Solution**:

1. Sanctum is automatically installed during `php artisan vormia:install`
2. Add `HasApiTokens` trait to User model
3. Check middleware alias: `'api-auth' => \App\Http\Middleware\Vrm\ApiAuthenticate::class`

### CSS/JS Assets Not Loading

**Problem**: jQuery, Select2, Flatpickr, or SweetAlert2 not working
**Solution**:

1. Verify npm packages are installed: `npm list jquery flatpickr select2 sweetalert2`
2. Check that `app.css` contains the required imports
3. Check that `app.js` contains the Vormia initialization code
4. Rebuild assets: `npm run build` or `npm run dev`
5. If packages are missing, reinstall: `npm install jquery flatpickr select2 sweetalert2`

### Installation Dependencies Issues

**Problem**: Missing intervention/image or other dependencies
**Solution**:

- intervention/image is automatically installed during installation
- If installation fails, manually install: `composer require intervention/image`
- npm packages are automatically installed if npm is available
- If npm is not available, manually install: `npm install jquery flatpickr select2 sweetalert2`

```

```

### **8. Code Quality Standards**

#### **Documentation Best Practices**

- **Use clear, descriptive headings**
- **Include practical examples for every feature**
- **Show both simple and advanced usage**
- **Explain configuration options thoroughly**
- **Provide troubleshooting for common issues**
- **Use consistent code formatting**
- **Include version compatibility notes**

#### **Example Quality Checklist**

- [ ] Does the example work as-is?
- [ ] Is the context clear?
- [ ] Are all imports shown?
- [ ] Is the expected output explained?
- [ ] Are error cases covered?
- [ ] Is the example realistic and practical?

## 🚀 **Quick Start Template**

When writing documentation, use this template structure:

```markdown
# Feature Name

## Overview

Brief description of what this feature does.

## Installation

How to set up this feature.

## Basic Usage

Simple examples to get started.

## Advanced Usage

Complex scenarios and advanced features.

## Configuration

Available options and settings.

## Examples

Real-world use cases.

## Troubleshooting

Common issues and solutions.
```

## 📋 **Documentation Review Checklist**

Before finalizing documentation:

- [ ] **Accuracy**: All code examples tested and working
- [ ] **Completeness**: All major features documented
- [ ] **Clarity**: Clear explanations for complex concepts
- [ ] **Examples**: Practical, real-world examples provided
- [ ] **Structure**: Logical organization and flow
- [ ] **Links**: Internal and external references working
- [ ] **Formatting**: Consistent markdown formatting
- [ ] **Screenshots**: Visual aids where helpful
- [ ] **Version Info**: Compatibility and version notes
- [ ] **Updates**: Reflects latest package features

## 🎯 **Remember**

- **Write for developers**, not for yourself
- **Assume the reader is new** to the package
- **Provide complete examples** that can be copy-pasted
- **Explain the "why"** not just the "how"
- **Include error handling** and edge cases
- **Keep it practical** and actionable
- **Update regularly** as the package evolves

---

**This guide ensures consistent, high-quality documentation that helps developers successfully implement and use the Vormia package in their Laravel projects.**
