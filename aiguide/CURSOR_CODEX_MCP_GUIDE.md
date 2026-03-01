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
