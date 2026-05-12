```markdown
# Author Browsing & Profile Module – Frontend Documentation

> **File:** `document/frontend/author-views.md`  
> **Module:** Authors (`authors/` views)  
> **Framework:** Laravel + Blade, Bootstrap 5, Bootstrap Icons, custom CSS  

---

## 1. Overview

The **Author browsing and profile** module provides three interconnected pages that let visitors discover authors, view detailed author profiles (including their posts and stats), and allow authenticated users to edit their own profile.

**Typical users and use‑cases:**

- **Visitors (guests and logged‑in users):**  
  Browse the author listing (`/authors`), search for authors by name, view an author’s public profile with bio, social links, publishing statistics, and their published posts.

- **Logged‑in authors (profile owners):**  
  From their own `show` page, they see an **Edit Profile** button leading to a form where they can update their avatar, name, email, bio, and social links. The edit page is also accessible directly via `route('author.edit')`.

- **Admins and other authenticated users:**  
  Can see all authors and profiles, but can only edit their own profile (authorisation enforced at the backend). The module does not expose admin‑specific editing of other users; that belongs to the Admin panel.

The module integrates tightly with the layout system (`layouts.app`, `layouts.admin`), shared partials (`_sidebar`, `_post_card`), and the global CSS theme defined in `author.css` and `app.css`.

---

## 2. File List

| File | Role |
|------|------|
| `resources/views/authors/index.blade.php` | Author listing page with grid, search, sidebar, and pagination. |
| `resources/views/authors/show.blade.php` | Public author profile with bio, stats, social links, and a list of their posts. |
| `resources/views/authors/edit.blade.php` | Profile editing form (avatar, name, email, bio, social links). |
| `resources/css/author.css` | Dedicated stylesheet for author cards, search bar, profile layout, social icons, empty states, and many shared CSS custom properties. |
| *(inline scripts)* | Provided inside `index.blade.php` (sidebar toggle) and `edit.blade.php` (bio character counter, avatar preview). No dedicated JS file. |

---

## 3. View Hierarchy & Inheritance

All three views extend a base layout. The inheritance chain is:

```
layouts/app.blade.php          (public-facing layout)
├── authors/index.blade.php
└── authors/show.blade.php

layouts/admin.blade.php        (admin / authenticated-focussed layout)
└── authors/edit.blade.php
```

### Detailed inheritance

#### `authors/index.blade.php`
```
@extends('layouts.app')
  └─ @section('title', 'Our Authors')
  └─ @push('styles') … @endpush (inline scroll tweak)
  └─ @section('content')
       ├─ includes `partials._sidebar` (the main blog sidebar)
       └─ inline <script> for mobile sidebar toggle
```

#### `authors/show.blade.php`
```
@extends('layouts.app')
  └─ @section('title', $user->name . ' - Author Profile')
  └─ @push('styles') … @endpush (massive profile CSS block)
  └─ @section('content')
       ├─ includes `partials._post_card` for each post
       └─ no additional scripts
```

#### `authors/edit.blade.php`
```
@extends('layouts.admin')
  └─ @section('title', 'Edit Profile')
  └─ @section('content') (form)
  └─ @push('scripts') … @endpush (bio counter + avatar preview)
