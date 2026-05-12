Admin Posts
```markdown
# Posts Management – Frontend Documentation

## Overview
The Posts Management page is the central hub for `Admin` and `Editor` users to manage blog posts. It offers filtering by status (`published`, `draft`, `trashed`), by author, and a search field. The table supports select‑all checkboxes, bulk actions (publish, draft, delete) via modal, and per‑post actions: edit, view, soft‑delete (move to trash), restore, and permanent deletion. Soft‑deleted posts are visually distinct with strikethrough styling.

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/posts/index.blade.php` | Main index page with filters, table, bulk actions, and inline modals |

## View Hierarchy & Inheritance

layouts/admin.blade.php
└── admin/posts/index.blade.php
Extends `layouts.admin`.

## Data Dependencies
This view is returned by `PostAdminController@index` and expects:
- **`$posts`** – paginated `LengthAwarePaginator` of `Post` models, including soft‑deleted ones if filter `trashed` is selected. Each post has:
  - `id`, `title`, `status`, `featured_image`, `views`, `created_at`
  - `author` relation (nullable), `categories` relation (with count)
  - `comments()` relation for comment count
  - `trashed()` method for soft‑delete check
- **`$authors`** – associative array `[id => name]` for the author dropdown.
- Possibly `request('...')` values for preselected filters.

## Detailed View: `admin/posts/index.blade.php`

### Path and Purpose
Renders a fully interactive post management table. It includes a filter bar, a table with checkboxes, rating system, and three sets of modals: per‑post soft‑delete modal, per‑post force‑delete modal (for trashed posts), and a global bulk action modal. Also provides restore and force‑delete buttons for trashed items.

### Blade Directives Used
- `@forelse($posts as $post)` … `@empty` (for empty state)
- `{{ $post->trashed() ? 'table-light' : '' }}` – row styling
- `@if($post->trashed())` – shows restore/force delete buttons
- `@forelse($post->categories->take(2) ...)` – shows first two categories
- `@can(...)` not explicitly used; authorization is likely checked by the controller. (Policy gates may be used to restrict certain actions, but the view shows all actions for the logged‑in admin.)
- `{{ request('status') == '...' ? 'selected' : '' }}` – retain filter state.

### Included Partials
None.

### Forms

#### Filter Form
- **Method**: `GET`, action: `route('admin.posts.index')`
- Fields:
  - `status` dropdown (submit on change via `onchange="this.form.submit()"`)
  - `author` dropdown
  - `search` text input
  - Submit button

#### Hidden Bulk Action Form
- **ID**: `bulk-action-form`
- **Action**: `route('admin.posts.bulk-action')`, method `POST` with `@csrf`
- Hidden fields: `action` (set dynamically), and multiple `ids[]` created by JavaScript.

#### Per‑Post Actions

**For Active (non‑trashed) Posts:**
- **Soft Delete Modal** (`deleteModal{{ $post->id }}`)
  - Form: `route('admin.posts.destroy', $post)`, `POST` with `@method('DELETE')`, `@csrf`
  - Header: `bg-warning`, titled “Move to Trash”.
  - Submitting moves the post to trash.

**For Trashed Posts:**
- **Restore Form** (direct)
  - Action: `route('admin.posts.restore', $post->id)`, `POST` with `@csrf`
  - No confirmation; button with `btn-outline-success`.
- **Force Delete Modal** (`forceDeleteModal{{ $post->id }}`)
  - Form: `route('admin.posts.forceDelete', $post->id)`, `POST` with `@method('DELETE')`, `@csrf`
  - Header: `bg-danger`, permanent deletion warning.

#### Bulk Action Modal
- ID: `bulkActionModal`
- Not a form; it calls JavaScript function `executeBulkAction(action)` which populates the hidden form and submits it.
- Buttons: Publish, Move to Draft, Delete (all trigger the same hidden form with different `action` values).

### JavaScript (@push('scripts'))
Extensive JavaScript for:
- `toggleSelectAll()` – selects/deselects all checkboxes.
- `updateBulkCount()` – updates the indeterminate state of select‑all, and manages hidden `ids[]` inputs in the bulk action form.
- `executeBulkAction(action)` – validates selection, sets the action, updates hidden inputs, hides the modal, and submits the bulk form.
- Event listeners on checkboxes and select‑all to dynamically update the form.

*Note:* The function `toggleBulkActions()` is defined in `admin.js` but not directly used in this view; instead the modal is always accessible via a button.

### Conditional Rendering
- **Empty state**: If no posts match filters, a row with colspan shows a Bootstrap icon and message “No posts found”.
- **Row styling**: Trashed posts get `table-light` with strikethrough CSS via the `<style>` block.
- **Action visibility**: Trashed posts get restore and force‑delete; active posts get edit, view, soft‑delete.

### Links & Navigation
- “Create New Post” → `route('posts.create')` (frontend post creation, not admin)
- Post title → `route('posts.show', $post)` (opens in new tab)
- Author name → `route('author.show', $post->author)`
- Edit button → `route('posts.edit', $post)`

### Styling Notes
- Inline `<style>` includes:
  - Card height `65vh; overflow-y: scroll;`
  - `tr.table-light td` with `text-decoration: line-through; color: #6c757d;` for trashed rows.
- Uses Bootstrap’s `table-responsive`, `btn-group`, `modal`.

## CSS & JavaScript Specific to Posts
- **Inline styles** handle the scrolling table and trashed-row appearance.
- **JavaScript** functions are self‑contained in the `@push('scripts')` section; the `admin.js` file includes a `toggleSelectAll` that targets `.post-checkbox` (not `.item-checkbox`), a bug fix indicated in the code comments. The `updateBulkCount` function in `admin.js` mirrors the one in the view’s script, but the view’s script takes precedence.

All admin layout styles (sidebar, topbar, flash) are from `admin.css`.

## Integration with Backend

| UI Action | Route | HTTP Method | Controller Method | Authorization |
|-----------|-------|-------------|-------------------|---------------|
| List/filter posts | `admin.posts.index` | GET | `PostAdminController@index` | Admin or Editor role (sidebar) |
| Soft delete | `admin.posts.destroy` {post} | DELETE | `PostAdminController@destroy` | Likely `delete` policy (PostPolicy) |
| Restore | `admin.posts.restore` {id} | POST | `PostAdminController@restore` | Admin only? |
| Force delete | `admin.posts.forceDelete` {id} | DELETE | `PostAdminController@forceDelete` | Admin only |
| Bulk action | `admin.posts.bulk-action` | POST | `PostAdminController@bulkAction` | Admin/Editor? |

**Note:** The bulk action form includes `@csrf` but no `@method`; the controller likely handles publish/draft/delete logic.

**Flash messages**: Success/error after actions are shown via the layout’s floating flash cards.

## Edge Cases & UI States
- **No posts**: Centralized empty state with icon and message.
- **Trashed posts**: Distinct visual style; restore and force‑delete actions available.
- **Bulk action with no selection**: JavaScript alerts the user “Please select at least one post…”.
- **Select‑all with mixed states**: The select‑all checkbox becomes indeterminate when some checkboxes are checked.
- **Pagination**: Links are shown at the bottom.
- **Author dropdown**: Lists all authors (passed as `$authors`); selecting an author filters the posts.
- **Search**: Debounced via `admin.js` (500ms delay) on search inputs; auto‑submits the filter form.
- **Permission handling**: The view assumes the user has sufficient permissions; if not, the controller should return 403. Currently there are no `@can` directives in the posts view.
- **Multiple modals**: Each post has unique modal IDs to prevent JavaScript conflicts.

---