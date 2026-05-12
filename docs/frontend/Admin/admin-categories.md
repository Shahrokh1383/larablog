Admin Categories
markdown

# Categories Management – Frontend Documentation

## Overview
The Categories module allows administrators (`Admin` role) to create, edit, and permanently delete categories. The UI consists of an index page with a scrollable table, a creation form, and an edit form that includes a “Danger Zone” for permanent deletion. All operations are governed by Laravel Policies (`CategoryPolicy`). The module extends the admin layout and relies on the global admin CSS/JS.

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/categories/index.blade.php` | List all categories with actions |
| `resources/views/admin/categories/create.blade.php` | Form to create a new category |
| `resources/views/admin/categories/edit.blade.php` | Form to edit an existing category (with delete zone) |

## View Hierarchy & Inheritance

layouts/admin.blade.php
├── admin/categories/index.blade.php
├── admin/categories/create.blade.php
└── admin/categories/edit.blade.php
text

All views `@extends('layouts.admin')`.

## Data Flow (Controllers)
Views are returned by `CategoryAdminController`:
- `index` → returns `$categories` (paginated, with `posts_count`)
- `create` → no data
- `edit` → returns `$category` (with `posts_count`)

## Detailed Views

### 1. `admin/categories/index.blade.php`

#### Path and Purpose
Lists categories in a table with inline edit/delete buttons and pagination. Only admins with the `create` policy see the “Add New Category” button; individual row actions are gated by `update` and `delete` policies.

#### Data Dependencies
- **`$categories`** – a paginated collection of `Category` models, each with:
  - `id`, `name`, `slug`, `description`, `posts_count`, `created_at`

#### Blade Directives Used
- `@can('create', \App\Models\Category::class)` – show “Add New” button
- `@if($categories->count())` – conditionally show table vs empty state
- `@foreach($categories as $category)` – iterate categories
- `@can('update', $category)` – show edit button per row
- `@can('delete', $category)` – show delete button (opens modal)
- `{{ $categories->links('pagination::bootstrap-5') }}` – pagination

#### Included Partials
None beyond the layout sidebar.

#### Forms
- **Delete Modal per Category**  
  - Form action: `route('admin.categories.destroy', $category)`  
  - Method: `POST` with `@method('DELETE')` and `@csrf`  
  - Modal header “bg-danger text-white” for permanent delete warning.  
  - No JavaScript confirmation – the submit button directly deletes.

#### Conditional Rendering
- Empty state: `<div class="text-center py-5">` with icon and prompt if no categories exist.
- Create button only visible if user has `create` policy.
- Edit/Delete buttons only visible per category if policies allow.

#### Links & Navigation
- “Add New Category” → `route('admin.categories.create')`
- Edit icon → `route('admin.categories.edit', $category)`
- Breadcrumbs / sidebar entry point: `route('admin.categories.index')`

#### Styling Notes
- A `<style>` block forces the card to `height: 60vh; overflow-y: scroll;` to create a scrollable table container.
- Table uses Bootstrap classes `table`, `table-hover`, `table-responsive`.
- The empty state uses large Bootstrap Icons (`bi-folder-x`).

### 2. `admin/categories/create.blade.php`

#### Path and Purpose
Displays a centred form for creating a new category.

#### Data Dependencies
- No pre‑loaded data; uses `old()` helper for form recycling.

#### Blade Directives
- `@csrf`
- `@error('name')`, `@error('slug')`, `@error('description')` – inline validation errors.

#### Forms
- **Method**: `POST` to `route('admin.categories.store')`
- Fields:
  - `name` (required, text, with `old('name')`)
  - `slug` (optional, text, with `old('slug')`; a live preview span shows `URL: /categories/<slug-preview>`)
  - `description` (textarea, with character counter `desc-count`; form‑text shows `/1000 characters`)
- **Buttons**: Cancel (returns to index) and Submit.

#### JavaScript (inline from layout or pushed)
- The character counter for description is not fully implemented in the provided code (only a `<span id="desc-count">0</span>` placeholder). A full implementation would require a small script updating the count on `input` – this can be added later.

#### Conditional Rendering
None.

#### Styling
- Centered card with `col-md-8 col-lg-6`.
- Form layout uses standard Bootstrap 5.

### 3. `admin/categories/edit.blade.php`

#### Path and Purpose
Pre‑filled form to edit a category, plus a “Danger Zone” inline for permanent deletion.

#### Data Dependencies
- **`$category`** – the existing category model with `name`, `slug`, `description`, `posts_count`.

#### Blade Directives
- `@method('PUT')` inside form
- `@can('delete', $category)` – shows danger zone
- `old('name', $category->name)`, etc. for value fallback

#### Forms
- **Edit form**  
  - Action: `route('admin.categories.update', $category)`  
  - Method: `POST` with `@method('PUT')`
  - Same fields as create, pre‑filled with `old('...', $category->...)`.  
- **Delete form** (danger zone)  
  - Action: `route('admin.categories.destroy', $category)`  
  - Method: `POST` with `@method('DELETE')`  
  - Shows `onclick="return confirm(...)"` for client‑side confirmation.

#### Conditional Rendering
- The “Delete Category” button and entire danger zone only appear if `@can('delete', $category)` passes.

#### Links & Navigation
- “Cancel” returns to `route('admin.categories.index')`.

#### Styling
- Danger zone has a dashed border and `.bg-danger.bg-opacity-10` to visually separate it.

## CSS & JavaScript Specific to Categories
- **Inline styles**: The index page and edit page have minor `<style>` pushes (card height, danger zone styling).  
- **No custom JavaScript** is required; the delete modals rely on Bootstrap’s JS. The character count in create/edit is currently static and would need a small script to fully function.

All admin‑wide CSS (stat cards, flash messages, sidebar responsiveness) is supplied by `admin.css` loaded via `@vite('resources/css/admin.css')` in the layout.

## Integration with Backend

| UI Action | Route | Controller Method | Policy / Gate |
|-----------|-------|-------------------|---------------|
| List categories | `admin.categories.index` | `CategoryAdminController@index` | `Admin` role (sidebar) |
| Show create form | `admin.categories.create` | `CategoryAdminController@create` | `create` policy |
| Store category | `admin.categories.store` | `CategoryAdminController@store` | `create` policy; validated by `CategoryRequest` |
| Show edit form | `admin.categories.edit` | `CategoryAdminController@edit` | `update` policy |
| Update category | `admin.categories.update` | `CategoryAdminController@update` | `update` policy; validated by `CategoryRequest` |
| Delete category | `admin.categories.destroy` | `CategoryAdminController@destroy` | `delete` policy |

**Flash messages**: Success/error messages are flashed from the controller and rendered as floating glassmorphism cards by the layout (see `layouts/admin.blade.php`). The index page also shows a standard `alert-success` if `session('success')` exists (duplicated from the layout, but the layout already handles it).

## Edge Cases & UI States
- **Empty state**: When no categories exist, a large muted icon with helper text and a call‑to‑action button (if authorized) is displayed.
- **Validation errors**: Forms use `@error` to display inline feedback with Bootstrap’s `is-invalid` class. Old input is retained.
- **Permanent deletion**: Both the index modal and the edit danger zone clearly warn that deletion is permanent and affects all associated posts (the policy ensures only admins with `delete` permission can do this).
- **Permission denied**: The `@can` directives hide actionable elements. If a user navigates to the URL directly without permission, the controller/form request will return a 403.
- **Responsive**: Forms are centred and responsive; the table scrolls on small screens.
- **Soft deletes**: Categories are not soft‑deleted in this system (deletion is permanent); no “trashed” state exists.

---