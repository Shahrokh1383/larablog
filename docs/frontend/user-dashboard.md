---

```markdown
# User Dashboard (Non‑Admin) – Frontend Module

## Overview
The User Dashboard is the personal hub for any authenticated, non‑admin user.
It provides a read‑only profile summary, an editable profile card, quick‑action links based on roles/permissions, and (for content creators) a small stats block.
The page is fully responsive and built with Bootstrap 5.3 RTL, custom CSS variables, and a small amount of vanilla JavaScript.

---

## File List
| File | Role |
|------|------|
| `resources/views/dashboard.blade.php` | Main dashboard Blade view (non‑admin) |
| `resources/views/layouts/app.blade.php` | Parent layout inherited by the dashboard |
| `resources/views/partials/_sidebar.blade.php` | Global sidebar widget (used elsewhere in the app, not directly on the dashboard) |
| `resources/css/app.css` | Global styles and CSS custom properties |
| `resources/css/dashboard.css` | Styles specific to the dashboard grid, cards, and form |
| `resources/css/sidebar.css` | Styles for the global sidebar widget |
| `resources/js/app.js` | Global JavaScript (image previews, slug generation, etc.) |
| *(Inline script in `dashboard.blade.php`)* | Dashboard‑specific JS (character counter, avatar preview) |

---

## View Hierarchy & Inheritance

```
layouts/app.blade.php
├── @yield('title')          ← dashboard.blade.php sets “My Dashboard”
├── @yield('content')
│   └── dashboard.blade.php
│       ├── Profile Card (read‑only)
│       ├── Stats Card (conditional)
│       ├── Quick Actions Card
│       └── Edit Profile Card (form)
└── @stack('scripts')
    ├── Bootstrap bundle (CDN)
    └── Custom scripts:
        ├── Inline script from dashboard.blade.php (character counter, avatar preview)
        └── resources/js/app.js (via Vite, global helpers)
```

**Note:** The global sidebar (`_sidebar.blade.php`) is **not** included on the dashboard page. It is used in other frontend views (posts index, author pages) but is documented here for completeness.

---

## Main Dashboard View  
### `resources/views/dashboard.blade.php`

#### Path and Purpose
Renders the entire logged‑in user dashboard as a two‑column layout:  
- **Left column**: Profile card, optional stats, quick actions.  
- **Right column**: Profile edit form.  

#### Data Dependencies
The controller (not documented here) passes at least one variable:  
- `$stats` – array with keys `published_posts`, `total_comments`, `total_views`.  
  Used only when the card is shown (see conditional rendering).

The view also relies on Laravel’s `auth()->user()` for all user data and on `old()` for form repopulation.

#### Blade Directives Used
| Directive | Purpose |
|-----------|---------|
| `@extends('layouts.app')` | Inherits the main layout. |
| `@section('title', …)` | Sets page title to “My Dashboard”. |
| `@section('content')` | Fills the layout’s content area. |
| `@if(auth()->user()->bio)` | Conditionally shows the “About Me” section. |
| `@if(auth()->user()->hasRole([…]) && $stats['published_posts'] > 0)` | Shows stats only for roles Admin/Editor/Author and when they have published posts. |
| `@can('create', App\Models\Post::class)` | Shows “Create Post” action if the user can create posts. |
| `@role('Admin')` / `@role('Editor')` | Displays Admin Panel or Manage Posts quick action button accordingly. |
| `@error('name')` etc. | Displays validation error messages below each field. |
| `@method('PUT')` | Spoofs the HTTP method for the update form. |
| `@csrf` | CSRF protection. |
| `@push('scripts')` | Injects dashboard‑specific JavaScript after the layout’s main scripts. |
| `old('name', auth()->user()->name)` | Repopulates form fields with old input or current user data. |

#### Included Partials
None. The sidebar partial is not used on this page.

#### Forms

**Edit Profile Form** (`action="{{ route('dashboard.update') }}"`)

- **Method:** `POST` with `@method('PUT')` → routes to `dashboard.update`.
- **CSRF:** `@csrf` included.
- **Fields:**
  - `name` (text, required) – max length not specified but backed by validation.
  - `email` (email, required).
  - `bio` (textarea, optional, maxlength 1000).  
    - Character counter (JS) shows remaining available characters.
    - CSS class `char-limit-exceeded` is applied if >1000 characters are typed.
  - `avatar` (file input, optional, accept="image/*").  
    - Uses a custom styled label because the real `<input type="file">` is visually hidden (`file-input` class).  
    - Checkbox “Delete current avatar” appears only when the user already has an avatar.
  - `delete_avatar` (checkbox, value="1") – sent along with form to signal avatar removal.
- **Validation Errors:** Each input has an `@error` block that displays the error message in `<div class="error-message">`.
- **Submit:** `<button type="submit" class="btn-submit">` with save icon.

**JavaScript Behaviour (inline):**
- Character counter for `#bio`: updates `<span id="bio-count">` in real time and toggles the `char-limit-exceeded` class.
- Avatar preview: when a new file is selected, it updates the `.profile-avatar` image via `FileReader`.  
  (This script is pushed with `@push('scripts')` and runs after DOMContentLoaded.)

