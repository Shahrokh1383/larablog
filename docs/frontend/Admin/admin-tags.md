Admin Tags
markdown

# Tags Management – Frontend Documentation

## Overview
The Tags module provides CRUD operations for tags, accessible only to users with the `Admin` role. The UI mirrors the Categories module: a scrollable index table, create and edit forms, and permanent deletion via modals or a “Danger Zone”. Authorization is enforced through the `TagPolicy`.

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/tags/index.blade.php` | List tags with actions |
| `resources/views/admin/tags/create.blade.php` | Create new tag form |
| `resources/views/admin/tags/edit.blade.php` | Edit tag form with delete danger zone |

## View Hierarchy & Inheritance

layouts/admin.blade.php
├── admin/tags/index.blade.php
├── admin/tags/create.blade.php
└── admin/tags/edit.blade.php
text

All extend `layouts.admin`.

## Data Flow (Controllers)
`TagAdminController`:
- `index` → returns `$tags` (paginated, with `posts_count`)
- `create` → no data
- `edit` → returns `$tag` (with `posts_count`)

## Detailed Views

### 1. `admin/tags/index.blade.php`

#### Path and Purpose
Lists tags in a scrollable table with edit/delete actions. The “Add New Tag” button is shown only if the user has the `create` policy.

#### Data Dependencies
- **`$tags`** – paginated collection of `Tag` models (with `id`, `name`, `slug`, `posts_count`, `created_at`).

#### Blade Directives
- `@can('create', \App\Models\Tag::class)` – controls “Add New Tag” button
- `@foreach($tags as $tag)`
- `@can('update', $tag)` / `@can('delete', $tag)` – row‑level actions
- `{{ $tags->links('pagination::bootstrap-5') }}`

#### Forms
- **Delete Modal** (per tag)  
  - Action: `route('admin.tags.destroy', $tag)`  
  - Method: `POST` with `@method('DELETE')` and `@csrf`  
  - Header: `bg-danger text-white`, warning of permanent deletion.

#### Conditional Rendering
- Empty state: large `bi-tag-x` icon, “No tags found” message, and a creation CTA if authorized.
- Action buttons only appear if the corresponding policy allows.

#### Links
- “Add New Tag” → `route('admin.tags.create')`
- Edit → `route('admin.tags.edit', $tag)`

#### Styling
- Card height `60vh` and overflow scroll, exactly like categories.
- Table uses `table-responsive`.

### 2. `admin/tags/create.blade.php`

#### Path and Purpose
Centred card form for a single tag name and slug.

#### Data Dependencies
- None; uses `old()`.

#### Forms
- **Action**: `route('admin.tags.store')`, `POST`
- Fields:
  - `name` (required, `old('name')`)
  - `slug` (optional, `old('slug')`)
- No description field.
- Character count hint is not present.

#### Blade Directives
- `@csrf`
- `@error(...)` for validation feedback.

#### Styling
- Inline `<style>` pushes card-body height to `70vh`.

### 3. `admin/tags/edit.blade.php`

#### Path and Purpose
Edit form with pre‑filled values and a danger zone for permanent deletion.

#### Data Dependencies
- `$tag` (with `name`, `slug`, `posts_count`)

#### Forms
- **Edit form**  
  - Action: `route('admin.tags.update', $tag)`, `POST` with `@method('PUT')`
- **Delete form**  
  - Action: `route('admin.tags.destroy', $tag)`, `POST` with `@method('DELETE')`
  - Client‑side `confirm()` via `onclick`.

#### Blade Directives
- `@can('delete', $tag)` – guards the danger zone.

#### Links
- Cancel returns to `route('admin.tags.index')`.

## CSS & JavaScript Specific to Tags
- Minor inline styles (card height, danger zone colours) are pushed via `@push('styles')`.
- No custom JavaScript is required; the standard admin.js handles sidebar, and Bootstrap handles modals.

All global admin styles are from `admin.css`.

## Integration with Backend

| UI Action | Route | Controller Method | Policy |
|-----------|-------|-------------------|--------|
| List tags | `admin.tags.index` | `TagAdminController@index` | `Admin` role (sidebar) |
| Show create form | `admin.tags.create` | `TagAdminController@create` | `create` policy |
| Store tag | `admin.tags.store` | `TagAdminController@store` | `create` policy; validated by `TagRequest` |
| Show edit form | `admin.tags.edit` | `TagAdminController@edit` | `update` policy |
| Update tag | `admin.tags.update` | `TagAdminController@update` | `update` policy; validated by `TagRequest` |
| Delete tag | `admin.tags.destroy` | `TagAdminController@destroy` | `delete` policy |

**Flash messages**: Success/error notifications are handled by the layout’s floating flash container.

## Edge Cases & UI States
- **Empty table**: Displayed with large icon and CTA if no tags exist.
- **Validation**: Inline error messages with `@error`; old input preserved.
- **Delete warning**: Modal makes it clear the action is permanent and affects associated posts.
- **Policy enforcement**: Buttons hidden if user lacks permissions; direct URL access returns 403.
- **Responsive design**: Forms are stacked on mobile; table scrolls horizontally.

---