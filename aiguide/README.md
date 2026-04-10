# AI Conversion Guides

This folder contains `.mdc` AI-oriented conversion guides for transforming between React and various Laravel/Next.js stacks. Each guide uses `alwaysApply: true` so Cursor and compatible AI assistants automatically reference it when working in the described context.

## Available Guides

| File | Conversion Track |
|------|-----------------|
| [`react-to-laravel-livewire-inline.mdc`](react-to-laravel-livewire-inline.mdc) | React → Laravel Blade + Livewire Volt (inline/non-class components) |
| [`react-to-inertia-react.mdc`](react-to-inertia-react.mdc) | React → Inertia.js with React adapter |
| [`react-to-inertia-vue.mdc`](react-to-inertia-vue.mdc) | React → Inertia.js with Vue 3 adapter |
| [`react-to-inertia-svelte.mdc`](react-to-inertia-svelte.mdc) | React → Inertia.js with Svelte 5 adapter |
| [`laravel-to-nextjs-tanstack.mdc`](laravel-to-nextjs-tanstack.mdc) | Laravel backend → Next.js (App Router) + TanStack Query |
| [`laravel-to-react.mdc`](laravel-to-react.mdc) | Laravel monolith → Standalone React SPA (with Laravel JSON API) |
| [`react-to-expo-react-native.mdc`](react-to-expo-react-native.mdc) | React (web) → Expo React Native (core primitives) |
| [`react-to-expo-react-native-gluestack.mdc`](react-to-expo-react-native-gluestack.mdc) | React (web) → Expo React Native + GlueStack UI v2 |

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

- [`../references/react-laravel.mdc`](../references/react-laravel.mdc) — original React → Blade/Livewire Volt (class-style) guide

## Vormia MediaForge Note (S3 / Remote Disks)

When host-app code uses `VormiaPHP\Vormia\Facades\MediaForge`, the `->run()` method returns a **string**:

- If the configured Laravel disk supports `url()`, it returns a **URL string** (often `https://{bucket}.s3.../{key}` or your `AWS_URL` / CloudFront URL).
- If `url()` can’t be generated (or throws), it returns the **storage path/key** (for example `uploads/products/2026/abc.webp`).

For the canonical docs, see the “S3 / Remote Disks” section in [`../README.md`](../README.md).