```

No `@include` of partials other than those listed.

---

## 4. Detailed View Documentation

### 4.1 `resources/views/authors/index.blade.php`

#### Path and Purpose
Renders the **author listing/search page** (`authors.index` route). Displays all authors in a responsive grid, with search bar, sidebar, and pagination.

#### Data Dependencies
Expects a paginator instance of users:
- **`$authors`** – Paginated collection of `User` models. Each author object must have:
  - `name`, `avatar_url`, `bio` (nullable HTML), `social_links` (array or null), `roles` (collection).
- The request object is available for preserving search query: `request('search')`.

Likely controller: `AuthorController@index`.

#### Blade Directives Used
- `@forelse($authors as $author)` → author cards grid; `@empty` → empty state.
- `@if($author->bio)` / `@else` → show bio excerpt or default message.
- `@if(!empty($author->social_links) && is_array($author->social_links))` → conditionally render social icons.
- `@foreach($author->roles as $role)` → display role badges with colour logic (`@if($role->name === 'Admin')` etc.).
- `@if($authors->hasPages())` → show pagination only when needed.

#### Included Partials
- `@include('partials._sidebar')` – Blog sidebar with categories, tags, etc. (located in `resources/views/partials/_sidebar.blade.php`). No parameters passed; uses shared data.

#### Forms
Search form:
- `action="{{ route('authors.index') }}" method="GET"`
- Input name: `search` (preserves value via `value="{{ request('search') }}"`)
- No CSRF needed (GET).
- Submit button with search icon.

#### Conditional Rendering
- Author roles: colour badges based on role name (`Admin` → `bg-danger`, `Editor` → `bg-warning text-dark`, default → `bg-primary`).
- Social links: each platform (github, linkedin, twitter, website) only appears if the respective key exists in the `social_links` array.
- Bio: if bio exists, shows (stripped, limited to 80 chars); otherwise shows "Content Creator on LaraBlog.".
- Pagination: only rendered when `$authors->hasPages()`.

#### Links & Navigation
- **Author profile link:** `route('author.show', $author)` on the "View Profile" button.
- **Pagination links:** Provided by `$authors->links('pagination::bootstrap-5')`.
- **Search form** submits to `route('authors.index')` with GET parameters.
- **Sidebar** links (from `_sidebar`) lead to posts, categories, etc.

#### Styling Notes
- Uses custom CSS variables defined in `author.css` for colours, shadows, transitions.
- Card hover effect: `transform: translateY(-5px)` and shadow change.
- Authors grid uses Bootstrap `col-md-6 col-lg-6` (two cards per row on medium screens).
- Left column (`col-lg-8`) has a fixed height with `overflow-y: scroll` via an inline `@push('styles')` style.
- Search bar, empty state, buttons are all styled in `author.css`.
- Sidebar toggle: JavaScript toggles `show` class on mobile, adjusts icon.

---

### 4.2 `resources/views/authors/show.blade.php`

#### Path and Purpose
Displays a **full author profile** (`author.show` route). It shows a sticky left sidebar (on larger screens) with avatar, name, email, roles, bio, social links, an "Edit Profile" button (if owner), and a stats grid. The right column lists the author’s published posts in a two‑column grid using `_post_card` partial.

#### Data Dependencies
Expects three variables:
- **`$user`** – `User` model instance (the author). Must have:
  - `name`, `email`, `avatar_url`, `bio` (nullable HTML), `social_links` (array/null), `roles` (collection), `id`.
- **`$posts`** – Paginated collection of `Post` models (the author’s posts).
- **`$stats`** – Associative array with keys `total_posts`, `total_views`, `total_comments`, `member_since` (formatted date).

Controller: likely `AuthorController@show`.

#### Blade Directives
- `@if($user->bio)` – display bio.
- `@if(!empty($user->social_links) && is_array($user->social_links))` + multiple `@if(isset(...))` for each social platform.
- `@if(auth()->check() && auth()->id() === $user->id)` – show the "Edit Profile" button **only if the currently logged‑in user is the profile owner**.
- `@foreach($user->roles as $role)` – role badges (same logic as index).
- `@if($posts->count())` vs `@else` – render post grid or empty state with an optional "Write your first post" link if the visitor is the profile owner.
- `@foreach($posts as $post)` → `@include('partials._post_card', ['post' => $post])`

#### Included Partials
- `partials._post_card` – the reusable post card component. Receives `$post` explicitly.

#### Forms
No forms on this page. Only navigation links.

#### Conditional Rendering
- **Edit Profile button:** Visible only when `auth()->check()` and `auth()->id() === $user->id`.
- **Empty posts state:** When no posts, shows a card with message. If the logged‑in user owns the profile, an additional link `route('posts.create')` appears.
- **Social icons:** Each platform only shown if its key exists in `$user->social_links`.

#### Links & Navigation
- `route('author.edit')` → "Edit Profile" (only for owner).
- `route('posts.create')` → "Write your first post" (only for owner on empty state).
- Post cards link to individual post pages (handled by `_post_card` partial).
- Pagination links for posts.

#### Styling Notes
- Extensive custom CSS embedded via `@push('styles')`.  
  Layout uses a **sticky sidebar** on desktop (`.profile-sidebar { position: sticky; top: 2rem; }`) with a card containing avatar, info, and stats.
- All colours, shadows, transitions are from `author.css` custom properties.
- Avatar has hover scale effect.
- Responsive: on screens ≤ 991px, the sidebar becomes static (`position: static`).
- Post grid uses Bootstrap classes (`col-lg-8 offset-xl-3`, etc.).

---

### 4.3 `resources/views/authors/edit.blade.php`

#### Path and Purpose
Provides the **profile editing form** for the authenticated user (`author.update` route). Only the profile owner can access it (backend enforces).

#### Data Dependencies
Relies on the authenticated user (`auth()->user()`). Uses:
- `auth()->user()` properties: `name`, `email`, `avatar_url`, `bio`, `social_links`, `avatar` (original filename? actually just existence check).
- Old input is preserved via `old(...)` helpers.
- Error bags for validation.

No additional variables passed from the controller (other than the shared auth data). Controller: `AuthorController@edit` (GET) and `update` (PUT).

#### Blade Directives
- `@csrf`, `@method('PUT')` – form method spoofing.
- `@error('field')` → show validation error messages.
- `@if(auth()->user()->avatar)` → show checkbox to delete current avatar.
- `@foreach` not needed; social links are individual inputs.

#### Forms
The core element: a multipart form for profile update.

| Attribute | Value |
|-----------|-------|
| `action` | `{{ route('author.update') }}` |
| `method` | `POST` (with `@method('PUT')`) |
| `enctype` | `multipart/form-data` (for avatar upload) |
| CSRF | `@csrf` |

**Fields:**

1. **Avatar**  
   - Input type `file`, name `avatar`, accept `image/*`.  
   - Shows current avatar as a preview image (120×120 circle).  
   - Checkbox `delete_avatar` (value 1) appears only if user has an existing avatar.  
   - Validation error: `@error('avatar')` displays under the input.

2. **Name**  
   - `type="text"`, required, populated with `old('name', auth()->user()->name)`.

3. **Email**  
   - `type="email"`, required, populated similarly.

4. **Bio**  
   - `textarea` with placeholder. Character counter via JavaScript (`<span id="bio-count">...`).  
   - Validation error `@error('bio')`.

5. **Social Links**  
   - Four `input[type="url"]` fields inside input groups, each prefixed with an icon.  
   - Name attribute uses array syntax: `social_links[twitter]`, `social_links[github]`, `social_links[linkedin]`, `social_links[website]`.  
   - Values default to existing social link or empty string.

**Actions:**
- Cancel button links to `route('author.show', auth()->user())`.
- Submit button: "Update Profile".

#### Conditional Rendering
- The edit page itself is only accessible to authenticated users (route middleware `auth`); if not, redirected to login.
- The delete avatar checkbox is only shown when `auth()->user()->avatar` exists (truthy value).
- The bio character counter shows current length.

#### Links & Navigation
- Cancel → author profile (`author.show`).
- Submit → `author.update` (PUT).

#### Styling Notes
- Uses `layouts.admin` layout, so the page appears within the admin sidebar and header (but still serves the user’s own profile editing, not admin-specific user management).
- Form fields use Bootstrap form classes, plus inline styles for avatar border colour (`var(--primary)`).

#### Inline JavaScript (inside `@push('scripts')`)
Two event listeners:
1. **Bio character counter:**  
   On `input` of `#bio`, updates `#bio-count` text and adds/removes `is-invalid` class if length > 1000. (Backend validation also enforces max length.)
2. **Avatar preview:**  
   On `change` of the avatar file input, reads the file via `FileReader` and updates the `src` of the existing avatar preview `img.rounded-circle`.

---

## 5. CSS/JS Specific to This Module

### 5.1 `resources/css/author.css`

This file is **loaded on all pages** (likely imported in `app.css` or loaded globally via Vite). It defines a comprehensive design system for author-related components but many variables are shared with the post module (as seen by `--post-primary` naming). Key sections:

- **CSS Custom Properties:** Defines a palette (`--post-primary: #FF2D20`, `--post-secondary`, etc.), shadows, transitions. These variables are consumed throughout the author views and also in the post cards.
- **Search Container:** Styles the search bar consistently across author and post indexes.
- **Author Card:**  
  - `.author-card` – hover lift, shadow, border colour transition.  
  - `.author-avatar` – size, border, shadow.  
  - `.author-bio` – min-height to prevent card layout shift.  
  - Social link icons scaling on hover.  
- **Empty State:** `.empty-state-card` with dashed border.
- **Profile page styles** (in `author/show.blade.php`) are mostly written inline in `@push('styles')` and rely on these variables. However, the stylesheet also includes some generic profile rules for reuse (if needed).
- **Buttons:** `.author-btn` and search button styles with hover effects.

Note: The inline styles in `show.blade.php` duplicate some variable usage but ensure the profile sidebar styling is self‑contained and not reliant on the CSS file for every specific class.

### 5.2 JavaScript

**No dedicated JS file.** Two inline scripts:

1. **Sidebar toggle (index.blade.php):**  
   - Mobile sidebar show/hide with `#sidebarToggle` and `#sidebarWrapper`.  
   - Swaps chevron icons, toggles `show` class, and closes sidebar on outside click.

2. **Profile edit (edit.blade.php):**  
   - Bio character counter with validation class toggle.  
   - Avatar client‑side preview using FileReader.

Both scripts are placed at the bottom of their respective views using `@push('scripts')` (edit) or raw `<script>` at section end (index). They are not deferred or loaded as modules.

---

## 6. Integration with Backend

### 6.1 Routes & Controllers

| View File | Route Name | HTTP Method | Controller Method | Purpose |
|-----------|------------|-------------|-------------------|---------|
| `index.blade.php` | `authors.index` | GET | `AuthorController@index` | List/search authors. |
| `show.blade.php` | `author.show` | GET | `AuthorController@show` | Display single author profile. |
| `edit.blade.php` | `author.edit` | GET | `AuthorController@edit` | Show edit form. |
| (form submit) | `author.update` | PUT | `AuthorController@update` | Process profile update. |

### 6.2 Data Flow

- **Index:** Controller queries `User` with roles, paginates, accepts `search` query param to filter users by name/email. Passes `$authors` to view.
- **Show:** Receives `User $user` via route model binding. Builds `$posts` paginator (published posts by that user) and an aggregated `$stats` array. Passes `$user`, `$posts`, `$stats`.
- **Edit:** No additional data needed; the form is populated from `auth()->user()`.
- **Update:** Validates request (likely `ProfileRequest` or `DashboardProfileRequest`). Updates name, email, bio, social_links, avatar (with deletion option). Uses `ImageService` for avatar processing. On success, redirects back with `success` flash message.

### 6.3 Flash Messages & Error Handling

- **Success message** after profile update: expected to be displayed in the layout via `@include('partials._alerts')` or similar (not shown in provided files, but standard Laravel pattern). The edit view does not render flash explicitly, so it must be in the admin layout.
- **Validation errors:** Displayed inline using `@error` directive; also Laravel’s `$errors` bag may be shown globally.
- **404/403:** Handled by exception views (not part of this module). If a user tries to access `author.show` for a non‑existent user, a 404 is thrown. If a non‑owner accesses `author.edit`, backend returns 403. These error pages are likely customised in `resources/views/errors/`.

---

## 7. Edge Cases & UI States

### 7.1 Loading States
No explicit spinner or skeleton screens are implemented in these views. Pages are rendered server‑side; the user sees a brief blank while Blade renders. If AJAX‑based features are added later (e.g., live search), loading states should be introduced.

### 7.2 Empty States

- **No authors found (index):**  
  An empty card with an icon, "No authors found", and suggestion to adjust search criteria. Pagination is hidden.

- **No posts by author (show):**  
  A centered card with clipboard icon, "No posts yet", and the author’s name. If the logged‑in user is the profile owner, a "Write your first post" link to `posts.create` appears.

### 7.3 Error Messages

- **Validation errors (edit form):**  
  Each field can show `@error` message (`invalid-feedback` class). Example: invalid avatar type, name required, email format, bio > 1000 characters. The bio counter also turns the field red client‑side if >1000 characters.

- **Authorization errors:**  
  If a user manually navigates to `/author/edit` while not logged in, the `auth` middleware redirects to login. If logged in but trying to edit another user’s profile (unlikely route, but protected by policy), a 403 Forbidden page (custom error view) is displayed.

- **404:**  
  Invalid author slug in `author.show` triggers a standard Laravel 404. No special handling in this module.

### 7.4 Permission‑Based Visibility

- **Edit Profile button (show page):** Only shown when `auth()->check() && auth()->id() === $user->id`.
- **“Write your first post” (show empty state):** Only if the visitor owns the profile.
- **Delete avatar checkbox (edit form):** Only appears if `auth()->user()->avatar` is truthy (i.e., an avatar already exists).
- **The edit page itself** is behind `auth` middleware; no guest access.

### 7.5 Banned or Suspended Users

The provided views do **not** contain explicit checks for banned/suspended users (e.g., a `banned_at` field). However, looking at the migration `add_ban_fields_to_users_table`, there are `banned_at` and `ban_reason` fields. These are likely used by the Admin panel to hide banned authors from public listings. If a banned user’s profile is still accessible, no UI indication is present. This behaviour depends on backend query scoping (e.g., `whereNull('banned_at')`) in `AuthorController@index` and `@show`. For frontend, no conditional rendering related to ban status exists.

### 7.6 Responsive Behaviour

- **Index page:** On mobile, the sidebar is hidden by default and can be toggled via the sidebar toggle button. Left column scrolls vertically.
- **Show page:** Profile sidebar becomes static (non‑sticky) on screens ≤ 991px, stacking above the posts list.
- **Edit page:** The admin layout already provides responsive handling (sidebar collapse, etc.); the form column is centered and shrinks on smaller screens.

---

## 8. Dependencies & Asset Compilation

- **CSS:** `resources/css/author.css` imported into `app.css` (or loaded directly via Vite). The compiled asset is `public/build/assets/author-3oCFCb6R.css`.
- **JS:** No dedicated author JS file. Inline scripts do not rely on any compiled modules.
- **Icons:** Bootstrap Icons (`bi bi-*`) are used throughout.
- **Blade partials:** `partials._sidebar`, `partials._post_card`, and possibly `partials._alerts` (for flash messages) are shared across the application.

---

```