# Svelte 5 from scratch — how to learn and build (step by step)

This guide is for developers who are **new to Svelte** (for example coming from PHP and Laravel) and want a **clear order of operations**: what to install, what to learn first, and how each step connects to the next.

Svelte **5** uses **runes** (`$state`, `$props`, `$derived`, `$effect`) as the default mental model. It is **not** the same as Svelte 3/4 “`export let` + `$:`” style — start with runes if you are learning from scratch.

Official hub: [Svelte — Overview](https://svelte.dev/docs/svelte/overview) and [What are runes?](https://svelte.dev/docs/svelte/what-are-runes).

---

## Who this is for

- You are comfortable with **HTML, CSS, and JavaScript** basics.
- You may know **Laravel Blade** — Svelte files look like enhanced HTML with a `<script>` block.
- You want a **Vite-based** workflow (same family as Laravel’s Vite integration).

---

## Mental model (read this once)

- Svelte is a **compiler**: you write `.svelte` files; the build outputs efficient JavaScript.
- **Runes** are explicit markers (`$state`, etc.) so the compiler knows what is reactive.
- There is **no virtual DOM** like React; Svelte updates the real DOM surgically.

---

## Step 0 — Prerequisites

1. Install **Node.js** LTS. Verify:

   ```bash
   node -v
   npm -v
   ```

2. Be comfortable with **arrow functions** and **template literals** — Svelte examples use them often.

---

## Step 1 — Create a project (Vite + Svelte + TypeScript)

```bash
npm create vite@latest my-svelte-app -- --template svelte-ts
cd my-svelte-app
npm install
npm run dev
```

Open the dev URL. Edit `src/App.svelte` and confirm hot reload.

**What to notice:**

- A `.svelte` file combines **`<script>`** (logic) and markup (no separate template string layer like JSX).

---

## Step 2 — Runes first: `$state` and events

**Goal:** A counter button.

1. Read [What are runes?](https://svelte.dev/docs/svelte/what-are-runes) (short).
2. In `<script>`, declare reactive state:

   ```svelte
   <script>
     let count = $state(0);
   </script>

   <button onclick={() => count++}>
     Count: {count}
   </button>
   ```

**PHP analogy:** `$state(0)` is like a **reactive property** — when it changes, the UI updates automatically, similar in *feel* to Livewire updating the DOM when a public property changes (implementation is completely different under the hood).

**Svelte 5 note:** event attributes are **`onclick`**, not `onClick` (lowercase, HTML-style).

---

## Step 3 — `$props()` for inputs from parent

**Goal:** Parent passes `title`; child displays it.

```svelte
<script>
  let { title } = $props();
</script>

<h2>{title}</h2>
```

Read: [$props](https://svelte.dev/docs/svelte/$props).

---

## Step 4 — `$derived` for computed values

**Goal:** Show `total = price * quantity` without storing a duplicate “manual” variable.

Read: [$derived](https://svelte.dev/docs/svelte/$derived).

**Laravel analogy:** A computed column in a query view vs storing redundant state — `$derived` recalculates when its dependencies change.

---

## Step 5 — `$effect` for side effects (use sparingly at first)

**Goal:** Update `document.title` when `count` changes.

Read: [$effect](https://svelte.dev/docs/svelte/$effect).

**Important:** Prefer **`$derived`** for values derived from state. Use **`$effect`** for side effects (logging, subscriptions, DOM sync that is not pure calculation).

Also read the official cautions: many “I used to use `useEffect` for everything” patterns move to **`$derived`** or event handlers in Svelte.

---

## Step 6 — Lists with `{#each}`

**Goal:** Render an array with a keyed each-block.

```svelte
{#each items as item (item.id)}
  <p>{item.name}</p>
{/each}
```

Read: [each blocks](https://svelte.dev/docs/svelte/each).

---

## Step 7 — Forms and `bind:`

**Goal:** Two-way bind an input to `$state`.

Read: [`bind:`](https://svelte.dev/docs/svelte/bind).

---

## Step 8 — Snippets and `{@render}` (Svelte 5 composition)

When you outgrow simple props, Svelte 5 uses **snippets** (reusable template fragments). This replaces many older “slot” patterns for new code.

Read: [Snippets](https://svelte.dev/docs/svelte/snippet) and [`{@render}`](https://svelte.dev/docs/svelte/@render).

---

## Step 9 — Fetching data

**Goal:** On mount, load JSON into `$state`.

Use `$effect` **or** the `{#await}` block for promises — pick one style and stay consistent in small apps.

**Laravel + Inertia note:** Prefer server-provided props over client `fetch` for initial page data when using Inertia.

---

## Step 10 — Routing and “full apps”

- **Vite + Svelte** gives you a **single page** shell.
- For file-based routing, layouts, and SSR at scale, the usual next step is **SvelteKit** ([kit.svelte.dev](https://kit.svelte.dev/docs)).

Do **not** jump to SvelteKit on day one unless you already know you need SSR and multi-route architecture.

---

## Step 11 — Styling

- **`<style>` in `.svelte` files** is scoped by default (great for components).
- Or integrate **Tailwind CSS** with Vite per [Tailwind docs](https://tailwindcss.com/docs/installation/using-vite) if that is your standard (Tailwind 4).

---

## Step 12 — Production build

```bash
npm run build
npm run preview
```

Understand `dist/` as static output unless you adopt SvelteKit’s adapter model.

---

## Common pitfalls (from scratch)

1. **Mixing Svelte 4 tutorials with Svelte 5** — runes vs `export let` / `$:` confuse newcomers; stick to **Svelte 5 docs** linked above.
2. **Overusing `$effect`** — reach for `$derived` first when output is purely computed from state.
3. **Forgetting keyed `{#each}`** when lists reorder — same idea as React `key` / Vue `:key`.

---

## Connecting to a Laravel / Vormia stack

- **Inertia + Svelte:** Laravel owns routes; Svelte components are pages. See [inertia/inertiajs-operations.md](inertia/inertiajs-operations.md) and [inertia/react-to-inertia-svelte.mdc](inertia/react-to-inertia-svelte.mdc).

---

## Official documentation (bookmark these)

| Topic | Link |
|--------|------|
| Overview | [svelte.dev/docs/svelte/overview](https://svelte.dev/docs/svelte/overview) |
| Runes | [svelte.dev/docs/svelte/what-are-runes](https://svelte.dev/docs/svelte/what-are-runes) |
| Template syntax | [svelte.dev/docs/svelte/basic-markup](https://svelte.dev/docs/svelte/basic-markup) |
| SvelteKit (later) | [kit.svelte.dev/docs](https://kit.svelte.dev/docs) |

---

**Last updated:** May 2026
