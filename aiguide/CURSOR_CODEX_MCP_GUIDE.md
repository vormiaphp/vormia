# Cursor + Codex MCP Guide

## Purpose

This is the single AI/MCP guidance file for this repository. It is shared by:

- Cursor IDE agents
- Codex-style coding agents

Use this guide for safe, consistent changes to the Vormia package.

## Current Package Architecture

Vormia now follows a package-first model. Core logic is in package namespaces and should not be copied to app model files by default.

- Models: `Vormia\Vormia\Models\*`
- Traits: `Vormia\Vormia\Traits\*`
- Middleware: `Vormia\Vormia\Http\Middleware\*`
- API controllers: `Vormia\Vormia\Http\Controllers\Api\*`
- Services: `Vormia\Vormia\Services\*`

Primary provider and registrations:

- `src/VormiaServiceProvider.php`
- API routes are registered under `/api` from `routes/api.php`
- Middleware aliases include: `api-auth`, `role`, `permission`, `authority`, `module`

## MediaForge Guidance

MediaForge is implemented package-first and exposed via a package facade.

- Service binding: `vrm.mediaforge` (container alias)
- Facade: `VormiaPHP\Vormia\Facades\MediaForge`
- Config: `config('vormia.mediaforge.*')` (driver, disk, base_dir, defaults)

Avoid documenting or generating `App\Facades\Vrm\MediaForge` unless explicitly targeting legacy host-app wrappers.

### MediaForge File Upload (no processing)

When the input might be a **non-image** (PDFs, docs, zips, etc.), do **not** decode it with Intervention.

Prefer one of these patterns:

- `MediaForge::uploadFile($file)->to('documents')->run()` — explicit “raw file upload”
- `MediaForge::upload($file)->isFile()->to('documents')->run()` — fluent flag on the normal upload pipeline

`isFile()` disables all image operations (`resize()`, `convert()`, `thumbnail()`), even if the uploaded file is an image.

### MediaForge Return Value (S3 / Remote Disks)

`MediaForge::upload(...)->run()` returns a **string** using “URL-or-path” behavior:

- If the configured Laravel disk supports `url()`, return a **URL string** (often `https://{bucket}.s3.../{key}` or your `AWS_URL` / CloudFront URL).
- If `url()` can’t be generated (or throws), return the **storage path/key** (for example `uploads/products/2026/abc.webp`).

### MediaForge URL Helpers (v5.2.0+)

Prefer these helpers in examples and generated code when you need a stable URL:

- `MediaForge::url($urlOrPath, $disk = null)` — normalize a URL-or-path return value into something usable in `<img src="...">` where possible.
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=true` will return `http(s)` / `data:` inputs unchanged.
- `MediaForge::previewUrl($urlOrPath, $disk = null, $expiresAt = null, array $options = [])` — generate preview URLs, including signed temporary URLs when supported by the disk.
  - Defaults are configured via `VORMIA_MEDIAFORGE_PREVIEW_MODE` (`auto|public|private|proxy`) and `VORMIA_MEDIAFORGE_PREVIEW_EXPIRES_MINUTES`.

### MediaForge Proxy Preview Route (only when enabled)

If `VORMIA_MEDIAFORGE_PREVIEW_MODE=proxy`, the package route `GET /api/vrm/media/preview?disk=...&path=...` will stream a file for previewing. If proxy mode is not enabled, the controller returns `404`.

## Safe Editing Rules

- Prefer package namespaces over app-local stubs for new functionality.
- Do not replace `app/Models/User.php` automatically.
- Keep auth flow decisions in developer control (no enforced Fortify-specific flow).
- Keep migrations package-driven via `loadMigrationsFrom(...)` and optional publish tags.
- Preserve table-prefix behavior via `config('vormia.table_prefix')`.
- Preserve configurable user model behavior via `config('vormia.user_model') ?? config('auth.providers.users.model')`.

## User and Role Integration Pattern

When documenting or generating integration code for host apps:

1. Ask developer to add `Vormia\Vormia\Traits\HasVormiaRoles` to User model.
2. Ask developer to add `is_active` to User `$fillable` and casts.
3. Let developer decide registration role assignment location (Fortify or custom flow).

Do not hard-require Fortify.

## Livewire 4 Guidance

For Livewire usage in this package:

- Prefer Livewire 4 patterns and attributes.
- Use `Vormia\Vormia\Traits\Livewire\WithNotifications`.
- Event listeners should use attribute-based style (for example, `#[On(...)]`) rather than legacy `$listeners` mutation patterns.

## API/Auth Guidance

- Sanctum-backed auth middleware alias: `api-auth`.
- Package routes live in `routes/api.php` and are loaded with `/api` prefix.
- Keep API auth behavior modular and avoid coupling it to one frontend/auth stack.

## Cursor-Specific Notes

- Favor minimal, precise edits over broad rewrites.
- Keep docs and code aligned whenever architecture changes.
- Before deleting or moving files, verify references in `README.md`, docs, and changelog notes.

## Codex-Specific Notes

- Use package source as the source of truth for examples.
- Avoid generating code with outdated namespaces such as `App\Models\Vrm\*` unless explicitly targeting legacy stubs.
- Prefer deterministic changes and include short migration notes when behavior changes.

## Documentation Conventions

- Canonical installation and usage docs live in:
  - `README.md`
  - `docs/INSTALLATION.md`
- This file is only for AI/MCP behavior and architecture guardrails.

## Quick Verification Checklist

After docs/code updates, verify:

1. No references remain to removed legacy AI guide files.
2. New examples use `Vormia\Vormia\...` namespaces.
3. README points to this guide as the single MCP/AI reference.
4. No instructions force a specific auth provider (Fortify is optional).
