# Vormia Package - IDE AI Assistant Rules

## 🎯 **Purpose of This Document**

This document provides **IDE-based AI assistants** (Cursor, GitHub Copilot, Tabnine, etc.) with specific rules, patterns, and context for providing accurate code suggestions and assistance when working with the Vormia Laravel package. Use these rules to ensure code quality and consistency.

## 🚨 **Critical Rules for Code Generation**

### **1. Namespace Enforcement**

```php
// ✅ ALWAYS use correct Vormia namespaces
use App\Models\Vrm\Taxonomy;
use App\Services\Vrm\UtilityService;
use App\Traits\Vrm\Model\HasUserMeta;
use App\Http\Middleware\Vrm\ApiAuthenticate;

// ❌ NEVER use incorrect namespaces
use App\Models\Taxonomy;           // Wrong - missing Vrm
use App\Services\UtilityService;   // Wrong - missing Vrm
```

### **2. Trait Usage Patterns**

```php
// ✅ ALWAYS include required traits for Vormia functionality
class User extends Authenticatable
{
    use HasUserMeta, HasSlugs;  // Required for meta and slug functionality

    // Vormia methods now available:
    // - setMeta(), getMeta(), deleteMeta()
    // - slug generation and management
}

// ❌ NEVER forget required traits
class User extends Authenticatable
{
    // Missing traits - meta methods won't work!
}
```

### **3. Method Naming Consistency**

```php
// ✅ ALWAYS use uniform meta method names
$user->setMeta('preference', 'value');      // Correct
$user->getMeta('preference', 'default');    // Correct
$user->deleteMeta('preference');            // Correct

// ❌ NEVER use old method names
$user->setMetaValue('preference', 'value'); // Wrong - deprecated
$user->getMetaValue('preference');          // Wrong - deprecated
```

## 🔧 **Code Generation Patterns**

### **1. Model Creation with Vormia**

```php
// ✅ CORRECT pattern for Vormia models
use App\Models\Vrm\Taxonomy;
use App\Traits\Vrm\Model\HasTaxonomyMeta;

class Category extends Taxonomy
{
    use HasTaxonomyMeta;

    protected $fillable = [
        'name',
        'type',
        'parent_id',
        'position',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer'
    ];

    // Vormia methods automatically available:
    // - setMeta(), getMeta(), deleteMeta()
    // - slug management
    // - hierarchical relationships
}
```

### **2. Service Usage Patterns**

```php
// ✅ CORRECT service instantiation
$utilities = app('vrm.utilities');
$utilities->set('setting_key', 'value', 'category');

// ✅ CORRECT service injection
public function __construct(
    private UtilityService $utilities
) {}

// ❌ WRONG service access
$utilities = new UtilityService(); // Don't instantiate directly
```

#### **⚠️ CRITICAL: Utilities Type System Confusion**

**IMPORTANT**: There's a **conceptual mismatch** between the table design and service implementation:

```sql
-- utilities table structure:
- key (e.g., 'theme')                    ← Setting identifier
- value (e.g., 'dark')                   ← Setting value
- type (e.g., 'string', 'integer')      ← Data type, NOT category
- is_public (true/false)                 ← Public visibility flag
```

**Correct Usage Patterns**:

```php
// ✅ RECOMMENDED: Direct access with explicit type
$theme = $utilities->get('theme', 'default-theme', 'general');

// ⚠️ CAUTION: Type method may not work as expected
$theme = $utilities->type('general')->get('theme');

// ✅ ALTERNATIVE: Get all utilities of a data type
$allUtilities = $utilities->getByType('string');

// ✅ CACHE CLEARING: When utilities aren't working
$utilities->clearCache('general');  // Clear specific type
$utilities->clearCache();           // Clear all cache
$utilities->fresh('theme', 'default', 'general'); // Force fresh data
```

**Never Use**:

```php
// ❌ WRONG: This suggests filtering by category that doesn't exist
$utilities->type('public')->get('theme');

// ❌ WRONG: Type column stores data types, not categories
$utilities->type('is_public')->get('theme');
```

### **3. Middleware Implementation**

```php
// ✅ CORRECT middleware usage in routes
Route::middleware(['api-auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
});

Route::middleware(['role:admin'])->group(function () {
    Route::get('/admin/users', [AdminController::class, 'users']);
});

// ❌ WRONG middleware usage
Route::middleware('auth')->group(function () { // Use Vormia middleware
    Route::get('/user/profile', [UserController::class, 'profile']);
});
```

### **4. MediaForge Image Processing**