#### Conditional Rendering
- **Bio section** – only if `auth()->user()->bio` is not empty.
- **Stats card** – only if user has role Admin, Editor, or Author **and** `$stats['published_posts'] > 0`.
- **Quick Actions**:
  - “Create Post” button visible only if `@can('create', Post::class)`.
  - “Admin Panel” only for `@role('Admin')`.
  - “Manage Posts” only for `@role('Editor')`.
  - “Browse Posts” always shown.
- **Delete avatar checkbox** – only if user already has an avatar (`auth()->user()->avatar` truthy).

#### Links & Navigation
| Element | Route Name | Target |
|---------|------------|--------|
| “Create Post” button | `posts.create` | Post creation form |
| “Admin Panel” button (Admin) | `admin.dashboard` | Admin dashboard |
| “Manage Posts” button (Editor) | `admin.posts.index` | Admin posts list |
| “Browse Posts” button | `posts.index` | Public post listing |

There is no direct link to the sidebar on this page.

#### Styling Notes
- The dashboard layout uses **CSS Grid**: `.dashboard-container` is `grid-template-columns: 350px 1fr`.
- Cards are styled with `dashboard-card` class – white background, rounded corners, subtle hover lift.
- Profile header uses a gradient (`--dashboard-primary` → `--dashboard-secondary`).
- Form elements follow Bootstrap 5 styling, enhanced by `dashboard.css`.
- All relevant CSS variables are defined in `:root` inside `dashboard.css`.

---

## Parent Layout  
### `resources/views/layouts/app.blade.php`

#### Path and Purpose
The global HTML shell for all frontend pages. It includes the navigation bar, floating flash messages, content area, and footer.

#### Data Dependencies
- `csrf_token()` – for the meta tag and logout form.
- `request()` – to highlight active navigation links via `request()->routeIs(…)`.
- `auth()->user()` – for user avatar, name, role‑based nav links.
- `session('success')`, `session('error')`, `session('status')` – for floating flash notifications.

#### Blade Directives Used
| Directive | Purpose |
|-----------|---------|
| `@yield('title', 'LaraBlog')` | Sets page title. |
| `@yield('content')` | Main content placeholder. |
| `@stack('styles')` | Additional stylesheets (not used by dashboard). |
| `@stack('scripts')` | Additional scripts (used by dashboard inline script). |
| `@auth` / `@guest` | Toggles between login/register links and user dropdown. |
| `@role('Admin')` / `@role('Editor')` | Shows admin/editor navigation links in the main navbar. |
| `@can('create', …)` | Shows “Create Post” in dropdown. |
| `@if(session('success'))` etc. | Renders floating flash messages. |
| `@endif`, `@else` | Standard conditionals. |

