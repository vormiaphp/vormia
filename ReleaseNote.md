# Release Notes

This document contains human-friendly release notes for tagged versions of this package.

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

