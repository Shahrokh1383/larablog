---

```markdown
# Frontend Custom CSS & JavaScript Documentation

## Overview

The LaraBlog frontend layer is built with a combination of **Bootstrap 5** (utility and component classes) and **custom CSS/JS** organized by feature area. Styles are split into six dedicated stylesheets, each targeting a distinct part of the application: global layout, public sidebar, blog posts, user dashboard, author listing, and admin panel. JavaScript is bundled into two main entry points: `app.js` (general interactivity) and `admin.js` (admin-specific behaviors), with a `bootstrap.js` file that imports and configures Axios.

All assets are compiled via **Vite** using the Laravel plugin. CSS files are plain CSS (not SCSS). The design emphasizes card‑based layouts, soft shadows, gradient accents, and smooth transitions. A consistent custom property system (`--primary`, `--sidebar`, etc.) is used across files, though with some duplication (see Maintainability Notes).

## File Inventory

| File | Path | Purpose |
|------|------|---------|
| `app.css` | `resources/css/app.css` | Global styles, color palette, navbar, buttons, cards, flash notifications, and base responsive rules. |
| `sidebar.css` | `resources/css/sidebar.css` | Public sidebar widgets (categories, tags, stats, admin quick‑access). |
| `post.css` | `resources/css/post.css` | Post listing grid, post detail page, comments, related posts, and search bar. |
| `dashboard.css` | `resources/css/dashboard.css` | User dashboard layout: profile card, stats, actions, and profile edit form. |
| `author.css` | `resources/css/author.css` | Author listing page: search, author cards, social links. |
| `admin.css` | `resources/css/admin.css` | Admin dashboard: stat cards, page headers, admin sidebar (off‑canvas on mobile), and user management tables. |
| `app.js` | `resources/js/app.js` | Form helpers (slug generation, character counters, reading time, image previews, delete protection). |
| `admin.js` | `resources/js/admin.js` | Admin bulk actions, search debounce, delete confirmations, Bootstrap tooltips, and responsive sidebar toggle. |
| `bootstrap.js` | `resources/js/bootstrap.js` | Loads Axios and sets the `X-Requested-With` header for AJAX requests. |

## Styling Architecture

### 1. `app.css` – Global Styles & Layout

**Target Scope**  
Applied globally via the main layout (`resources/views/layouts/app.blade.php`). Affects all public pages, including post listings, dashboard, and author pages.

**Key Selectors & Classes**
- **`:root` variables** – Defines the primary color palette (`--primary: #FF2D20`, etc.), spacing, and shadows.
- **`.main-content`** – Minimum height container for sticky footer.
- **`.card`, `.card-compact`** – Reusable card components with hover lift effect. `.card-compact` is used for smaller post previews (120px image, compact text).
- **`.posts-container`** – Scrollable container with custom scrollbar, used on pages with many posts.
- **`.sidebar-container`** – Sticky positioning for public sidebars.
- **`.btn-primary`, `.btn-outline-primary`, etc.** – Enhanced button styles with gradient overlay on hover and active press effect.
- **`.navbar-dark`, `.navbar-nav .nav-link`** – Dark navbar with underline animation on hover and active state support.
- **`.flash-container`, `.flash-card`** – Glassmorphism floating notification system used for success/error messages (likely populated by Blade flash data).
- **Responsive rules** – Collapse sidebar to static on tablets, reduce card motion on mobile.

**Methodology & Conventions**  
Utility-first mix: global CSS variables for theming, component classes for cards and buttons. Hover effects rely on `transform`, `box-shadow`, and `transition`. The glassmorphism notifications are a distinct, reusable pattern.

**Specificity & Overrides**  
Overrides Bootstrap defaults for buttons and form controls by targeting `.btn-*` and `.form-control:focus` directly. No deep nesting; uses simple class selectors.

**Responsive/State Handling**  
- Mobile (<768px): full‑width navbar search, simplified button effects.
- Tablet (<992px): sidebar becomes static, scrollable container loses max‑height.
- No explicit dark mode; color scheme is fixed.

---

### 2. `sidebar.css` – Public Sidebar Widgets

**Target Scope**  
Loaded on pages that include the public sidebar partial (`resources/views/partials/_sidebar.blade.php`). It styles individual widgets inside the `.sidebar-container`.