```php
// ✅ CORRECT MediaForge usage with resize and background fill
use App\Facades\Vrm\MediaForge;

$imageUrl = MediaForge::upload($request->file('image'))
    ->useYearFolder(true)
    ->randomizeFileName(true)
    ->to('products')
    ->resize(606, 606, true, '#5a85b9')  // width, height, keepAspectRatio, fillColor
    ->convert('webp', 90, true, true)     // format, quality, progressive, override
    ->run();

// ✅ CORRECT thumbnail generation with controls
$imageUrl = MediaForge::upload($file)
    ->to('products')
    ->thumbnail(
        [[500, 500, 'thumb'], [400, 267, 'featured']],  // sizes array
        true,   // keepAspectRatio (null = use config default)
        false,  // fromOriginal (null = use config default)
        '#ffffff' // fillColor (null = no fill)
    )
    ->run();

// ✅ CORRECT thumbnail from original image
$imageUrl = MediaForge::upload($file)
    ->resize(606, 606)
    ->thumbnail([[500, 500, 'thumb']], true, true) // fromOriginal = true
    ->run();

// ❌ WRONG - missing required parameters or incorrect usage
$imageUrl = MediaForge::upload($file)->resize(606); // Missing height parameter
$imageUrl = MediaForge::upload($file)->thumbnail([500, 500]); // Wrong format - needs nested array
```

## 📁 **File Structure Rules**

### **1. Model Organization**

```
app/Models/
├── User.php                    # Enhanced with Vormia traits
└── Vrm/                       # Vormia-specific models
    ├── Taxonomy.php           # Content organization
    ├── Role.php               # User roles
    ├── Permission.php         # User permissions
    └── UserMeta.php           # User metadata
```

### **2. Service Organization**

```
app/Services/
└── Vrm/                       # Vormia services
    ├── UtilityService.php     # Application settings
    ├── GlobalDataService.php  # Global data sharing
    ├── TokenService.php       # Token management
    └── MediaForgeService.php  # Image processing
```

### **3. Trait Organization**

```
app/Traits/
└── Vrm/
    └── Model/
        ├── HasUserMeta.php    # User metadata functionality
        ├── HasTaxonomyMeta.php # Taxonomy metadata functionality
        └── HasSlugs.php       # Slug generation functionality
```

## 🔒 **Security & Access Control Rules**

### **1. Role-Based Access Control**

```php
// ✅ CORRECT role checking
if ($user->hasRole('admin')) {
    // Admin functionality
}

// ✅ CORRECT permission checking
if ($user->hasPermission('edit_users')) {
    // User editing capability
}

// ✅ CORRECT module checking
if ($user->hasModule('content')) {
    // Content management access
}

// ❌ WRONG access control
if ($user->role === 'admin') { // Don't check raw attributes
    // Admin functionality
}
```

### **2. API Authentication**

```php
// ✅ CORRECT API route protection
Route::middleware(['api-auth'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
});

// ✅ CORRECT controller authentication check
public function profile(Request $request)
{
    $user = auth()->guard('sanctum')->user();

    if (!$user) {
        return response()->json(['error' => 'Unauthenticated'], 401);
    }

    return response()->json($user->load('meta'));
}

// ❌ WRONG authentication
public function profile(Request $request)
{
    // Missing authentication check
    return response()->json(['data' => 'profile']);
}
```

## 📊 **Database & Migration Rules**

### **1. Table Naming Convention**

```php
// ✅ ALWAYS use Vormia table prefix
Schema::create(config('vormia.table_prefix') . 'utilities', function (Blueprint $table) {
    $table->id();
    $table->string('key');
    $table->text('value');
    $table->string('type')->default('general');
    $table->timestamps();
});

// ❌ NEVER use hardcoded table names
Schema::create('utilities', function (Blueprint $table) { // Wrong - no prefix
    $table->id();
    $table->string('key');
    $table->text('value');
    $table->timestamps();
});
```

### **2. Relationship Definitions**

```php
// ✅ CORRECT relationship definitions
public function meta()
{
    return $this->hasMany(UserMeta::class, 'user_id');
}

public function roles()
{
    return $this->belongsToMany(
        Role::class,
        config('vormia.table_prefix') . 'role_user'
    );
}

// ❌ WRONG relationship definitions
public function meta()
{
    return $this->hasMany('UserMeta'); // Missing class reference
}
```

## 🎨 **Code Style & Quality Rules**

### **1. Error Handling**

