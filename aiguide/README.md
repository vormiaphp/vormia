# AI Conversion Guides

This folder contains `.mdc` AI-oriented conversion guides for transforming between React and various Laravel/Next.js stacks. Each guide uses `alwaysApply: true` so Cursor and compatible AI assistants automatically reference it when working in the described context.

Companion **Markdown** guides (not auto-applied rules) live here too — for example **Inertia operations** and **from-scratch** learning paths below.

## From-scratch frontend guides (Markdown)

Step-by-step approaches for developers new to each stack (Vite-first, with PHP/Laravel parallels where helpful):

| File | Purpose |
|------|---------|
| [reactjs-from-scratch.md](reactjs-from-scratch.md) | Learn **React** (hooks, JSX, components, routing) in a sensible order |
| [vuejs-from-scratch.md](vuejs-from-scratch.md) | Learn **Vue 3** (`<script setup>`, reactivity, router, Pinia when needed) |
| [svelte-from-scratch.md](svelte-from-scratch.md) | Learn **Svelte 5** (runes, `.svelte` files, snippets; SvelteKit deferred) |

## Available Guides

| File | Conversion Track |
|------|-----------------|
| [`react-to-laravel-livewire-inline.mdc`](livewire/react-to-laravel-livewire-inline.mdc) | React → Laravel Blade + Livewire Volt (inline/non-class components) |
| [`inertiajs-operations.md`](inertia/inertiajs-operations.md) | **Inertia.js v3 (any adapter)** — Laravel visits, Blade root, middleware, forms, SSR; not React/Vue/Svelte-specific |
| [`react-to-inertia-react.mdc`](inertia/react-to-inertia-react.mdc) | React → Inertia.js with React adapter |
| [`react-to-inertia-vue.mdc`](inertia/react-to-inertia-vue.mdc) | React → Inertia.js with Vue 3 adapter |
| [`react-to-inertia-svelte.mdc`](inertia/react-to-inertia-svelte.mdc) | React → Inertia.js with Svelte 5 adapter |
| [`laravel-to-nextjs-tanstack.mdc`](beta/laravel-to-nextjs-tanstack.mdc) | Laravel backend → Next.js (App Router) + TanStack Query |
| [`laravel-to-react.mdc`](beta/laravel-to-react.mdc) | Laravel monolith → Standalone React SPA (with Laravel JSON API) |
| [`react-to-expo-react-native.mdc`](beta/react-to-expo-react-native.mdc) | React (web) → Expo React Native (core primitives) |
| [`react-to-expo-react-native-gluestack.mdc`](beta/react-to-expo-react-native-gluestack.mdc) | React (web) → Expo React Native + GlueStack UI v2 |

## Guide Structure

Every guide follows the same sections:

1. **Tech Stack** — versions in use
2. **File Structure** — before/after folder layout
3. **Component Conversion** — pattern-by-pattern translation
4. **State Management** — state/store/signal mapping
5. **Event Handling** — click, submit, input events
6. **Data Fetching** — server vs client data loading
7. **Routing & Navigation** — route definitions and params
8. **Common Patterns** — conditionals, loops, slots, forms
9. **Best Practices** — naming, typing, performance, a11y
10. **Migration Checklist** — step-by-step conversion checklist
11. **Full Example** — end-to-end before/after component
12. **Resources** — official documentation links

## Related

- [`livewire/react-to-laravel-livewire-inline.mdc`](livewire/react-to-laravel-livewire-inline.mdc) — React → Laravel Blade + Livewire Volt (inline components)

## Vormia MediaForge Note (S3 / Remote Disks)

When host-app code uses `VormiaPHP\Vormia\Facades\MediaForge`, the `->run()` method returns a **string storage path/key** (for example `uploads/products/2026/abc.webp`).

- To display a **public** URL: `MediaForge::url($path)->public()`
- To display a **private** (signed) URL: `MediaForge::url($path)->private()`
  - Default lifetime comes from `VORMIA_MEDIAFORGE_PREVIEW_PERIOD` (seconds)
    - Missing key: defaults to **24h**
    - Present but empty (`VORMIA_MEDIAFORGE_PREVIEW_PERIOD=`): defaults to **1h**
  - Override as needed: `->seconds()`, `->minutes()`, `->hours()`, `->days()`, etc.

## MediaForge File Upload (no processing)

If the uploaded file might be a **non-image** (PDFs, docs, zips, etc.), use either:

- `MediaForge::uploadFile($file)->to('documents')->run()` (explicit raw file upload), or
- `MediaForge::upload($file)->isFile()->to('documents')->run()` (fluent flag)

`isFile()` disables all image operations (`resize()`, `convert()`, `thumbnail()`), even if the file is an image.

In `v5.2.0+`, prefer these helpers when converting UI code:

- `MediaForge::url($pathOrUrl, $disk = null)` — fluent URL builder for `<img src="...">`
  - Public: `->public()`
  - Private/signed: `->private()`
- `MediaForge::previewUrl(...)` — compatibility helper for signed preview URLs. If configured with `VORMIA_MEDIAFORGE_PREVIEW_MODE=proxy`, the host app can also use the proxy endpoint `GET /api/vrm/media/preview?disk=...&path=...`.

For the canonical docs, see the “S3 / Remote Disks” section in [`../README.md`](../README.md).