**Key Selectors & Classes**
- **`.sidebar-card`, `.sidebar-card-header`** – Styled widget containers with gradient headers (different colors per widget type).
- **`.about-widget`, `.categories-widget`, `.tags-widget`, `.stats-widget`, `.admin-quick-access`** – Distinct gradient themes for each widget.
- **`.pending-comments-alert`** – Special alert for pending comments (visible to admins/moderators).
- **`.animated-list-item`** – Slide‑right hover effect on list items (e.g., category links).
- **`.pulse-animation`, `.glowing-effect`** – Decorative animations for highlighting elements.

**Methodology & Conventions**  
Each widget uses a different gradient to visually separate sections. Icons are integrated via `<i>` tags. Badge hover effects use `transform: translateY(-2px)` and a shadow.

**Responsive**  
On tablet and below, sidebar becomes static and cards stack normally.

**Integration with Blade**  
Matches markup expected in `_sidebar.blade.php`: a `<div class="sidebar-container">` containing multiple `<div class="sidebar-card ...-widget">` blocks.

---

### 3. `post.css` – Blog Posts & Detail Pages

**Target Scope**  
Applied on post listing pages (`resources/views/posts/index.blade.php`) and individual post pages (`resources/views/posts/show.blade.php`). Also styles the search bar and pagination.

**Key Selectors & Classes**
- **`.posts-grid-container`** – CSS Grid layout that auto‑fills columns (min 350px). Responsive to single column on small screens.
- **`.post-card`** – Full card with image, truncation (2‑line title, 3‑line excerpt), hover effects (lift, border color change).
- **`.post-card-tag`** – Gradient tag badges.
- **`.create-post-button`** – Animated “Create Post” button with rotating icon on hover.
- **`.sidebar-toggle-button`, `.sidebar-wrapper`** – Off‑canvas sidebar toggle for mobile; visible only below 992px.
- **Post detail styles** – `.post-header`, `.post-hero-image`, `.post-meta`, `.post-content` (images, code blocks, etc.), `.comments-section`, `.related-posts`.
- **`.empty-state-card`** – Dashed border placeholder for no posts.

**Methodology**  
BEM‑like naming (`.post-card`, `.post-card-body`, `.post-card-title`). Uses CSS Grid for the listing. Truncation via `-webkit-line-clamp`. Pagination overrides Bootstrap’s `.pagination` with rounded pills and primary color active state.

**Responsive**  
- Below 992px: sidebar toggle appears; grid forced to 1 column.
- Below 768px: grid single column; post header padding reduced.
- Below 576px: card title smaller; create button more compact.

**Edge Cases**  
Empty state styled with dashed border and hover effect. Comment reply indentation via `.replies` left border.

---

### 4. `dashboard.css` – User Dashboard

**Target Scope**  
Used exclusively on the user dashboard page (`resources/views/dashboard.blade.php`).

**Key Selectors & Classes**
- **`.dashboard-container`** – Two‑column grid layout (350px sidebar, flexible main).
- **`.dashboard-card`** – Standard card with hover lift.
- **`.profile-card`** – Profile widget with gradient header, avatar overlay, and detail list.
- **`.stats-card`** – Three‑column stats grid (posts, comments, etc.).
- **`.actions-card`** – Quick‑action buttons (create post, manage comments).
- **`.edit-card`** – Profile edit form with file upload, character counter, and password change info.
- **`.form-control.char-limit-exceeded`** – Visual feedback for exceeding character limits.

**Responsive**  
Below 992px: layout becomes single column. Stats grid collapses to single column on small screens.

**Integration**  
The dashboard’s Blade view uses `<div class="dashboard-container">` with columns matching the left (profile, stats, actions) and right (edit form) order.

---

### 5. `author.css` – Author Listing

**Target Scope**  
Applied on the authors index page (`resources/views/authors/index.blade.php`).

**Key Selectors & Classes**
- **`.search-container`, `.search-form`** – Styled search bar with primary‑colored submit button.
- **`.author-card`** – Card with avatar, name, bio, social links. Hover lifts card and changes border color to secondary.
- **`.author-avatar-wrapper`, `.author-avatar`** – Circular avatar with border.
- **`.empty-state-card`** – Styled empty state.

**Methodology**  
Consistent card hover pattern (translateY + shadow). Social icons scale on hover.