#### Conditional Rendering
- **Navigation bar**:  
  - Guest users see Login and Register links.  
  - Authenticated users see a user dropdown with role‑sensitive items:  
    - Content creators (Admin/Editor/Author) see “Public Profile” and “Advanced Settings”.  
    - Regular users see “My Account” (link to dashboard).  
    - “Create Post” appears if `@can('create', Post::class)`.  
    - Admin Panel / Post Manager appear according to strict `@role` checks.  
- **Flash messages** appear as fixed, glassmorphic cards at the bottom‑right.

#### Included Partials
None. The sidebar is not included here.

#### Forms
- **Logout:** `<form method="POST" action="{{ route('logout') }}">` with `@csrf` and a submit button styled as dropdown item.

#### Links & Navigation
- Brand: `route('home')`
- Posts: `route('posts.index')`
- Authors: `route('authors.index')`
- Dashboard: `route('dashboard')`
- Admin Panel (Admin): `route('admin.dashboard')`
- Post Manager (Editor): `route('admin.posts.index')`
- Login: `route('login')`
- Register: `route('register')`
- Public Profile: `route('author.show', auth()->user())`
- Advanced Settings: `route('author.edit')`

Active link detection is done with `request()->routeIs('…') ? 'active' : ''`.

#### Styling Notes
- Bootstrap 5 RTL from CDN.
- Bootstrap Icons CDN.
- Custom CSS via Vite: `resources/css/app.css`, `sidebar.css`, `dashboard.css`, `author.css`, `post.css`.
- Background colour set to `var(--background)`.
- Flash messages use a custom glassmorphism style with `slideInUp` animation defined in `app.css`.

---

## Global Sidebar Partial (used elsewhere, not on dashboard)  
### `resources/views/partials/_sidebar.blade.php`

#### Path and Purpose
A sidebar widget for public pages (post index, author pages). Not rendered on the dashboard but documented for completeness.

#### Data Dependencies
- Direct Eloquent queries: `\App\Models\Category::withCount('posts')->get()`, `\App\Models\Tag::withCount('posts')->get()`, `\App\Models\Comment::where('approved', false)->count()`, etc.
- `auth()->user()` roles for `@role` / `@can` directives.

#### Blade Directives Used
- `@can('view-admin-panel')` – wraps the entire Admin Quick Access block.
- `@role('Admin')` – shows admin‑only sections (dashboard link, categories, tags, pending comments).
- `@can('approve', Comment::class)` – shows pending comments alert only for users who can approve.
- `@if($pendingComments > 0)` – alert visibility.
- `@foreach` – iterates categories/tags.
- `{{ route('categories.show', $category->slug) }}` – category links.
- `{{ route('tags.show', $tag->slug) }}` – tag links.

#### Conditional Rendering
- The entire “Admin Quick Access” section appears only for users who can `view-admin-panel`.
- Inside it, “Dashboard”, “Manage Categories”, “Manage Tags” are strictly for `Admin`.
- “Manage Posts” is visible to both Admin and Editor (no `@role` guard).
- Pending comments alert appears only for `Admin` who can `approve` comments.
- Categories and Tags widgets only if they have records.

#### Links & Navigation
- `admin.dashboard`, `admin.categories.index`, `admin.tags.index`, `admin.posts.index`
- `categories.show`, `tags.show`

#### Styling Notes
- Uses `.sidebar-card` classes with gradient headers.
- Enhanced badges and hover animations (`animated-list-item`).
- Sticky positioning (`position: sticky; top: 20px`).

---

## CSS Details