```php
// ✅ ALWAYS include proper error handling
try {
    $utilities = app('vrm.utilities');
    $setting = $utilities->get('key', 'default');
} catch (\Exception $e) {
    Log::error('Failed to get utility setting: ' . $e->getMessage());
    $setting = 'default';
}

// ❌ NEVER ignore potential errors
$utilities = app('vrm.utilities');
$setting = $utilities->get('key', 'default'); // No error handling
```

### **2. Validation Rules**

```php
// ✅ ALWAYS validate input data
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:category,tag',
        'parent_id' => 'nullable|exists:taxonomies,id',
        'position' => 'nullable|integer|min:0'
    ]);

    $taxonomy = Taxonomy::create($validated);

    // Handle metadata separately
    if ($request->has('meta')) {
        foreach ($request->meta as $key => $value) {
            $taxonomy->setMeta($key, $value);
        }
    }

    return response()->json($taxonomy, 201);
}

// ❌ WRONG - no validation
public function store(Request $request)
{
    $taxonomy = Taxonomy::create($request->all()); // No validation
    return response()->json($taxonomy, 201);
}
```

## 🔄 **Common Code Patterns**

### **1. Meta Data Management**

```php
// ✅ CORRECT meta data handling
class UserController extends Controller
{
    public function updateMeta(Request $request, User $user)
    {
        $validated = $request->validate([
            'meta' => 'required|array',
            'meta.*.key' => 'required|string',
            'meta.*.value' => 'required'
        ]);

        foreach ($validated['meta'] as $meta) {
            $user->setMeta($meta['key'], $meta['value']);
        }

        return response()->json([
            'message' => 'Meta data updated successfully',
            'user' => $user->load('meta')
        ]);
    }
}
```

### **2. Taxonomy Management**

```php
// ✅ CORRECT taxonomy creation with hierarchy
class TaxonomyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'parent_id' => 'nullable|exists:taxonomies,id',
            'meta' => 'array'
        ]);

        $taxonomy = Taxonomy::create($validated);

        // Handle metadata
        if (isset($validated['meta'])) {
            foreach ($validated['meta'] as $key => $value) {
                $taxonomy->setMeta($key, $value);
            }
        }

        // Handle slug generation
        if ($taxonomy->shouldAutoUpdateSlug()) {
            $taxonomy->generateSlug();
        }

        return response()->json($taxonomy, 201);
    }
}
```

## 🚫 **Anti-Patterns to Avoid**

### **1. Direct Database Access**

```php
// ❌ WRONG - bypassing Vormia services
DB::table('user_meta')->insert([
    'user_id' => $userId,
    'key' => 'preference',
    'value' => 'dark'
]);

// ✅ CORRECT - using Vormia methods
$user->setMeta('preference', 'dark');
```

### **2. Hardcoded Configuration**

```php
// ❌ WRONG - hardcoded values
$tablePrefix = 'vrm_';
$maxSlugLength = 50;

// ✅ CORRECT - using configuration
$tablePrefix = config('vormia.table_prefix');
$maxSlugLength = config('vormia.max_slug_length', 50);
```

### **3. Missing Trait Usage**

```php
// ❌ WRONG - missing required traits
class User extends Authenticatable
{
    // Missing HasUserMeta trait - meta methods won't work!
}

// ✅ CORRECT - including required traits
class User extends Authenticatable
{
    use HasUserMeta, HasSlugs;
}
```

## 📋 **Code Review Checklist**

When reviewing Vormia-related code, ensure:

- [ ] **Correct namespaces** used (`App\Vrm\`)
- [ ] **Required traits** included for models
- [ ] **Proper middleware** used for routes
- [ ] **Configuration values** from config files
- [ ] **Error handling** implemented
- [ ] **Validation rules** defined
- [ ] **Database prefixes** used correctly
- [ ] **Service injection** instead of direct instantiation
- [ ] **Meta methods** use uniform naming (`setMeta`, `getMeta`)
- [ ] **Access control** implemented properly

## 🎯 **Best Practices Summary**

1. **Always use Vormia namespaces** (`App\Vrm\`)
2. **Include required traits** for model functionality
3. **Use Vormia services** instead of direct database access
4. **Implement proper error handling** and validation
5. **Follow naming conventions** consistently
6. **Use configuration values** instead of hardcoded values
7. **Implement proper access control** with Vormia middleware
8. **Handle metadata** through Vormia methods
9. **Use database prefixes** from configuration
10. **Test functionality** before deployment

---

**These rules ensure that IDE-based AI assistants generate consistent, secure, and maintainable code that properly utilizes Vormia package functionality.**