**Responsive**  
Primarily uses Bootstrap grid; no explicit breakpoints beyond what the framework provides.

---

### 6. `admin.css` – Admin Panel

**Target Scope**  
Loaded in the admin layout (`resources/views/layouts/admin.blade.php`) and applied to all admin pages.

**Key Selectors & Classes**
- **`.admin-dashboard`** – Light background container.
- **`.page-header`, `.page-title`** – Admin page heading style.
- **`.stats-grid`, `.stat-card`** – Admin dashboard stat cards with gradient top border, icon with gradient background, and loading shimmer animation.
- **`.user-management`** – Table and form focus styles for the user management section.
- **`.admin-sidebar`** – The left sidebar in admin. Inline‑style responsive: on mobile (<992px) it becomes a fixed off‑canvas overlay with slide‑in from left, backdrop overlay, and a close button (`.sidebarCloseBtn`). On desktop it is a static vertical nav.
- **`.flash-container`** – Duplicate of the glassmorphism notification (same as app.css). (Potential refactoring target.)

**Responsive**  
- Stats grid: 1 column mobile, 2 columns tablet, auto‑fit desktop.
- Sidebar uses JavaScript toggling with CSS transitions and overlay.

**Interaction**  
The sidebar behavior is fully documented in `admin.js` below. The CSS provides the visual layer: `body.sidebar-open` triggers `transform: translateX(0)` on `.sidebar-wrapper`.

---

## JavaScript Behavior

### 1. `bootstrap.js` – Axios Setup

**Initialization & Scope**  
Imported by `app.js`. Runs on every page load.

**Key Functions**
- Imports `axios` and attaches it to `window.axios`.
- Sets a default header `X-Requested-With: XMLHttpRequest` so Laravel can identify AJAX requests.

No DOM dependencies. This file does not initialize Bootstrap JS components.

---

### 2. `app.js` – General Frontend Interactivity

**Initialization & Scope**  
Loaded on all pages that include forms (post creation/editing, category/tag management, dashboard profile edit). Uses global `window.LaravelConfig` for image validation settings.

**Key Functions & Event Listeners**
- **Slug auto‑generation** (`#title`, `#name`): On input, generates a URL‑safe slug and updates `#slug` and `#slug-preview` unless the slug field has been manually edited (marked by `dataset.manuallyEdited`).
- **Excerpt character counter** (`#excerpt`): Shows count (`#excerpt-count`) and adds `.is-invalid` if >500 characters.
- **Reading time estimator** (`#body`): Calculates words per minute (200) and updates `#reading-time`.
- **Image preview** (`#featured_image`): Reads file with `FileReader` and sets `#image-preview` src. Also calls `window.previewImage` which performs size/type validation using `imageConfig`.
- **Delete protection** (`form[action*="destroy"]`): Checks `data-posts-count`, prevents submission with an alert if item has associated posts.
- **Category/Tag description counter** (`#description`): Updates `#desc-count` and invalidates >1000 chars.
- **Image delete checkbox**: When `#delete_image` is checked, dims current image preview and disables new file input.

**DOM Dependencies**  
Expects elements with IDs `title`, `slug`, `slug-preview`, `excerpt`, `excerpt-count`, `body`, `reading-time`, `featured_image`, `image-preview`, `name`, `description`, `desc-count`, `delete_image`, `current-image`, etc. These are present in Blade forms under `resources/views/posts/partials/form.blade.php`, `resources/views/admin/categories/create.blade.php`, etc.

**Data Flow**  
No direct AJAX calls. Communication with backend happens via form submissions. The `delete protection` only prevents client‑side; server‑side validation should still exist.

---

### 3. `admin.js` – Admin Panel Behaviors

**Initialization & Scope**  
Loaded only on admin pages (layout `/admin`). Uses DOMContentLoaded event for sidebar setup and global functions for bulk actions.

**Key Functions & Event Listeners**

1. **Bulk Actions**
   - `toggleBulkActions()` – Toggles visibility of the bulk action form.
   - `setBulkAction(action)` – Validates that checkboxes are selected, confirms with user, then submits `#bulk-action-form` with a hidden `bulk-action-value` input.
   - `toggleSelectAll()` – Toggles all `.post-checkbox` checkboxes via the `#select-all` checkbox.
   - `updateBulkCount()` – Updates the selected count display, manages the indeterminate state of `#select-all`, and dynamically adds/removes hidden `ids[]` inputs to the form to reflect current selections.

