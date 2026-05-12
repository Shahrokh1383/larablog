Admin Users
markdown

# User Management – Frontend Documentation

## Overview
The User Management module provides `Admin` users with an interface to view, ban/unban, soft‑delete, restore, and permanently delete user accounts. It includes a summary stats bar (Total, Active, Banned, Deleted), a filterable table with search, role, and status filters, and detailed user profile pages. The module strictly uses Laravel Policies (`UserPolicy`) and Spatie permissions (`manage users`, `ban`, `unban`).

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/users/index.blade.php` | User list with stats, filters, and row actions |
| `resources/views/admin/users/show.blade.php` | User detail with ban/delete modals and recent posts |

## View Hierarchy & Inheritance

layouts/admin.blade.php
├── admin/users/index.blade.php
└── admin/users/show.blade.php
text

Both extend `layouts.admin`.

## Data Flow (Controllers)
`UserAdminController`:
- `index` → returns:
  - `$users` – paginated collection of `User` models (including trashed? `withTrashed` based on status filter)
  - `$stats` – array with keys `total`, `active`, `banned`, `deleted`
  - `$roles` – list of role names for filter dropdown
  - `request('search')`, `request('role')`, `request('status')` for preserving filter state
- `show` → returns:
  - `$user` – the specific user with relationships
  - `$activity` – array with keys `last_post`, `posts_count`, `published_count`, `draft_count`
  - Various role/permission data

## Detailed Views

### 1. `admin/users/index.blade.php`

#### Path and Purpose
Displays a summary of users, filter controls, and a scrollable table with user details and actions. Row appearance changes: `table-danger` for trashed, `table-warning` for banned.

#### Data Dependencies
- `$users` (paginated, each with `id`, `name`, `email`, `avatar_url`, `roles` relationship, `trashed()`, `isBanned()`, `published_posts_count`, `last_activity`, `created_at`)
- `$stats` – array
- `$roles` – array of role names
- `request(...)` for old input

#### Blade Directives
- `@forelse($users as $user)` … `@empty`
- `@if($user->trashed())` / `@elseif($user->isBanned())` – row styling
- `@unless($user->trashed())` – show active actions (view, ban, unban, soft‑delete)
- `@can('ban', $user)`, `@can('unban', $user)`, `@can('delete', $user)`, `@can('manage users')` (only in sidebar)
- `{{ request('search') }}`, etc. for filter retention

#### Included Partials
None.

#### Forms

##### Filter Form
- Method: `GET`, action: same page
- Fields:
  - `search` (text)
  - `role` (select)
  - `status` (select)

##### Per‑User Forms/Modals

**Active Users:**
- **View Details**: Link to `route('admin.users.show', $user)`
- **Ban Modal** (if `@can('ban', $user)`):
  - Action: `route('admin.users.ban', $user)`, `POST` with `@csrf`
  - Contains a `reason` textarea (required)
- **Unban Button** (if `@can('unban', $user)`):
  - Direct form: `route('admin.users.unban', $user)`, `POST` with `@csrf`
- **Soft Delete Modal** (if `@can('delete', $user)`):
  - Action: `route('admin.users.destroy', $user)`, `POST` with `@method('DELETE')`, `@csrf`
  - Header: `bg-warning` “Move to Trash”

**Trashed Users:**
- **Restore Form**: `route('admin.users.restore', $user->id)`, `POST` with `@csrf`
- **Force Delete Modal**: `route('admin.users.forceDelete', $user->id)`, `POST` with `@method('DELETE')`
  - Header: `bg-danger` “Permanent Delete”

#### Conditional Rendering
- Ban/Unban buttons depend on policy and user’s banned state.
- Delete/Restore depend on trashed state.
- Role badges are coloured via custom classes (`.role-admin`, `.role-editor`, etc.).
- Status badges `.status-active`, `.status-banned`, `.status-deleted`.

#### Links
- User avatar/name no longer linked (view button handles navigation).
- “Manage Users” in sidebar guarded by `@can('manage users')`.

#### Styling
- Custom CSS pushed via `@push('styles')` for role and status badges, table container with sticky header and custom scrollbar.
- Table container has `max-height: 500px; overflow-y: auto;`.

### 2. `admin/users/show.blade.php`

#### Path and Purpose
Detailed user profile with avatar, role, ban info (if banned), stats cards, recent posts, and action buttons (ban/unban/delete).

#### Data Dependencies
- `$user` – the user model
- `$activity` – array with `last_post`, `posts_count`, `published_count`, `draft_count`
- The user’s posts are loaded in‑view via `$user->posts()->latest()->limit(10)->get()`.

#### Blade Directives
- `@unless($user->trashed())` – show active-state actions
- `@can('ban', $user)` / `@can('delete', $user)` – show buttons/modals
- `@if($user->isBanned())` – banner alert

#### Forms/Modals
- **Ban Modal** (same structure as index, with reason textarea)
- **Delete Modal** (soft delete)
- **Restore Form** (if trashed)

#### Conditional Rendering
- Ban alert shown only when banned.
- Recent posts table only if `$user->posts->isNotEmpty()`; otherwise empty state.
- Buttons hidden for trashed users except restore and force delete.

#### Links
- “Back” button → `route('admin.users.index')`
- “View All” → `route('author.show', $user)` (public profile)
- Breadcrumbs: Dashboard → Users → `$user->name`

#### Styling
- Inline style for `.role-badge` to increase size.
- Uses the same `.role-*` and `.status-*` classes as the index, but with larger padding.

## CSS & JavaScript Specific to Users
- **Inline styles (`@push('styles')`)** in `index.blade.php`:
  - Defines `.role-badge` gradient backgrounds for Admin, Editor, Author, User.
  - Defines `.status-badge` colours.
  - Configures scrollable table container with custom scrollbar and sticky header.
  - In `show.blade.php`, minor adjustments to role badge sizing.
- **JavaScript**: No custom scripts; all interactions rely on Bootstrap modals and standard form submissions. The `admin.js` file provides generic delete confirm (`delete-confirm`) but it is not used here; instead, modals are used.

Global admin CSS (`admin.css`) covers the stats cards (.stat-card) used in the user index summary and the general layout.

## Integration with Backend

| UI Action | Route | HTTP Method | Controller Method | Policy / Gate |
|-----------|-------|-------------|-------------------|---------------|
| List users | `admin.users.index` | GET | `UserAdminController@index` | `Admin` role + `manage users` |
| Show user | `admin.users.show` | GET | `UserAdminController@show` | `Admin` role + `manage users` |
| Ban user | `admin.users.ban` {user} | POST | `UserAdminController@ban` | `ban` policy |
| Unban user | `admin.users.unban` {user} | POST | `UserAdminController@unban` | `unban` policy |
| Soft delete user | `admin.users.destroy` {user} | DELETE | `UserAdminController@destroy` | `delete` policy |
| Restore user | `admin.users.restore` {id} | POST | `UserAdminController@restore` | `Admin` role |
| Force delete user | `admin.users.forceDelete` {id} | DELETE | `UserAdminController@forceDelete` | `Admin` role (likely) |

**Ban request**: Uses `AdminUserBanRequest` to validate `reason`.  
**Role management**: Not directly in these views; possibly a separate interface or handled by Spatie’s permission UI.

## Edge Cases & UI States
- **Banned user**: Row `table-warning` styling; detail page shows a red alert with ban reason, banner name, and date.
- **Soft‑deleted user**: Row `table-danger`; actions limited to restore and force delete.
- **Empty user list**: “No users found” message with icon.
- **Empty recent posts**: “No posts yet” placeholder.
- **Filter persistence**: All filter values are preserved in the form using `request(...)`.
- **Policies**: All destructive actions are guarded by `@can` directives; if a user tries to access a banning form without permission, it’s hidden. The backend also enforces via policies.
- **Modals**: Unique IDs prevent conflicts even with multiple modals on the same page.
- **Responsive**: The table scrolls horizontally; stats cards adapt to grid.
- **Sticky header**: Ensures table headers remain visible during vertical scroll.

---