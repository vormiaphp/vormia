# Release Notes

This document contains human-friendly release notes for tagged versions of this package.

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

