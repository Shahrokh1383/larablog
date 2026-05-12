Admin Dashboard
markdown

# Admin Dashboard – Frontend Documentation

## Overview
The Admin Dashboard is the home screen for users with the `Admin` role. It displays at-a-glance statistics (total posts, categories, tags, users) via a set of animated stat cards. Each card links to the corresponding management page. The page is built as a Blade view extending the admin layout and includes custom JavaScript for interactive hover effects and a theoretical auto‑refresh mechanism.

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/dashboard.blade.php` | Main dashboard view with stats grid and inline scripts |

## View Hierarchy & Inheritance

layouts/admin.blade.php
└── @extends ('layouts.admin')
└── admin/dashboard.blade.php
├── @section('title', 'Admin Dashboard')
├── @section('content')
│ (stats cards rendered via inline Blade)
└── @section('scripts')
(dashboard-specific JavaScript)
text


## Detailed View: `resources/views/admin/dashboard.blade.php`

### Path and Purpose
The sole view for the Admin Dashboard. It expects to be the entry point for `Admin` users after login or via sidebar navigation.

### Data Dependencies
- **No explicit variables are passed from the controller.**  
  The view directly calls Eloquent models:  
  - `\App\Models\Post::count()`  
  - `\App\Models\Category::count()`  
  - `\App\Models\Tag::count()`  
  - `\App\Models\User::count()`  
  *Potential controller:* `Admin\DashboardController@index` – likely returns only the view.

### Blade Directives Used
- `@extends('layouts.admin')`  
- `@section('title', ...)` – sets page title  
- `@section('content')` – main content area  
- `@section('scripts')` – injects JavaScript  
- No complex `@if` or `@role` here, but the dashboard is only accessible through the sidebar, which uses `@role('Admin')` to show the link (see sidebar partial).

### Included Partials
None directly; the layout provides the sidebar via `@include('admin.partials._sidebar')`.

### Forms
No forms on this page.

### Conditional Rendering
None within this view – all four stat cards are always shown to an `Admin` user (the sidebar guards access).

### Links & Navigation
Each `.stat-card__action` link points to:
- `route('admin.posts.index')` → Manage Posts
- `route('admin.categories.index')` → Manage Categories
- `route('admin.tags.index')` → Manage Tags
- `route('admin.users.index')` → Manage Users

### Styling Notes
- Uses BEM‑like classes defined in `admin.css`: `.admin-dashboard`, `.page-header`, `.page-title`, `.stats-grid`, `.stat-card`, `.stat-card--primary`, etc.  
- Custom properties (CSS variables) are used for colors and spacing (`--admin-bg`, `--admin-primary`, etc.).  
- Responsive grid: single column on mobile, two columns on tablet, auto‑fit (min 280px) on desktop.

## CSS & JavaScript Specific to Admin Dashboard

### JavaScript (inline)
- **DOM Ready Logic**  
  - Adds `tabindex="0"` to stat cards for keyboard accessibility.  
  - Listens for `Enter` or `Space` key on card to trigger the action link.  
- **`DashboardStats` Object** (global)  
  - `updateStat(statType, newValue)`: animates the value of a stat card by scaling the text momentarily.  
  - `refreshStats()`: example AJAX call to `/api/admin/stats` to update all cards.  
- Optional auto‑refresh comment: `setInterval(DashboardStats.refreshStats, 300000);` (disabled by default).  

*Note:* The dashboard JS does **not** depend on `admin.js` but is pushed via `@section('scripts')`.

### CSS (from `admin.css`)
All relevant classes for stat cards, including loading animation and hover effects, are defined in `admin.css`. The dashboard page does not add any additional `<style>` blocks.

## Integration with Backend

| UI Action | Route | Controller Method | Authorization |
|-----------|-------|-------------------|---------------|
| Click “Manage Posts” | `admin.posts.index` | `PostAdminController@index` | Role: Admin or Editor |
| Click “Manage Categories” | `admin.categories.index` | `CategoryAdminController@index` | Role: Admin + `create` policy |
| Click “Manage Tags” | `admin.tags.index` | `TagAdminController@index` | Role: Admin + `create` policy |
| Click “Manage Users” | `admin.users.index` | `UserAdminController@index` | Role: Admin + `manage users` |

The dashboard itself is accessed via `route('admin.dashboard')` which is only shown to `Admin` role in the sidebar.

Flash messages (success/error) are rendered by the admin layout and appear as fixed floating cards.

## Edge Cases & UI States
- **Loading state**: The JS includes a `simulateLoading()` function that adds a `loading` class to the first stat card, triggering a shimmer animation. This can be used to test the loading UX.
- **No data state**: The stat cards always show a count – zero is a valid number, so no “empty state” view is needed.
- **Accessibility**: Keyboard navigation is built‑in for stat cards; focus styles are defined.
- **Responsive**: Cards stack vertically on small screens.
- **Auto‑refresh**: The code comments suggest an auto‑refresh every 5 minutes; it is disabled by default but can be activated.

---