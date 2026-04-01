# Release Notes

This document contains human-friendly release notes for tagged versions of this package.

## v5.1.3

### Summary
- Patch release: `vormia:install` no longer fails when Laravel Sanctum’s `install:api` step tries to add two-factor columns that already exist on `users` (e.g. Fortify or Breeze).

### Changes
- `InstallCommand::installSanctum()` skips `php artisan install:api` when `users.two_factor_secret` already exists, and installs Sanctum with `composer require laravel/sanctum` plus `vendor:publish` for `SanctumServiceProvider` only.
- If `install:api` still fails with a duplicate `two_factor_*` column error, the installer patches migrations (see below), re-runs `migrate`, then completes Sanctum setup as needed.
- `install:api` is invoked with `--no-interaction` for consistent non-interactive runs.
- **`TwoFactorMigrationNormalizer`**: before `migrate` during install, and again before `install:api`, Vormia scans `database/migrations` for files whose names include `two_factor` and `user`, and replaces unsafe Fortify-style migrations with an idempotent version that uses `Schema::hasColumn` for `two_factor_secret`, `two_factor_recovery_codes`, and `two_factor_confirmed_at`.

### Upgrade / Install
```bash
composer require vormiaphp/vormia:^5.1
```

### Manual fix (older installs)
If a duplicate two-factor migration still exists, edit or delete that file under `database/migrations/`, or run `php artisan migrate` again after re-running `vormia:install` so the normalizer can patch the migration.

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

