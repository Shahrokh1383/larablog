```markdown
# Layouts & Templates

## Overview

The application uses two primary Blade layout files—one for the public-facing blog and one for the administration area—along with shared sidebar partials. These layouts enforce a consistent HTML structure, asset loading strategy, and navigation pattern across all pages.

The **public layout** (`app.blade.php`) provides a responsive Bootstrap 5.3 RTL-enabled interface with a dark navbar, main content area, footer, and support for floating flash messages. It is designed for blog readers, authors, and authenticated users accessing their dashboard or profile.

The **admin layout** (`admin.blade.php`) serves the role‑based administration panel. It uses a standard LTR Bootstrap setup with a sticky topbar, a collapsible sidebar, and a dedicated content area. Navigation is strictly controlled by role (`Admin`, `Editor`) and permissions, displaying only the links that the authenticated user is authorized to access.

Both layouts rely on Vite for loading custom CSS and JavaScript, and they extend the Bootstrap ecosystem via CDN for core framework files.

---

## File List

| File | Role |
|------|------|
| `resources/views/layouts/app.blade.php` | Public master layout; RTL Bootstrap, navbar, main container, footer, flash messages. |
| `resources/views/layouts/admin.blade.php` | Admin master layout; LTR Bootstrap, topbar, sidebar include, breadcrumbs, error/success handling. |
| `resources/views/partials/_sidebar.blade.php` | Public sidebar widget area (about, admin quick access, pending comments, categories, tags, stats) used on various front‑end pages. |
| `resources/views/admin/partials/_sidebar.blade.php` | Admin sidebar navigation; role‑gated links, mobile profile, link to public site, logout button. |
| `vite.config.js` | Vite configuration; defines build inputs (CSS/JS) and activates the Laravel plugin with HMR refresh. |
| `package.json` | Lists development dependencies and the `dev`/`build` scripts for asset compilation. |

---

## View Hierarchy & Inheritance

The application does **not** use Blade `@extends` from a base layout to another layout (i.e., a three‑level hierarchy). Instead, each full page view directly extends either `app` or `admin` and fills the defined sections.

**Public pages** extend `layouts.app`:

```
layouts.app
├── @yield('title')            (page title suffix)
├── @stack('styles')           (extra CSS pushed by child views)
├── Navbar (inline)
├── Flash messages (inline)
├── @yield('content')          (main page content)
├── Footer (inline)
└── @stack('scripts')          (extra JS pushed by child views)
```

**Admin pages** extend `layouts.admin`:

```
layouts.admin
├── @yield('title')
├── @stack('styles')
├── Topbar (inline)
├── @include('admin.partials._sidebar')
├── Flash messages & validation errors (inline)
├── @yield('breadcrumbs')
├── @yield('page-title')
├── @yield('page-actions')
├── @yield('content')
└── @stack('scripts')
```

There is no shared layout that both `app` and `admin` inherit from; they are completely independent.

---

## Layout & Partial Descriptions

### 1. `resources/views/layouts/app.blade.php`

**Path and Purpose**  
The public master layout. Renders the HTML skeleton for all visitor‑facing pages, the user dashboard, and author profile sections. It uses Bootstrap 5.3 RTL (right‑to‑left) by loading the `bootstrap.rtl.min.css` stylesheet, making the site LTR‑in‑RTL ready (the content direction is set implicitly by the CSS; no `dir` attribute on `<html>` so it remains LTR but leverages RTL component styling). The body background and navbar use CSS custom properties (`--background`, `--sidebar`) defined in the compiled CSS.

**Data Dependencies**  
- `auth()->user()` – Used in the navbar dropdown for avatar (`avatar_url`), display name, and role checks (`@role`, `@can`).  
- `session('success')`, `session('error')`, `session('status')` – Flash data for floating messages.  
- `request()->routeIs(...)` – Determines active navigation classes.  
- Route names expected: `home`, `posts.index`, `authors.index`, `dashboard`, `admin.dashboard`, `admin.posts.index`, `login`, `register`, `author.show`, `author.edit`, `posts.create`, `logout`.  
- Model policies for `App\Models\Post` (e.g., `create`).  
- Spatie’s `@role` directive for roles `Admin`, `Editor`, `Author`.

**Blade Directives Used**  
- `@yield('title', 'LaraBlog')` – Page title suffix.  
- `@stack('styles')` – Additional stylesheets from child views.  
- `@auth`, `@guest`, `@role`, `@can` – Auth and role‑based navigation rendering.  
- `@if(session(...))` – Flash message display.  
- `@csrf` – Inside logout form.

**Included Partials**  
None; all structural elements (navbar, footer) are inline.

**Conditional Rendering**  
- **Guest vs. Authenticated**: The right‑side navbar shows Login/Register links when guest, otherwise a user dropdown.  
- **Role‑based Admin Links**: Only users with `Admin` role see a link to the full admin dashboard; `Editor` sees a direct link to Post Manager.  
- **Dropdown Content**: Authors/Editors/Admins get links to Public Profile and Advanced Settings; regular users see only a “My Account” link to the dashboard.  
- **Post Creation**: The “Create Post” link appears only if the user has the `create` policy for `Post`.  
- **Active State**: Navigation links receive an `active` class based on `request()->routeIs(...)`.

**Navigation & Links**  
- **Home**: `route('home')`  
- **Posts**: `route('posts.index')`  
- **Authors**: `route('authors.index')`  
- **Dashboard**: `route('dashboard')`  
- **Admin Panel**: `route('admin.dashboard')` (Admin) / `route('admin.posts.index')` (Editor)  
- **Login/Register**: `route('login')`, `route('register')`  
- **Logout**: POST to `route('logout')` using a form.  
- **User Dropdown**: `route('author.show', auth()->user())`, `route('author.edit')`, `route('posts.create')`

**Styling Notes**  
- Inline `style` tags set the body background to `var(--background)` and navbar/footer to `var(--sidebar)`. These custom properties are likely defined in `resources/css/app.css` (or a related file) and compiled via Vite.  
- Navbar uses `navbar-dark` with a shadow.  
- Flash messages use classes `flash-container`, `flash-card`, `flash-success`, `flash-error`, `flash-info`—style definitions are in the custom CSS bundle.  
- The user avatar uses `rounded-circle`, `border`, and `object-fit: cover`.

---

### 2. `resources/views/layouts/admin.blade.php`

**Path and Purpose**  
The administration master layout. Provides the shell for all `/admin/*` pages. Unlike the public layout, it uses Bootstrap LTR (`bootstrap.min.css`) and a distinct set of CSS custom properties (`--admin-bg`, `--admin-topbar`). It implements a collapsible sidebar (via Bootstrap grid classes and a toggle button visible only on small screens) and a sticky topbar.

**Data Dependencies**  
- `auth()->user()` – Name, email, avatar, role (for topbar brand link and sidebar profile).  
- `session('success')`, `session('error')`, `$errors` (from validation) – Flash and validation error rendering.  
- `request()->routeIs(...)` – Active state for sidebar links.  
- Route names: `admin.dashboard`, `admin.posts.index`, `admin.categories.index`, `admin.tags.index`, `admin.comments.pending`, `admin.users.index`, `author.show`, `dashboard`, `home`, `logout`.  
- Model counts: `App\Models\Post::count()`, `App\Models\Category::count()`, `App\Models\Tag::count()`, `App\Models\User::count()`, pending comments count.  
- Laravel version via `app()->version()` (displayed in footer).

**Blade Directives Used**  
- `@yield('title', 'Admin Panel')`  
- `@stack('styles')`  
- `@yield('breadcrumbs')` – For admin breadcrumb navigation.  
- `@yield('page-title')`, `@yield('page-actions')` – Content area header and action buttons.  
- `@yield('content')`  
- `@stack('scripts')`  
- `@include('admin.partials._sidebar')` – Sidebar injection.  
- `@if($errors->any())` – Validation error block.  
- `@auth`, `@role`, `@can` used inside the included sidebar (but not directly in the layout file).  
- Conditional logo link based on `@if(auth()->user()->hasRole('Admin'))`.

**Included Partials**  
- `admin.partials._sidebar` – The sidebar navigation, passed no explicit data; it relies on the same global session and auth data.

**Conditional Rendering**  
- **Topbar Brand Link**: Admins go to `admin.dashboard`, Editors to `admin.posts.index`, others to `dashboard`.  
- **Desktop/Mobile Profile**: The desktop topbar shows a profile dropdown (`d-none d-lg-block`); the mobile profile is rendered inside the sidebar (`d-lg-none`).  
- **Flash Messages**: Success, error, and validation errors are displayed in the same floating card design; validation errors additionally list each message in a `<ul>`.  
- **Breadcrumbs**: The `@yield('breadcrumbs')` section is empty by default; child views may fill it with a Bootstrap breadcrumb.

**Navigation & Links**  
All navigation links beyond the topbar are delegated to the sidebar partial (documented next).

**Styling Notes**  
- Layout uses Bootstrap’s grid: sidebar takes `col-lg-3 col-xl-2`, content `col-lg-9 col-xl-10`.  
- A `sidebar-overlay` div exists for mobile background when sidebar is open (likely controlled by `admin.js`).  
- The sidebar toggle button `#sidebarToggle` is visible only on `d-lg-none`.  
- The footer background uses `var(--admin-topbar)`.

---

### 3. `resources/views/partials/_sidebar.blade.php`

**Path and Purpose**  
A public‑facing sidebar component intended to be included by various front‑end pages (e.g., `posts/index`, `authors/index`). It provides an “About” widget, an admin quick‑access section, a pending comments alert, a categories list with post counts, a tags cloud, and blog statistics.

**Data Dependencies**  
- **No injected variables**; it directly queries the database using Eloquent:  
  - `App\Models\Category::withCount('posts')->get()` – Categories and their post counts.  
  - `App\Models\Tag::withCount('posts')->get()` – Tags and post counts.  
  - `App\Models\Comment::where('approved', false)->count()` – Pending comments (inside `@role('Admin')` block).  
  - `App\Models\Post::published()->count()` – Total published posts for stats.  
  - `App\Models\Category::count()`, `App\Models\Tag::count()` for quick‑access badge counts.  
- Route names: `route('admin.dashboard')`, `route('admin.posts.index')`, `route('admin.categories.index')`, `route('admin.comments.pending')`, `route('categories.show', $category->slug)`, `route('tags.show', $tag->slug)`.

**Blade Directives Used**  
- `@can('view-admin-panel')` – Entire “Admin Quick Access” block.  
- `@role('Admin')` – Restricts dashboard and taxonomy management links to Admins.  
- `@can('approve', App\Models\Comment::class)` – Pending comments alert only for users allowed to approve.  
- `@php` blocks for queries and counts.  
- `@foreach` for categories and tags.

**Included Partials**  
None; a standalone widget component.

**Conditional Rendering**  
- The admin quick‑access section appears only when `@can('view-admin-panel')` passes.  
- Inside it, the Dashboard, Categories, and Tags links are further restricted to `@role('Admin')`, while the Posts link is visible to both Admin and Editor.  
- The pending comments alert is strictly for Admins who can approve comments.  
- If no categories or tags exist, those widgets are hidden.

**Styling Notes**  
- Uses classes like `sidebar-card`, `sidebar-card-header`, `list-group-flush`, `animated-list-item`.  
- Admin quick‑access links have badge counts using Bootstrap’s `badge` classes.  
- Pending comments alert has class `pending-comments-alert`.  
- Tags are rendered as `.enhanced-badge` elements.  
- These styles are defined in `resources/css/sidebar.css` (included via Vite in the public layout and likely the admin layout as well).

---

### 4. `resources/views/admin/partials/_sidebar.blade.php`

**Path and Purpose**  
The dedicated navigation sidebar for the admin panel. It offers a vertical `nav-pills` UI with role‑gated links, a mobile‑only user profile, and a logout button. It is included in `layouts.admin` inside the `sidebar-wrapper` div.

**Data Dependencies**  
- `auth()->user()` – Name, email, avatar.  
- `request()->routeIs(...)` – Active link styling.  
- Model counts: `App\Models\Post::count()`, `App\Models\Category::count()`, `App\Models\Tag::count()`, `App\Models\User::count()`, pending comment count via `App\Models\Comment::where('approved', false)->count()`.  
- Routes: `admin.dashboard`, `admin.posts.*`, `admin.categories.*`, `admin.tags.*`, `admin.comments.pending`, `admin.users.*`, `home`, `logout`.

**Blade Directives Used**  
- `@role('Admin')` – Affects visibility of Dashboard, Categories, Tags, Comments, Users links.  
- `@can('manage users')` – Wraps the Users link (Admin only, further permission check).  
- `@php` for pending comment count.  
- `@if($pending > 0)` – Conditional badge on Comments link.

**Included Partials**  
None.

**Conditional Rendering**  
- **Mobile vs. Desktop**: The mobile profile block (`d-lg-none`) shows user info and buttons; on desktop, the sidebar shows only the navigation list and a “View Site” link.  
- **Role‑specific links**: Dashboard, Categories, Tags, Comments, Users are visible only to `Admin`. Posts link is visible to both Admin and Editor (no `@role` guard).  
- **Pending badge**: Appears on the Comments link only if there is at least one unapproved comment.  
- **Users link**: Requires both `@role('Admin')` and `@can('manage users')`.  
- A close button (`#sidebarCloseBtn`) appears only on mobile (`d-lg-none`).

**Styling Notes**  
- The sidebar is wrapped in `.admin-sidebar` and uses `nav-pills flex-column`.  
- Links are styled as `nav-link` with `active` state driven by `request()->routeIs(...)`.  
- The mobile profile area has a semi‑transparent background (`rgba(255,255,255,0.05)`) and rounded corners.  
- The logout button is rendered as a submit button inside a form, but styled like a nav link (classes `nav-link text-start w-100`).  
- Custom CSS for sidebar appearance is managed by `resources/css/admin.css` (loaded via Vite in the admin layout).

---

## Asset Loading & Vite Integration

All custom application CSS and JavaScript are bundled and served through **Laravel Vite**. The configuration is defined in `vite.config.js` and the build/dev scripts in `package.json`.

### Vite Configuration

File: `vite.config.js`
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/sidebar.css',
                'resources/css/post.css',
                'resources/css/dashboard.css',
                'resources/css/author.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
```

Key points:
- The plugin `laravel-vite-plugin` handles asset manifest generation and HMR.  
- **Inputs** define six entry points (five CSS files, one JS file). Note that `resources/js/admin.js` and `resources/css/admin.css` are **not** listed here. They are imported separately in the admin layout using a separate `@vite` call. The Vite plugin will automatically detect additional input files if they are referenced via `@vite` in Blade templates and add them to the build.  
- `refresh: true` enables full‑page reload on file changes during development.

### How Layouts Load Assets

- **Public layout** (`app.blade.php`):  
  ```blade
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/sidebar.css', 'resources/css/post.css', 'resources/css/dashboard.css', 'resources/css/author.css'])
  ```
  This single directive loads all public‑facing CSS and the main JavaScript bundle.

- **Admin layout** (`admin.blade.php`):  
  ```blade
  @vite('resources/css/admin.css')
  ...
  @vite('resources/js/admin.js')
  @vite('resources/js/app.js')
  ```
  The admin layout loads its own CSS (`admin.css`), its own JavaScript (`admin.js`), and also the global `app.js` (which likely contains shared utilities like Bootstrap initialisation). The separate `@vite` calls for JS are placed before the `@stack('scripts')` so that inline scripts pushed by child views run after the main bundles.

- **Bootstrap CDN**: Both layouts include Bootstrap 5.3 CSS and JS from `jsdelivr.net` (RTL for public, standard LTR for admin) and Bootstrap Icons. This relieves the Vite bundle from having to compile Bootstrap, keeping build times fast.

### Build Scripts (`package.json`)

```json
"scripts": {
    "build": "vite build",
    "dev": "vite"
}
```

- `vite` starts the development server with HMR.  
- `vite build` generates production assets in `public/build/`. The output manifest (`public/build/manifest.json`) is consumed by Laravel’s `@vite` directive.

### Deferred/Async Loading

There is no explicit `defer` or `async` on the Vite‑injected `<script>` tags because Laravel Vite by default adds `type="module"` (which is deferred automatically). The CDN Bootstrap bundle is loaded synchronously via a regular `<script>` tag (no `defer` attribute; placed before `</body>` to avoid render‑blocking). No further lazy‑loading strategy is implemented.

---

## Integration with Backend

The layouts rely on several backend mechanisms to display dynamic data:

- **Authentication State** (`auth()->user()`): Provided by Laravel’s session‑based auth. Accessed directly in Blade via the `auth()` helper.  
- **User Role & Permissions**: The `@role` and `@can` directives are powered by the **Spatie Laravel Permission** package. The user model uses the `HasRoles` trait. This determines the visibility of admin links, the “Create Post” button, and sidebar sections.  
- **User Avatar**: Each user has an `avatar_url` attribute (likely an accessor that returns a stored image URL or a default placeholder). The backend source is probably the `App\Models\User` model; no service provider or view composer is evident.  
- **Flash Messages**: Controllers are expected to flash `success`, `error`, or `status` to the session. These are displayed as floating cards in both layouts. Admin layout additionally catches validation errors via `$errors` (from `Illuminate\Support\ViewErrorBag`).  
- **Model Counts** (Dashboard Stats, Sidebar Badges): Several partials use `App\Models\...::count()` directly in Blade. This couples the views to the database; there is no view composer or service provider injecting these values. The queries run on every request to those pages.  
- **Pending Comments**: `App\Models\Comment::where('approved', false)->count()` is used in both the public sidebar and the admin sidebar. The gate `approve` on `Comment` model determines who can see the alert.  
- **Route Model Binding**: The public sidebar links to `route('categories.show', $category->slug)` and `route('tags.show', $tag->slug)` assume route model binding by slug is configured. The admin sidebar uses standard resource routes.  
- **Date/Version**: Footer in admin uses `app()->version()` to display Laravel version; footer in app layout uses a hardcoded “2026”.  

No custom view composer, service provider, or config file for assets was provided; the layouts depend solely on global helpers and Eloquent calls.

---

## Edge Cases & UI States

The layouts handle a variety of states gracefully:

- **Guest Users (Public Layout)**  
  - Navbar shows Login and Register links with active‑state support.  
  - No user dropdown or admin links are rendered.  
  - `@auth` sections are skipped entirely.  
  - The public sidebar may still render (if included on a page) because it does not rely on auth except for the admin quick‑access section, which is hidden via `@can('view-admin-panel')` (guests will not pass this check).

- **Missing User Avatar**  
  If a user has no avatar, the `avatar_url` accessor is expected to return a default image path or a placeholder. There is no additional fallback logic in Blade (e.g., an `onerror` attribute). This could result in a broken image; it is assumed the backend always provides a valid URL.

- **Empty Navigation (Admin Sidebar for Editors)**  
  Editors only see the Posts link and the common items (View Site, Logout). No empty states exist; the sidebar adapts to the limited set of links.

- **Validation Errors (Admin Layout)**  
  When `$errors->any()` is true, a flash card lists all errors in a bulleted list. This complements the usual inline field errors that child views may display.

- **Flash Messages**  
  Both layouts include a close button (`flash-close`) that uses inline JavaScript (`onclick="this.closest('.flash-card').remove()"`) to dismiss the message without a page reload. No automatic timeout is set.

- **Empty Categories/Tags (Public Sidebar)**  
  If no categories or tags exist, the corresponding widgets are not rendered at all, avoiding empty boxes.

- **Mobile/Tablet Admin Sidebar**  
  - The sidebar is hidden off‑canvas by default on small screens; toggled by the `#sidebarToggle` button.  
  - A close button is provided inside the sidebar.  
  - A mobile profile section displays user details directly in the sidebar, so the user does not need to access the topbar dropdown.  
  - The sidebar overlay (`#sidebarOverlay`) is present for background dimming; behavior is likely handled by `admin.js`.

- **No Breadcrumbs**  
  If a child view does not provide a `@section('breadcrumbs')`, nothing is rendered, avoiding an empty whitespace.

- **Authentication Checks for Logout**  
  The logout form is only visible when the user is authenticated. This is inherent because the entire dropdown/button is inside `@auth`/`@else` blocks.

- **Active State on Navigation**  
  Both layouts use `request()->routeIs()` patterns for active links. If a route name changes, the active highlighting will silently break without error—this is a maintenance consideration.

---

```