2. **Search Debounce**
   - Listens to `input[name="search"]` for 500ms debounce, then submits the parent form.

3. **Confirm Delete**
   - All elements with class `.delete-confirm` show a confirmation dialog before allowing click action (prevents navigation on cancel).

4. **Bootstrap Tooltips**
   - Initializes tooltips on all elements with `[data-bs-toggle="tooltip"]`.

5. **Responsive Admin Sidebar**
   - Toggles `body.sidebar-open` class via hamburger button (`#sidebarToggle`), overlay click (`#sidebarOverlay`), and close button (`#sidebarCloseBtn`).
   - Closes sidebar automatically on window resize to ≥992px or when a nav link is clicked (mobile UX).
   - Depends on CSS in `admin.css` for the off‑canvas animation.

**DOM Dependencies**
- Bulk actions: `#bulk-action-form`, `.post-checkbox`, `#select-all`, `#bulk-selected-count`, hidden input fields.
- Sidebar: `#sidebarToggle`, `#sidebarOverlay`, `#sidebarCloseBtn`, `.admin-sidebar .nav-link`.
- These elements are found in admin index views (e.g., `admin/posts/index.blade.php`) and `admin/partials/_sidebar.blade.php`.

**External Library Usage**  
Uses Bootstrap’s `Tooltip` class directly. No other library calls.

---

## Loading & Build Integration

**Vite Configuration (`vite.config.js`)**
```js
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
});
```
Note: `admin.css` is **not** listed in the Vite input array, yet it exists in the CSS directory and compiled assets appear in `public/build/assets/`. This suggests that `admin.css` is likely imported inside another file (e.g., maybe `app.js` or a separate admin entry point that wasn’t provided). The compiled assets list shows `admin-3oCFCb6R.css`, so it is being built. This should be verified and corrected in the Vite config (likely missing an entry for `resources/css/admin.css`).

**Blade Inclusion**  
CSS and JS are loaded using the `@vite` directive. Based on typical Laravel usage, the main layout (`resources/views/layouts/app.blade.php`) includes:
```blade
@vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/css/post.css', 'resources/css/dashboard.css', 'resources/css/author.css', 'resources/js/app.js'])
```
The admin layout (`resources/views/layouts/admin.blade.php`) presumably has its own `@vite` directive including `admin.css` and `admin.js` (though admin.js isn’t in the Vite config either – it may be included as a separate entry point or imported dynamically). The compiled `manifest.json` would indicate how these are resolved.

**Conditional Loading**  
- `dashboard.css` and `author.css` are page‑specific but bundled together. They use scoped class names (e.g., `.dashboard-container`, `.author-card`) so they do not conflict when loaded on other pages.
- `admin.css` and `admin.js` appear to be loaded only on admin routes; they are not listed in the global Vite input array, so they are likely loaded via a separate admin layout `@vite` directive (e.g., `@vite(['resources/css/admin.css', 'resources/js/admin.js'])`).

**Build Process**  
Scripts `npm run dev` and `npm run build` run Vite. Tailwind CSS 4 and its Vite plugin are included in `devDependencies` but no Tailwind classes are evident in the custom CSS; the project relies heavily on custom properties. Bootstrap 5 is used via its JS bundle (Popper.js as dependency).

---

## Integration with Blade Views

The CSS class names and JS selectors map to specific Blade views as follows:

- **`app.css`** → `layouts/app.blade.php` – provides the base frame (navbar, main content, footer).
- **`sidebar.css`** → `partials/_sidebar.blade.php` – widgets within `sidebar-container`.
- **`post.css`** → `posts/index.blade.php` (grid), `posts/show.blade.php` (detail), `partials/_post_card.blade.php` (individual card).
- **`dashboard.css`** → `dashboard.blade.php` – the entire two‑column dashboard.
- **`author.css`** → `authors/index.blade.php` – author cards and search.
- **`admin.css`** → `layouts/admin.blade.php` and `admin/partials/_sidebar.blade.php` – stat cards, tables, and admin sidebar.
- **`app.js`** selectors (`#title`, `#slug`, `#featured_image`, etc.) are present in:
  - `posts/create.blade.php` and `posts/edit.blade.php` (they share `posts/partials/form.blade.php`)
  - `admin/categories/create.blade.php`, `admin/categories/edit.blade.php`
  - `admin/tags/create.blade.php`, `admin/tags/edit.blade.php`
