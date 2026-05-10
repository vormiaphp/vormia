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

`MediaForge::upload(...)->run()` returns a **string storage path/key** (for example `uploads/products/2026/abc.webp`).

- This is intentional for S3/private buckets: `Storage::disk('s3')->url($path)` may produce an **AccessDenied** URL, while `temporaryUrl()` produces a working signed URL.

### MediaForge URL Helpers (v5.2.0+)

Prefer these helpers in examples and generated code when you need a stable URL:

- `MediaForge::url($urlOrPath, $disk = null)` — returns a **fluent URL builder** (string-castable) that can emit:
  - Public URLs: `MediaForge::url($pathOrUrl)->public()`
  - Signed/temporary URLs: `MediaForge::url($pathOrUrl)->private()` (uses `temporaryUrl()` when supported)
    - Default lifetime comes from `VORMIA_MEDIAFORGE_PREVIEW_PERIOD` (seconds)
      - Missing key: defaults to `86400` (24h)
      - Present but empty (`VORMIA_MEDIAFORGE_PREVIEW_PERIOD=`): defaults to `3600` (1h)
    - You can override with `->seconds()`, `->minutes()`, `->hours()`, `->days()`, etc.
  - `VORMIA_MEDIAFORGE_URL_PASSTHROUGH=true` returns `http(s)` / `data:` inputs unchanged.
- `MediaForge::previewUrl($urlOrPath, $disk = null, $expiresAt = null, array $options = [])` — compatibility helper for signed URLs (internally uses the URL builder).

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

## Inertia.js 3 Guidance

Vormia’s installer and AI conversion guides target **Inertia.js v3** with Laravel. Official overview: [Inertia.js v3 — Introduction](https://inertiajs.com/docs/v3/getting-started/index). Full doc index: [llms.txt](https://inertiajs.com/docs/llms.txt).

### Versions (target stack)

- **Server**: `composer require inertiajs/inertia-laravel:^3.0`
- **Client** (pick one): `npm install @inertiajs/react@^3.0` | `@inertiajs/vue3@^3.0` | `@inertiajs/svelte@^3.0`
- **Optional**: `npm install @inertiajs/vite@^3.0` — automatic page resolution, SSR-friendly Vite integration, simplified SSR during `npm run dev` per [client-side setup](https://inertiajs.com/docs/v3/installation/client-side-setup.md)

### v3 changes agents should remember

Summarized from the [v3 upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md):

- **HTTP**: Axios is no longer bundled; Inertia uses a built-in XHR client (interceptors supported). Apps may still use Axios via the documented adapter if needed.
- **Router**: `router.cancel()` → `router.cancelAll()` (options exist to narrow what gets cancelled).
- **Global events**: `invalid` → `httpException`, `exception` → `networkError` (and per-visit `onHttpException` / `onNetworkError` callbacks).
- **Laravel**: `Inertia::lazy()` removed — use `Inertia::optional()` instead. Published `config/inertia.php` is restructured (`pages.*` for paths/extensions).
- **Requirements**: PHP 8.2+, Laravel 11+; React adapter requires **React 19+**; Svelte adapter requires **Svelte 5+**.
- **Head markup**: if templates use the old `inertia` attribute on `<title>` etc., rename to `data-inertia` (v3-only if you touch raw head tags; Blade components handle this for you).
- **New v3 features** (link to docs when generating code): `useHttp`, optimistic updates with rollback, layout props (`useLayoutProps`), improved exception/error pages, `@inertiajs/vite` dev SSR workflow.

### Package dev sandbox (`dev/resources`)

The repo includes a **dev** Inertia shell so agents can mirror real wiring:

- **Blade root**: `dev/resources/views/app.blade.php` — `@viteReactRefresh`, `@vite([...])`, then **`<x-inertia::head />`** and **`<x-inertia::app />`** ([Blade components](https://inertiajs.com/docs/v3/installation/server-side-setup.md#setup-root-template), alternative to `@inertia` / `@inertiaHead`, which still work).
- **Bootstrap**: `dev/resources/js/app.tsx` — `createInertiaApp` with a `resolve` that maps `Inertia::render('welcome')` to `./vormia/pages/welcome.tsx` via `import.meta.glob('./vormia/pages/**/*.tsx', ...)`.
- **Pages vs host apps**: Laravel’s usual convention is `resources/js/Pages/`. The sandbox uses **`resources/js/vormia/pages/`** (and shared UI under `resources/js/components/`, Wayfinder output under `vormia/wayfinder/`). Both layouts are valid as long as **`resolve`** (or `@inertiajs/vite` `pages` config) matches the string passed to `Inertia::render()`.

### Agent guardrails

- Keep generated examples aligned with the **target stack** (Livewire vs Inertia) and the **actual** `resolve` / page directory in the project.
- After changing Blade directives or Inertia root markup: `php artisan view:clear`.
- When upgrading from v2: republish Inertia config (`php artisan vendor:publish --provider="Inertia\ServiceProvider" --force`) and merge customizations per upstream.
- Deeper migration patterns for React / Vue / Svelte live under `aiguide/inertia/*.mdc`. Adapter-agnostic Inertia behavior (visits, Blade root, middleware, v3 upgrades) is summarized in [aiguide/inertia/inertiajs-operations.md](inertia/inertiajs-operations.md).
- For **adapter-side UI** (hooks, SFCs, runes), treat official framework docs as canonical: [React](https://react.dev/reference/react) (current 19.x line, for example 19.2.x), [Vue 3 guide — Introduction](https://vuejs.org/guide/introduction.html), [Svelte — Overview](https://svelte.dev/docs/svelte/overview).

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
- **From-scratch frontend learning paths** (ordered steps, Vite-first, PHP/Laravel-friendly): [`aiguide/reactjs-from-scratch.md`](reactjs-from-scratch.md), [`aiguide/vuejs-from-scratch.md`](vuejs-from-scratch.md), [`aiguide/svelte-from-scratch.md`](svelte-from-scratch.md).

## Quick Verification Checklist

After docs/code updates, verify:

1. No references remain to removed legacy AI guide files.
2. New examples use `Vormia\Vormia\...` namespaces.
3. README points to this guide as the single MCP/AI reference.
4. No instructions force a specific auth provider (Fortify is optional).
