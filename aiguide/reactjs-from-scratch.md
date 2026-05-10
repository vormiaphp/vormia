# React from scratch — how to learn and build (step by step)

This guide is for developers who are **new to React** (for example coming from PHP and Laravel) and want a **clear order of operations**: what to install, what to learn first, and how each step connects to the next.

It is **not** a full API reference. Use the official docs for details: [React — Learn](https://react.dev/learn) and [React — Reference](https://react.dev/reference/react).

---

## Who this is for

- You are comfortable with **HTML, CSS, and JavaScript** basics.
- You may know **Laravel Blade** and **PHP** but not component-based frontends.
- You want a **Vite-based** modern setup (same family of tooling Laravel uses for `npm run dev`).

---

## Mental model (read this once)

- **UI = functions that return markup.** In React these functions are called **components**. They run when React decides the screen needs updating.
- **State** is JavaScript data. When state **changes**, React **re-renders** affected components (similar in spirit to Livewire re-rendering when a property changes — but here the browser holds the state unless you sync it to a server).
- **Props** are like **constructor arguments**: the parent passes data **down**; the child should not mutate props (treat them as read-only inputs).

---

## Step 0 — Prerequisites

1. Install **Node.js** LTS (includes `npm`). Verify:

   ```bash
   node -v
   npm -v
   ```

2. Use an editor with **JSX** support (VS Code / Cursor is fine).

3. Skim [JavaScript refresher](https://developer.mozilla.org/en-US/docs/Web/JavaScript) if closures and `async/await` feel rusty — React uses them constantly.

---

## Step 1 — Create a project (Vite + React)

Official tooling path (simple, fast dev server, same ecosystem as Laravel Vite):

```bash
npm create vite@latest my-react-app -- --template react-ts
cd my-react-app
npm install
npm run dev
```

- **`react-ts`** gives you **TypeScript** from day one. You can use `react` template instead if you want plain JavaScript first.
- Open the printed **local URL** in the browser. You should see the starter page.

**What to look at in the repo:**

- `index.html` — mounts the app on a root `<div>`.
- `src/main.tsx` — calls `createRoot` and renders `<App />`.
- `src/App.tsx` — your first component to edit.

---

## Step 2 — Understand one component end to end

**Goal:** Change text on screen and see hot reload.

1. Edit `App.tsx`: change visible text, save, confirm the browser updates.
2. Notice **JSX**: HTML-like syntax inside JavaScript; `className` instead of `class`.

**PHP analogy:** One `.tsx` file is a bit like a Blade partial mixed with PHP — markup and logic live together, but the “template” is JavaScript expressions in `{}`.

---

## Step 3 — `useState`: local state

**Goal:** A button that increments a counter.

1. Read [State: A Component's Memory](https://react.dev/learn/state-a-components-memory) (short official lesson).
2. Implement `useState`:

   - Call `useState(initialValue)` at the **top level** of your component (not inside `if` / loops).
   - You get `[value, setValue]`. Always update with `setValue(new)` (or functional updates `setValue(v => v + 1)`).

**Checkpoint:** You can explain why clicking the button updates the number without reloading the page.

---

## Step 4 — Multiple components and props

**Goal:** Split UI into small pieces and pass data down.

1. Create `src/components/Greeting.tsx` that accepts a prop, e.g. `name: string`.
2. Render `<Greeting name="Ada" />` from `App`.

Read: [Passing Props to a Component](https://react.dev/learn/passing-props-to-a-component).

**Rule of thumb:** If a chunk of UI has a clear name and props, it is probably its own component.

---

## Step 5 — Lists and keys

**Goal:** Render an array as a list.

1. Use `.map()` in JSX to produce elements.
2. Give each row a stable **`key`** (usually an `id`). Keys help React reconcile updates efficiently.

Read: [Rendering Lists](https://react.dev/learn/rendering-lists).

---

## Step 6 — Effects and fetching (`useEffect`)

**Goal:** Load simple JSON when the component mounts.

1. Read [You Might Not Need an Effect](https://react.dev/learn/you-might-not-need-an-effect) first — React encourages **not** overusing effects.
2. When you *do* need sync with the outside world (e.g. `fetch` on mount), use `useEffect` with a dependency array.

**Laravel mindset:** This is closest to “run this after the view is ready” — but prefer **passing data from the server** (Inertia/Livewire/API) over fetching in the client when the page already has the data.

---

## Step 7 — Forms (controlled inputs)

**Goal:** Controlled `<input>`: value comes from state; `onChange` updates state.

Read: [Reacting to Input with State](https://react.dev/learn/reacting-to-input-with-state).

For larger forms, libraries exist later — start with plain state so you understand the pattern.

---

## Step 8 — Routing (when you need multiple pages)

A Vite React app is a **single page** until you add a router.

1. When you outgrow one screen, add **React Router** (or use a meta-framework like Next.js later).
2. Follow the current [React Router](https://reactrouter.com/) quick start for your chosen major version.

**Laravel analogy:** Routes in `web.php` map URLs to controllers; the router maps URLs to **components**.

---

## Step 9 — Styling

Pick one approach and stay consistent:

- **Plain CSS** / CSS modules (Vite supports CSS import).
- **Tailwind CSS** — if you use Tailwind 4 elsewhere, add it per [Tailwind + Vite docs](https://tailwindcss.com/docs/installation/using-vite).

---

## Step 10 — TypeScript (if you used `react-ts`)

- Define **prop types** with `type Props = { title: string }` or `interface`.
- Let the compiler teach you — fix red squiggles as you go.

Official: [TypeScript with React](https://react.dev/learn/typescript) (React docs overview).

---

## Step 11 — Production build

```bash
npm run build
npm run preview   # optional: test the production build locally
```

Understand that `npm run dev` is for development; CI and hosting use **`build`** output.

---

## Common pitfalls (from scratch)

1. **Mutating state in place** — e.g. pushing to an array without `setState` / setter — React may not re-render. Prefer immutable updates or functional updates.
2. **Hooks rules** — only call hooks at the top level of React components or custom hooks (see [Rules of Hooks](https://react.dev/reference/rules/rules-of-hooks)).
3. **Fetching for data you already have server-side** — in Laravel + **Inertia**, pass props from the controller instead of duplicating with `fetch` on first paint.

---

## Connecting to a Laravel / Vormia stack

When you join a Laravel monolith:

- **Inertia + React:** server still owns routes; pages are React components fed by Laravel. See [inertia/inertiajs-operations.md](inertia/inertiajs-operations.md) and [inertia/react-to-inertia-react.mdc](inertia/react-to-inertia-react.mdc).
- **Livewire:** different model (server-rendered HTML + Alpine-style interactivity) — stay in Livewire guides if that is the stack.

---

## Official documentation (bookmark these)

| Topic | Link |
|--------|------|
| Learn path | [react.dev/learn](https://react.dev/learn) |
| Hooks & API | [react.dev/reference/react](https://react.dev/reference/react) |
| React DOM (createRoot, etc.) | [react.dev/reference/react-dom](https://react.dev/reference/react-dom) |

---

**Last updated:** May 2026
