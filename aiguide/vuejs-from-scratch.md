# Vue 3 from scratch — how to learn and build (step by step)

This guide is for developers who are **new to Vue** (for example coming from PHP and Laravel) and want a **clear order of operations**: what to install, what to learn first, and how each step connects to the next.

It is **not** a full API reference. Use the official docs: [Vue.js — Introduction](https://vuejs.org/guide/introduction.html) and [Quick Start](https://vuejs.org/guide/quick-start.html).

---

## Who this is for

- You are comfortable with **HTML, CSS, and JavaScript** basics.
- You may know **Laravel Blade** and **PHP** but not Single-File Components (SFCs).
- You want a **Vite-based** workflow (same tooling family as Laravel’s frontend).

---

## Mental model (read this once)

- A **`.vue` file** usually has **`<script>`**, **`<template>`**, and optional **`<style scoped>`** — like Blade + PHP + CSS in one file, but the script is JavaScript.
- **Reactivity:** plain values wrapped with **`ref()`** or objects with **`reactive()`** automatically update the DOM when they change (no manual “re-render” calls).
- **`<script setup>`** (recommended) is the concise way to use the **Composition API** — variables and functions you declare are directly usable in the template.

---

## Step 0 — Prerequisites

1. Install **Node.js** LTS. Verify:

   ```bash
   node -v
   npm -v
   ```

2. Skim [MDN JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript) if modules (`import` / `export`) and arrow functions are unfamiliar.

---

## Step 1 — Create a project (official scaffolding)

Use Vue’s official project creator (wraps Vite):

```bash
npm create vue@latest
```

- Answer prompts: enable **TypeScript** if you want types from the start (recommended for new apps).
- Then:

  ```bash
  cd your-project-name
  npm install
  npm run dev
  ```

**What you get:**

- Hot module replacement (fast refresh while editing).
- Suggested folder layout (`src/components`, `src/views`, etc.) depending on template.

---

## Step 2 — Read “Introduction” once through

Goal: big-picture vocabulary — **SFC**, **Options API vs Composition API**, **progressive** adoption.

- [Vue.js — Introduction](https://vuejs.org/guide/introduction.html)

You do **not** need to memorize everything; you will revisit pages as you code.

---

## Step 3 — Your first `.vue` component

**Goal:** Edit the starter view and see the browser update.

1. Find the main view (often `App.vue` or a routed view).
2. Change static text in `<template>`.
3. Add a **`<script setup>`** block with:

   ```ts
   import { ref } from 'vue'
   const message = ref('Hello')
   ```

4. Use `{{ message }}` in the template — then change `message` from a button `@click` handler.

Read: [Reactivity Fundamentals](https://vuejs.org/guide/essentials/reactivity-fundamentals.html) (focus on `ref` first).

**PHP analogy:** `ref('Hello')` is a bit like a **small object** holding a value; in `<script setup>` you use `.value` in **script** but **not** in the template (Vue unwraps refs in templates for you).

---

## Step 4 — Props, emits, and child components

**Goal:** Parent passes data down; child notifies parent with events.

1. Create `src/components/ChildCard.vue`.
2. Use `defineProps<{ title: string }>()` (TypeScript) or `defineProps({ title: String })`.
3. Use `defineEmits<{ (e: 'select', id: number): void }>()` and `emit('select', id)` from a button.

Read: [Props](https://vuejs.org/guide/components/props.html) and [Component Events](https://vuejs.org/guide/components/events.html).

---

## Step 5 — Lists with `v-for`

**Goal:** Render collections with stable **`:key`**.

Read: [List Rendering](https://vuejs.org/guide/essentials/list.html).

**Laravel analogy:** `@foreach` in Blade → `v-for` in Vue; `:key` is like choosing a stable identifier so diffing is efficient.

---

## Step 6 — Forms and `v-model`

**Goal:** Two-way binding on inputs.

Read: [Form Input Bindings](https://vuejs.org/guide/essentials/forms.html).

For larger forms, you may later use a form library — start with `v-model` on a few fields first.

---

## Step 7 — Fetching data (and when not to)

**Goal:** `onMounted` + `fetch`, store result in `ref`.

Read: [Lifecycle Hooks — onMounted](https://vuejs.org/api/composition-api-lifecycle.html#onmounted).

**Important:** In **Laravel + Inertia**, page data usually comes from the **controller as props** — avoid duplicating “load on mount” for data the server already sent.

---

## Step 8 — Routing (Vue Router)

When you need multiple URLs in the SPA:

1. Add **Vue Router** per the official [Routing guide](https://router.vuejs.org/).
2. Define routes → components; use `<RouterView />` in the shell layout.

**Laravel analogy:** `routes/web.php` → router table; each route points to a “page component” instead of a controller returning a Blade view.

---

## Step 9 — Shared client state (optional, later)

When many distant components need the same client state:

- Start with **provide / inject** for medium complexity.
- Graduate to **Pinia** when you outgrow that (official store).

Read: [Pinia](https://pinia.vuejs.org/) when you reach that point — not required on day one.

---

## Step 10 — Styling

- **Scoped styles** in `.vue` files keep CSS local.
- Or add **Tailwind CSS** per [Tailwind + Vite](https://tailwindcss.com/docs/installation/using-vite) if that is your house standard (Tailwind 4).

---

## Step 11 — Production build

```bash
npm run build
```

Inspect `dist/` output. Deploy that folder to static hosting, or let Laravel/Vite integrate handle assets in a monolith.

---

## Common pitfalls (from scratch)

1. **Forgetting `.value`** on `ref` in `<script>` (not in template) — TypeScript and the compiler usually remind you.
2. **Mutating props** — treat props as read-only; emit events upward to change parent state.
3. **Missing `:key` on `v-for`** — causes subtle UI bugs when lists reorder.

---

## Connecting to a Laravel / Vormia stack

- **Inertia + Vue:** Laravel routes + `Inertia::render`; Vue pages live under your Vite `resolve` path. See [inertia/inertiajs-operations.md](inertia/inertiajs-operations.md) and [inertia/react-to-inertia-vue.mdc](inertia/react-to-inertia-vue.mdc) (React → Vue migration patterns if you think in React today).

---

## Official documentation (bookmark these)

| Topic | Link |
|--------|------|
| Introduction | [vuejs.org/guide/introduction.html](https://vuejs.org/guide/introduction.html) |
| Quick Start | [vuejs.org/guide/quick-start.html](https://vuejs.org/guide/quick-start.html) |
| `<script setup>` | [vuejs.org/api/sfc-script-setup.html](https://vuejs.org/api/sfc-script-setup.html) |
| Composition API FAQ | [vuejs.org/guide/extras/composition-api-faq.html](https://vuejs.org/guide/extras/composition-api-faq.html) |

---

**Last updated:** May 2026
