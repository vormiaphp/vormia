# Release Notes

This document contains human-friendly release notes for tagged versions of this package.

## v5.4.0

### Summary
- MediaForge now returns **storage paths/keys by default**, and provides a fluent URL builder that can generate either **public** URLs or **signed temporary** URLs for private buckets (S3, R2, Spaces, etc.).

### Breaking Changes
- **MediaForge upload return value**: `MediaForge::upload(...)->run()` and `MediaForge::uploadFile(...)->run()` now return a **path/key** (e.g. `uploads/products/2026/04/17/abc.webp`) instead of sometimes returning a URL.
  - **Upgrade**: If your app previously stored the return value as a URL, update it to store the returned **path** and build URLs at render time.

### Changes
- **Fluent URL builder**: `MediaForge::url($pathOrUrl, $disk = null)` now returns a builder (string-castable):
  - Public: `MediaForge::url($path)->public()`
  - Private/signed: `MediaForge::url($path)->private()` (uses `temporaryUrl()` when supported)
  - Expiry helpers: `->seconds()`, `->minutes()`, `->hours()`, `->days()`, `->years()`, or `->expiresAt(...)`
- **Preview URL defaults**:
  - New env: `VORMIA_MEDIAFORGE_PREVIEW_PERIOD` (seconds)
    - Missing key: defaults to **86400** (24h)
    - Present but empty (`VORMIA_MEDIAFORGE_PREVIEW_PERIOD=`): defaults to **3600** (1h)
- **Installer env updates** (`php artisan vormia:install`):
  - Adds `VORMIA_MEDIAFORGE_STORAGE_RULE=vormia`
  - Adds `VORMIA_MEDIAFORGE_PREVIEW_PERIOD=86400`
- **Compatibility**:
  - `MediaForge::previewUrl(...)` remains available as a compatibility helper (internally uses the builder).

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.4
php artisan vormia:install
```

## v5.2.0

### Summary
- Feature release focused on **MediaForge URL handling** for local disks and S3/remote disks: normalize “URL-or-path” return values, generate previewable URLs (including signed URLs when supported), and optionally enable a proxy preview endpoint.

### Changes
- **`MediaForge::url()`**: helper to turn either a URL **or** a storage path/key into something safe to use in `<img src="...">` when possible.
  - Controlled by `VORMIA_MEDIAFORGE_STORAGE_RULE`, `VORMIA_MEDIAFORGE_DISK`, and `VORMIA_MEDIAFORGE_URL_PASSTHROUGH`.
- **`MediaForge::previewUrl()`**: helper to generate a browser-previewable URL for a MediaForge output.
  - Uses `temporaryUrl()` when available and configured to prefer private previews.
  - Defaults are controlled via:
    - `VORMIA_MEDIAFORGE_PREVIEW_MODE` (`auto|public|private|proxy`)
    - `VORMIA_MEDIAFORGE_PREVIEW_EXPIRES_MINUTES`
- **Proxy preview endpoint (optional)**: when `VORMIA_MEDIAFORGE_PREVIEW_MODE=proxy`, Vormia enables:
  - `GET /api/vrm/media/preview?disk={disk}&path={path}`
  - Streams the file from the configured disk for previewing (returns `404` if proxy mode is not enabled).

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.2
```

## v5.1.3

### Summary
- Patch release: `vormia:install` is resilient when two-factor columns already exist on `users` (Fortify, Breeze, or an earlier migration) and when `php artisan install:api` would otherwise publish a duplicate two-factor migration.

### Changes
- **`TwoFactorMigrationNormalizer`** (`src/Console/Support/TwoFactorMigrationNormalizer.php`): runs automatically during `vormia:install`—before the kit’s `migrate`, and again before `install:api`. It finds migrations in `database/migrations` whose names include `two_factor` and `user`, and replaces unconditional `Schema::table('users')` adds with idempotent `Schema::hasColumn` checks for `two_factor_secret`, `two_factor_recovery_codes`, and `two_factor_confirmed_at`.
- **`installSanctum()`**: skips `php artisan install:api` when `users.two_factor_secret` already exists; installs Sanctum via `composer require laravel/sanctum` and publishes `SanctumServiceProvider` (including `personal_access_tokens` migration when missing).
- On duplicate `two_factor_*` column errors from `install:api`, the installer re-runs the normalizer, runs `migrate --force`, then finishes Sanctum setup if Sanctum is still missing.
- `install:api` is invoked with `--no-interaction` for predictable scripted installs.
- No separate Artisan command for two-factor patching—behavior is integrated into `vormia:install` only.

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.1
```

### Manual fix (older installs or failed installs)
1. Upgrade the package, then run `php artisan vormia:install` again so the normalizer can rewrite unsafe migrations before `migrate` runs.
2. If a bad migration file remains, delete or hand-edit it under `database/migrations/`, then run `php artisan migrate`.

## v5.1.2

### Summary
- Documentation-focused patch: the copy-paste `User` example is aligned with Vormia traits and migrations.

### Changes
- Expanded `examples/User.php` with a file-level guide for integrating `HasVormiaRoles` (roles, permissions, and composed `HasUserMeta`) and `HasSlugs` (registry-backed slugs).
- Switched to explicit `$fillable` / `$hidden` and added fields that match the Vormia users migration (`username`, `phone`, `provider`, `provider_id`, `avatar`), plus `SoftDeletes` and `phone_verified_at` casting.
- Exposed the primary slug as a virtual `slug` attribute via `Attribute::get()` so route generation and serialization stay consistent with `findBySlug()` / `resolveRouteBinding()`.
- Added `isMember()` (role check by slug `member`) and tightened return types on slug-related methods.

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.1
```

## v5.1.1

### Summary
- Patch release to fix a PHP `match` syntax issue in `NotificationService`.

### Changes
- Fixed invalid `match` arm for the `INFO` notification type that could trigger a parse error (`unexpected token "default", expecting "=>"`).

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.1
```

## v5.1.0

### Summary
- Maintenance release to align dependencies and keep repository artifacts clean.

### Changes
- Synced `composer.lock` with `composer.json` after dependency updates.
- Stopped tracking PHPUnit cache artifacts and ignored `/.phpunit.cache/`.

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.1
```

### Verification
```bash
composer validate --strict
composer test
```