### `resources/css/dashboard.css`
- Defines all `--dashboard-*` CSS custom properties.
- Implements the two‑column grid layout.
- Card hover effects (`translateY(-2px)`, deeper shadow).
- Profile card gradient header, avatar overlay, role badge.
- Stats grid (`grid-template-columns: repeat(3, 1fr)`).
- Action buttons with brand colours.
- Edit form: file input hidden, styled label, character counter, error messages, submit button.
- Responsive breakpoints: collapses to single column ≤992px, tighter padding ≤768px.

### `resources/css/sidebar.css`
- Sidebar‑specific variables (`--sidebar-*`).
- Sticky container, card styling, gradient headers per widget type.
- Animations: `animated-list-item` (translation on hover), `pulse`, `glowing`.
- Pending comments alert with yellow gradient.
- Tags display as `enhanced-badge` pills with hover scale.

### `resources/css/app.css`
- Global colour palette and variables (`--primary: #FF2D20`, etc.).
- Body, link, card, and button base styles.
- Navbar active state with underline animation.
- Flash notification system: fixed container, glassmorphism cards with `slideInUp` keyframe.
- Responsive adjustments.

---

## JavaScript Details

### Inline Script (dashboard.blade.php)
- **Character Counter:** Listens to `#bio` `input` event, updates `#bio-count` text, toggles class `char-limit-exceeded`.
- **Avatar Preview:** Listens to `change` on `input[name="avatar"]`, reads file with `FileReader`, updates `src` of `.profile-avatar` image.

### `resources/js/app.js`
Not specifically for the dashboard, but loaded on every page. Contains reusable helpers:
- Auto‑slug generation from title.
- Reading‑time estimation.
- Image preview with file size and type validation (using `window.LaravelConfig`).
- `delete_image` checkbox logic.
Used on post/article forms, not directly by dashboard elements.

Both the inline script and `app.js` are loaded after the Bootstrap bundle, ensuring DOM is ready.

---

## Integration with Backend (Minimal Reference)
- **Page rendering**: The dashboard is served by a controller method (likely `DashboardController@index`) that provides the `$stats` array and the authenticated user. The route name is `dashboard`.  
- **Form submission**: The profile edit form submits to `route('dashboard.update')` (PUT method). Validation is handled by a form request class (`DashboardProfileRequest` assumed).  
- **Flash messages**: The layout displays `success`, `error`, and `status` session keys as floating notifications.  
- **Authorization**: Blade directives `@can`, `@role` rely on Laravel’s permission system (spatie/laravel-permission) with policies defined for `Post` and `Comment`.  

*For full backend documentation, see the separate backend docs.*

---

## Edge Cases & UI States

### Loading States
No explicit loading spinners are present in this module. The page renders server‑side, so the user sees the dashboard immediately after navigation.

### Empty States
- **Bio**: If user hasn’t written a bio, the “About Me” block is hidden entirely.
- **Stats Card**: Hidden if the user does not belong to a content‑creator role **or** has zero published posts.
- **Avatar**: If no avatar is set, a default placeholder (likely handled by `avatar_url` accessor on the User model) is displayed.

### Error Handling
- **Form validation errors** appear below each field with a dedicated `.error-message` block.
- **Server‑side errors** are displayed via the flash message system (e.g., “Profile update failed”).
- **Client‑side**: Character counter warns visually when bio exceeds 1000 characters (red border).

### Permission‑Denied / Role‑Based Visibility
- “Create Post” is hidden from users who cannot create posts (e.g., regular users without Author role).
- “Admin Panel” link is only shown to Admins; Editors see “Manage Posts” instead.
- Navigation bar dynamically adjusts links based on authentication and roles, preventing useless clicks.

### Banned User Views
No explicit “banned” state is rendered in this dashboard view. The application likely prevents banned users from logging in (handled at middleware level), so the dashboard is only available to active users.

### Browser Support
- Uses modern CSS grid, custom properties, and `FileReader` – all are well supported in evergreen browsers.
- Bootstrap 5 RTL provides a solid baseline with polyfills for older browsers.

---
```