# Inertia.js operations guide (adapter-agnostic)

This document describes **Inertia.js v3** behavior that is the same whether the client uses **React**, **Vue 3**, or **Svelte 5**. It complements the stack-specific conversion guides:

- [react-to-inertia-react.mdc](react-to-inertia-react.mdc)
- [react-to-inertia-vue.mdc](react-to-inertia-vue.mdc)
- [react-to-inertia-svelte.mdc](react-to-inertia-svelte.mdc)

Official entry point: [Inertia.js v3 — Introduction](https://inertiajs.com/docs/v3/getting-started/index). Topic index: [llms.txt](https://inertiajs.com/docs/llms.txt).

---

## What Inertia does (mental model)

- **Laravel (or another server adapter)** still owns **routes**, **controllers**, **auth**, **validation**, and **redirects**.
- The browser loads a **single-page shell** (Vite bundle). Subsequent navigations are **XHR visits** that return **JSON** describing the next **page** (component name + props), not full HTML documents.
- There is **no client-side router** in the SPA sense: URLs match **Laravel `web.php` (or equivalent)** routes. The client adapter swaps the active page component when Inertia receives a new page payload.

So: **server decides the page and data**; **the adapter renders it** in React, Vue, or Svelte.

---

## Versions (Vormia target)

| Layer | Package | Notes |
|--------|---------|--------|
| Server (Laravel) | `inertiajs/inertia-laravel` ^3 | PHP 8.2+, Laravel 11+ |
| Client | `@inertiajs/react` \| `@inertiajs/vue3` \| `@inertiajs/svelte` ^3 | React 19+ (React adapter), Svelte 5+ (Svelte adapter) |
| Optional tooling | `@inertiajs/vite` ^3 | Page resolution, SSR-friendly Vite integration |

---

## Laravel: returning a page

```php
use Inertia\Inertia;

// Component name maps to a file resolved by the client (glob, vite plugin, etc.)
return Inertia::render('Products/Index', [
    'products' => Product::all(),
]);
```

- The **first string** is the **page name** (often `Folder/Component` matching `Pages/Folder/Component.*` or your custom `vormia/pages` layout).
- The **array** keys become **props** on the page component (any adapter).

**Redirects** after POST/PATCH/DELETE are normal Laravel redirects; Inertia follows them as visits. See [Redirects](https://inertiajs.com/docs/v3/the-basics/redirects.md) and [Responses](https://inertiajs.com/docs/v3/the-basics/responses.md).

**Lazy / optional data (v3):** use `Inertia::optional(...)` (and related helpers like `defer`, `merge` per docs). The old `Inertia::lazy()` API was removed in v3 — see [Upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md).

---

## Root HTML template (Blade)

Inertia needs a root view that loads your JS and mounts the app.

You may use **either**:

1. **Blade directives** — `@inertiaHead` and `@inertia` (classic).
2. **v3 Blade components** — `<x-inertia::head />` and `<x-inertia::app />` ([server-side setup](https://inertiajs.com/docs/v3/installation/server-side-setup.md)).

The head component can wrap fallback `<title>` / meta for when SSR is not active.

After changing Blade or directive output, run:

```bash
php artisan view:clear
```

---

## Middleware: shared props and defaults

`HandleInertiaRequests` (publish if missing) is where you **share data on every page** (auth user, flash messages, app name, etc.):

```php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => ['user' => $request->user()],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
        ],
    ]);
}
```

See [Shared data](https://inertiajs.com/docs/v3/data-props/shared-data.md).

---

## Visits, links, and programmatic navigation (conceptual)

- **`<Link>`** (each adapter exports its own from `@inertiajs/react` / `vue3` / `svelte`) performs a visit without full page reload when possible.
- **`router.visit()`**, **`router.get/post/put/patch/delete`**, **`router.reload()`** behave the same **semantically** across adapters; only the **import path** changes.

**Partial reloads:** `router.reload({ only: ['propName'] })` (and dot-notation for nested props in v3). See [Partial reloads](https://inertiajs.com/docs/v3/data-props/partial-reloads.md).

**Cancelling visits (v3):** use `router.cancelAll()` instead of removed `router.cancel()`. See [Manual visits — cancellation](https://inertiajs.com/docs/v3/the-basics/manual-visits.md).

**Global events renamed in v3:** `invalid` → `httpException`, `exception` → `networkError`. See [Events](https://inertiajs.com/docs/v3/advanced/events.md) and the [Upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md).

---

## Forms, validation, and CSRF

- **Mutating** requests should go through Inertia visits (`useForm` / form component / `router.post` patterns per adapter) so **CSRF**, **validation errors**, and **redirects** integrate cleanly.
- Laravel returns **422** with validation errors; adapters map them into per-field error state. See [Validation](https://inertiajs.com/docs/v3/the-basics/validation.md) and [Forms](https://inertiajs.com/docs/v3/the-basics/forms.md).

**`preserveErrors`:** partial reloads can preserve validation errors when needed — see partial reloads docs.

---

## File uploads

Multipart and file inputs are supported through the same visit pipeline. See [File uploads](https://inertiajs.com/docs/v3/the-basics/file-uploads.md).

---

## Layouts (conceptual)

Persistent layouts avoid remounting shell UI (nav, layout chrome). Mechanisms differ by adapter (`Page.layout`, `defineOptions({ layout })`, `defineOptions` in Svelte, or `defaultLayout` in `createInertiaApp`). **React v3** has a specific constraint on assigning **arrow function components** directly to `.layout` — see the [Upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md) and [Layouts](https://inertiajs.com/docs/v3/the-basics/layouts.md).

**Layout props (v3):** share dynamic data between layout and pages via the documented layout-props pattern (`useLayoutProps` in adapters).

---

## SSR and Vite

- With **`@inertiajs/vite`**, SSR in **development** is streamlined (see [Server-side rendering](https://inertiajs.com/docs/v3/advanced/server-side-rendering.md)).
- **Production** still follows deployment steps for the SSR server when enabled.

---

## Configuration (`config/inertia.php`)

v3 restructured options (for example **`pages.paths`**, **`pages.extensions`**, testing flags). After upgrading or publishing:

```bash
php artisan vendor:publish --provider="Inertia\ServiceProvider" --force
```

Merge your old customizations carefully. See the [Upgrade guide — config](https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md).

---

## Authentication and authorization

Inertia does not replace Laravel auth. Protect routes with `auth` middleware, policies, and gates as usual; pass what the UI needs via props or shared data. See [Authentication](https://inertiajs.com/docs/v3/security/authentication.md) and [Authorization](https://inertiajs.com/docs/v3/security/authorization.md).

---

## Testing

Use Laravel’s testing tools plus Inertia assertions (`AssertableInertia`, etc.). Deprecated v1 testing traits were removed; follow current docs: [Testing](https://inertiajs.com/docs/v3/advanced/testing.md).

---

## Vormia package dev sandbox (`dev/resources`)

This repository’s dev shell is a concrete wiring example (React + TS in-tree):

- Blade: `dev/resources/views/app.blade.php` — Vite + `<x-inertia::head />` + `<x-inertia::app />`
- Bootstrap: `dev/resources/js/app.tsx` — `createInertiaApp` + `import.meta.glob('./vormia/pages/**/*.tsx')`
- Pages: `dev/resources/js/vormia/pages/`

Host applications often use `resources/js/Pages/` instead; both are valid if **page names** match **`resolve`** (or the Vite plugin’s `pages` config).

---

## Quick checklist (any adapter)

- [ ] `inertiajs/inertia-laravel` ^3 and one client adapter ^3 installed
- [ ] Root Blade loads Vite entry and Inertia (`@inertia*` or `<x-inertia::*>`)
- [ ] `createInertiaApp` (or Vite plugin equivalent) **resolves** page names to real modules
- [ ] `HandleInertiaRequests` registered; shared props defined as needed
- [ ] Forms use Inertia-aware submission for validation + CSRF
- [ ] After Blade/config changes: `view:clear` / republish config as documented

---

## Further reading (v3)

| Topic | Link |
|--------|------|
| How it works | [How it works](https://inertiajs.com/docs/v3/core-concepts/how-it-works.md) |
| The protocol / page object | [The protocol](https://inertiajs.com/docs/v3/core-concepts/the-protocol.md) |
| Pages | [Pages](https://inertiajs.com/docs/v3/the-basics/pages.md) |
| Links | [Links](https://inertiajs.com/docs/v3/the-basics/links.md) |
| Manual visits | [Manual visits](https://inertiajs.com/docs/v3/the-basics/manual-visits.md) |
| Title and meta | [Title & Meta](https://inertiajs.com/docs/v3/the-basics/title-and-meta.md) |
| Deferred props | [Deferred props](https://inertiajs.com/docs/v3/data-props/deferred-props.md) |
| TypeScript | [TypeScript](https://inertiajs.com/docs/v3/advanced/typescript.md) |

---

**Last updated:** May 2026