- **`admin.js`** selectors (`.post-checkbox`, `#bulk-action-form`, `#sidebarToggle`) are in:
  - `admin/posts/index.blade.php` (bulk actions)
  - `admin/partials/_sidebar.blade.php` (sidebar toggle elements)
  - Admin layout includes the overlay and close button.

---

## Edge Cases & UI States

- **Empty States**: `post.css` and `author.css` provide `.empty-state-card` with dashed border and hover effect. Used when no posts or authors are returned.
- **Loading Feedback**: `admin.css` includes a shimmer animation on `.stat-card.loading`. JS does not explicitly trigger it; likely controlled by a server‑rendered class.
- **Form Validation Feedback**: `app.js` adds `.is-invalid` class to fields exceeding character limits (excerpt >500, description >1000). Backend validation errors are displayed via Laravel’s `$errors` and Bootstrap’s `.is-invalid` class in Blade.
- **Image Upload Edge Cases**: `previewImage` validates file size and type using configurable values; alerts on failure and resets the input.
- **Delete Protection**: Client‑side check on forms with `data-posts-count` > 0 prevents accidental deletion of categories/tags with posts. This is a soft protection; backend policy enforcement still required.
- **Permission‑Dependent UI**: The public sidebar’s `.admin-quick-access` widget and `.pending-comments-alert` are only rendered if the user has the appropriate role (as handled by Blade directives). No JS conditional logic for permissions.
- **Missing DOM Elements**: All JS event listeners use optional chaining (`?.`) or check element existence, preventing errors if an expected ID is absent (e.g., `app.js` slug generation on pages without a title field).
- **Mobile Sidebar**: Admin sidebar gracefully toggles off‑canvas; overlay prevents background interactions. Sidebar automatically closes on window resize to desktop or when a link is clicked.
- **Browser Support**: `backdrop-filter` used in flash notifications and sidebar overlay requires modern browsers; no fallback is provided but these are progressive enhancements.

---

## Maintainability Notes

1. **Duplication of Custom Properties**: Several CSS files redefine similar variables (`--primary`, `--secondary`, etc.) with file‑specific names (`--post-primary`, `--dashboard-primary`). This leads to inconsistency and larger CSS payloads. Consider consolidating all global design tokens into `app.css` (or a separate `_variables.css`) and importing it where needed.
2. **Flash Notification Duplication**: The entire `.flash-container` and `.flash-card` styles are copy‑pasted in both `app.css` and `admin.css`. This should be extracted into a shared CSS partial (`flash.css`) and imported via Vite or CSS `@import`.
3. **Vite Configuration Possibly Incomplete**: `admin.css` is built but missing from the Vite input array. This may indicate a dynamic import or a manual entry in a different config. Verify that `admin.css` and `admin.js` are properly listed in `vite.config.js` to ensure consistent hashing and cache busting.
4. **Global JS Namespace**: `app.js` uses `window.previewImage` and `imageConfig`; `admin.js` uses `window.toggleBulkActions`, `window.setBulkAction`, etc. While functional, a module‑scoped approach or a single `LaraBlog` namespace would reduce global scope pollution.
5. **Tailwind Unused**: Tailwind CSS 4 is installed but no utility classes are used in the custom stylesheets. If Tailwind is not intended for use, consider removing it to reduce dependency overhead.
6. **Bootstrap Initialization**: Bootstrap’s JS is imported globally but only tooltips are explicitly initialized. Modals, toasts, etc. rely on Bootstrap’s data‑attribute API. This is fine but should be documented.
7. **Accessibility**: Focus styles are present (`:focus` outlines). The admin sidebar manages focus implicitly but could benefit from a focus trap on mobile. The flash notification lacks ARIA live regions; consider adding `role="alert"`.
8. **Refactoring Opportunities**:
   - Unify repeated hover effect patterns (translateY + box‑shadow) into a utility class.
   - Move sidebar toggle logic from `admin.js` into a separate module if it grows.
   - Use `const` and `let` consistently (already mostly done).
   - The `bootstrap.js` file could be expanded to include Bootstrap JS imports, but currently only sets up Axios; consider renaming to `axios.js` if Bootstrap JS is loaded elsewhere.

---

```