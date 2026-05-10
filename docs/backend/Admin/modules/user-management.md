**Admin User Management – Documentation**  

> This document covers **Admin‑exclusive** user operations: listing, viewing, role changes, banning/unbanning, soft deletion, restoration and permanent deletion. It is a complement to the general [User Management](../user-management.md) module — for authentication, registration, user profiles and public author pages please refer to that document.

---

## 1. Purpose & Scope  

The admin user management module provides a complete interface for administrators to oversee and control all registered accounts. It exposes:

- A searchable, filterable, paginated user list with real‑time statistics.
- Detailed user profiles with activity summaries.
- Role reassignment (Admin, Editor, Author, User) with safeguards.
- Banning and unbanning, including token revocation.
- Safe deletion (soft‑delete) that cascades to user’s posts, plus restoration.
- Permanent deletion (force delete) only possible after prior soft‑delete.

All actions are protected by the `Admin` role middleware and fine‑grained `UserPolicy` rules.

---

## 2. Files at a Glance  

| File | Role |
|------|------|
| `app/Http/Controllers/Admin/UserAdminController.php` | Controller handling all admin user CRUD operations |
| `app/Http/Requests/AdminUserBanRequest.php` | Validates ban reason before the ban action |
| `app/Http/Requests/AdminUserRoleRequest.php` | Validates the new role value |
| `app/Policies/UserPolicy.php` | Authorization rules for all user management actions |
| `app/Models/User.php` | User model with ban fields, scopes, soft‑delete cascade |
| `app/Http/Middleware/CheckRole.php` | Role‑based middleware enforcing `role:Admin` |
| `app/Providers/AuthServiceProvider.php` | Policy registration and `Gate::before` for Admins |
| `routes/web.php` (admin user section) | Route definitions prefixed with `admin/` |
| `database/migrations/…_add_ban_fields_to_users_table.php` | Adds `banned_at`, `banned_by`, `ban_reason`, `softDeletes` |
| `database/migrations/…_create_permission_tables.php` | Spatie role/permission tables |
| `database/migrations/…_create_users_table.php` | Original users table schema |

---

## 3. Controller `UserAdminController`  

**Namespace:** `App\Http\Controllers\Admin`  
**Applied middleware:** `auth` + `role:Admin` (constructor, duplicated with route middleware).  

All public methods use `Gate::authorize()` to enforce `UserPolicy` rules.

### 3.1 `index(Request $request)`  

**Route:** `GET /admin/users` — `admin.users.index`  

**Description:**  
Displays a paginated list of all users (including soft‑deleted when filtered).  

**Features / Query parameters:**

| Parameter | Type   | Description |
|-----------|--------|-------------|
| `search`  | string | Searches `name` or `email` (partial match) |
| `role`    | string | One of `Admin`, `Editor`, `Author`, `User` – filters by role |
| `status`  | string | `active` (not banned), `banned`, `deleted` (only trashed) |
| `sort`    | string | Column to sort by (default `created_at`) |
| `dir`     | string | `asc` or `desc` (default `desc`) |

**Statistics passed to view:**  
- `total` – non‑trashed user count  
- `active` – not banned users  
- `banned` – banned users  
- `deleted` – only soft‑deleted users  

**Policies:** Indirectly via `viewAny` (called inside view, but no explicit `Gate` call here; the middleware already restricts to Admin).  

**View:** `admin.users.index`

---

### 3.2 `show(User $user)`  

**Route:** `GET /admin/users/{user}` — `admin.users.show`  

**Description:**  
Displays a single user with loaded relationships: posts, roles, permissions, and the admin who banned them (`bannedBy`). Also computes an activity summary.  

**Activity summary (method `getUserActivity`):**  
- `last_post` – timestamp of the user’s most recent post  
- `posts_count` – total number of posts  
- `published_count` – published posts (using the `published_posts_count` accessor)  
- `draft_count` – draft posts  
- `member_since` – user creation date  

**Policies:** `Gate::authorize('view', $user)` → UserPolicy::view  

**Note:** The accessor `published_posts_count` relies on the `published()` scope on Post, not relevant to admin logic directly.

---

### 3.3 `updateRole(AdminUserRoleRequest $request, User $user)`  

**Route:** `PUT /admin/users/{user}/role` — `admin.users.updateRole`  

**Validation:**  
- `role` required, string, one of `User, Author, Editor, Admin`  

**Authorization:** `Gate::authorize('updateRole', $user)` → UserPolicy::updateRole  
- Admin only, cannot change own role, cannot demote the last admin.  

**Logic:**  
1. Retrieve old role name (`$user->roles->first()->name`).  
2. Call `syncRoles([$request->role])` – replaces all existing roles with the single new one.  
3. Log the change (old role → new role, performed by).  

**Returns:** Redirect to `admin.users.index` with success message.

---

### 3.4 `ban(AdminUserBanRequest $request, User $user)`  

**Route:** `POST /admin/users/{user}/ban` — `admin.users.ban`  

**Validation:**  
- `reason` required, string, min 10, max 500 characters  

**Authorization:** `Gate::authorize('ban', $user)` → UserPolicy::ban  
- Admin only, cannot ban self, cannot ban last admin, target must not already be banned.  

**Logic:**  
1. Update user’s `banned_at` (now), `banned_by` (current admin id), `ban_reason`.  
2. Delete **all Sanctum personal access tokens** of the user (`$user->tokens()->delete()`) – this revokes API access but does **not** destroy current web sessions.  
3. Log the ban event.  

**Returns:** Redirect to index with success message.

---

### 3.5 `unban(User $user)`  

**Route:** `POST /admin/users/{user}/unban` — `admin.users.unban`  

**Authorization:** `Gate::authorize('unban', $user)` → UserPolicy::unban  
- Admin only, target must currently be banned.  

**Logic:**  
- Set `banned_at`, `banned_by`, `ban_reason` to `null`.  
- Log the action.  

**Returns:** Redirect to index.

---

### 3.6 `destroy(User $user)`  

**Route:** `DELETE /admin/users/{user}` — `admin.users.destroy`  

**Description:** Soft‑deletes the user.  

**Authorization:** `Gate::authorize('delete', $user)` → UserPolicy::delete  
- Admin only, cannot delete self, cannot delete last admin.  

**Logic:**  
- Call `$user->delete()` (triggers the model’s `deleting` event → soft‑deletes all user’s posts).  
- Log the soft deletion.  

**Returns:** Redirect to index with success message.

---

### 3.7 `restore($id)`  

**Route:** `POST /admin/users/{id}/restore` — `admin.users.restore`  

**Description:** Restores a soft‑deleted user (and cascades to their posts).  

**Authorization:** `Gate::authorize('restore', $user)` → UserPolicy::restore (Admin only).  

**Logic:**  
- Find user by ID using `onlyTrashed()`, then call `$user->restore()`.  
- The model’s `restoring` event restores all soft‑deleted posts.  
- Log the action.  

**Returns:** Redirect to index (with `status=deleted` to stay in trashed view) with success message.

---

### 3.8 `forceDelete($id)`  

**Route:** `DELETE /admin/users/{id}/force-delete` — `admin.users.forceDelete`  

**Description:** Permanently deletes a soft‑deleted user and all their posts.  

**Authorization:** `Gate::authorize('forceDelete', $user)` → UserPolicy::forceDelete  
- Admin only, user must be trashed.  

**Logic:**  
- Call `$user->forceDelete()`. The model’s `deleting` event force‑deletes all associated posts.  
- Log the permanent deletion.  

**Returns:** Redirect to index (with `status=deleted`) with success message.

---

## 4. Form Requests  

### 4.1 `AdminUserBanRequest`  

**Authorization:** `$this->user()->hasRole('Admin')` – ensures only admins can submit.  

**Rules:**  

| Field    | Rules | Description |
|----------|-------|-------------|
| `reason` | required, string, min:10, max:500 | Reason for banning the user |

No custom messages; uses Laravel defaults.

---

### 4.2 `AdminUserRoleRequest`  

**Authorization:** `$this->user()->hasRole('Admin')`.  

**Rules:**  

| Field | Rules | Description |
|-------|-------|-------------|
| `role` | required, string, in:User,Author,Editor,Admin | Must be one of the four allowed roles |

---

## 5. Authorization (Policies & Gates)  

### 5.1 `UserPolicy`  

All methods receive the authenticated `$user` and the subject `$subject`.  
Helper `isLastAdmin()` returns `true` when the total count of users with the `Admin` role is ≤ 1.

**Methods used by admin controller:**  

| Ability | Parameters | Conditions |
|---------|------------|------------|
| `viewAny` | $user | `hasRole('Admin')` |
| `view` | $user, $subject | `hasRole('Admin')` |
| `updateRole` | $user, $subject | Admin, cannot change own role, cannot demote last admin |
| `ban` | $user, $subject | Admin, cannot ban self, cannot ban last admin, target must not already be banned |
| `unban` | $user, $subject | Admin & subject is banned |
| `delete` | $user, $subject | Admin, cannot delete self, cannot delete last admin |
| `restore` | $user, $subject | `hasRole('Admin')` |
| `forceDelete` | $user, $subject | Admin & subject is trashed |

**Profile update policy (`update`)** – not directly used by admin, but follows `$user->id === $subject->id || $user->hasRole(['Admin', 'Editor'])`.  

### 5.2 Global `Gate::before`  

In `AuthServiceProvider::boot()`, a `Gate::before` callback grants **all abilities** to any user with the `Admin` role:  

```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Admin') ? true : null;
});
```

This means that for most actions an `Admin` will pass even without checking the policy. However, the policy methods still contain explicit checks for self‑modification and last‑admin protection, which override the global bypass (**because returning `false` from a policy method overrides a previous `true`**). Therefore the fine‑grained restrictions (can’t ban self, can’t delete last admin) remain effective.

### 5.3 Custom Gates  

Defined in `AuthServiceProvider`:  

- `view-admin-panel` – `hasRole('Admin|Editor')`  
- `manage-users` – `hasRole('Admin')`  

These are not directly called by the controller but could be used in views or other middleware.

### 5.4 Policy Registration  

```
Post::class => PostPolicy::class,
Category::class => CategoryPolicy::class,
Tag::class => TagPolicy::class,
User::class => UserPolicy::class,
```

The `User` model is mapped to `UserPolicy`, enabling `Gate::authorize()` calls.

---

## 6. Middleware  

### 6.1 `CheckRole` (alias `role`)  

Applied at the route group level and also in the constructor.  

**Behavior:**  
- Accepts one or more role names (e.g., `role:Admin`).  
- If user is unauthenticated → redirect to login.  
- If user lacks **any** of the required roles → abort 403 after logging the attempt.  
- Priority set to 10 (runs after auth).  

For the admin user routes, the middleware is `role:Admin`, ensuring only administrators ever reach the controller. The redundant constructor check is harmless.

---

## 7. Routes (Admin User Management)  

All routes are inside `web.php` under `Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')`.

| Method   | URI                          | Controller Method  | Route Name                  | Description |
|----------|------------------------------|-------------------|-----------------------------|-------------|
| GET      | `admin/users`                | `index`           | `admin.users.index`         | User list (search, filter, sort) |
| GET      | `admin/users/{user}`         | `show`            | `admin.users.show`          | User details and activity |
| PUT      | `admin/users/{user}/role`    | `updateRole`      | `admin.users.updateRole`    | Change user role |
| POST     | `admin/users/{user}/ban`     | `ban`             | `admin.users.ban`           | Ban user with reason |
| POST     | `admin/users/{user}/unban`   | `unban`           | `admin.users.unban`         | Unban user |
| DELETE   | `admin/users/{user}`         | `destroy`         | `admin.users.destroy`       | Soft‑delete user |
| POST     | `admin/users/{id}/restore`   | `restore`         | `admin.users.restore`       | Restore soft‑deleted user |
| DELETE   | `admin/users/{id}/force-delete` | `forceDelete`     | `admin.users.forceDelete`   | Permanently delete user |

**Note:** The `restore` and `force-delete` routes use explicit `{id}` instead of `{user}` because they operate on trashed models that would not be resolved by implicit route‑model binding (which excludes soft‑deleted records by default).

---

## 8. Model `User` – Admin‑specific Aspects  

### 8.1 Ban Fields  

| Column     | Type        | Description |
|------------|-------------|-------------|
| `banned_at` | timestamp, nullable | If set, the user is banned |
| `banned_by` | unsignedBigInteger, FK→users, nullable, `nullOnDelete` | Admin who performed the ban |
| `ban_reason` | text, nullable | Reason for ban |

**Casts:** `banned_at` → `datetime`.  

**Scopes:** `scopeNotBanned($query)` (`whereNull('banned_at')`), `scopeBanned($query)` (`whereNotNull('banned_at')`).  

**Helper:** `isBanned()` returns `true` when `banned_at !== null`.

### 8.2 Relationships  

- `bannedBy()` – belongsTo(User, 'banned_by') → the admin who banned this user.  
- `updater()` – belongsTo(User, 'updated_by') – **non‑functional** because the `updated_by` column does not exist (see §10).  

### 8.3 Soft‑delete Cascade  

Defined in the model’s `boot()`:  

- **`deleting`:** If force‑deleting, force‑deletes all posts; otherwise soft‑deletes them.  
- **`restoring`:** Restores all soft‑deleted posts.  

This ensures referential integrity without hard database constraints.

### 8.4 Other Admin‑Relevant Methods  

- `isAdmin()`, `isEditor()`, `isAuthor()` – role checks via Spatie.  
- `getPublishedPostsCountAttribute()` – count of published posts (used in activity summary).  
- `getLastActivityAttribute()` – returns human‑readable time based on `last_login_at` and latest post. **Important:** `last_login_at` column is missing, causing an error if called (see §10).  
- `unreadNotificationsCount()` – native notification count.

---

## 9. Cross‑References to General User Documentation  

The admin module extends the base user system. For details on:  

- Database schema, roles and permissions seeding,  
- Authentication flows (login, registration, password reset),  
- User dashboard and author profiles,  
- The overall image service and avatar handling,  

please refer to the [User Management](../user-management.md) document.  

The authorization middleware (`CheckRole`) and policy registration are described in the [Authorization & Middleware](../../authorization-and-middleware.md) document (to be created).

---

## 10. Known Issues & Constraints  

| Issue | Severity | Impact |
|-------|----------|--------|
| `last_login_at` column missing | **Critical** | The `User::getLastActivityAttribute()` accessor will throw a database error if called (e.g., in admin views that display user activity). An immediate migration is required. |
| `updated_by` column missing | Medium | The `updater()` relationship remains non‑functional. No admin views currently use it. |
| Ban does not invalidate current web sessions | Medium | Banning a user removes API tokens but **not** existing session cookies. A banned user may remain logged in until their session expires or they log out. |
| Duplicate dashboard route | **Critical** | The `/dashboard` route is defined twice in `web.php`, which causes a Laravel runtime error. This affects the admin panel’s own dashboard as well. Must be resolved before the application can run. |
| Redundant middleware | Low | The controller constructor applies `auth` and `role:Admin` again, but the route group already enforces these. No functional impact. |
| No admin interface to edit full user profiles | Low | The admin can only change roles and ban status; updating name, email, bio, etc., is not yet available. |

---

## 11. Practical Workflows  

### 11.1 Banning a User  

1. Admin navigates to `GET /admin/users`, selects a user, clicks “Ban”.  
2. A form captures a reason (min 10 chars).  
3. Form submits to `POST /admin/users/{user}/ban` with the `reason` field.  
4. `AdminUserBanRequest` validates the input; authorization ensures only Admins can submit.  
5. The controller calls `Gate::authorize('ban', $user)` → policy checks (not self, not last admin, not already banned).  
6. User’s `banned_at`, `banned_by`, `ban_reason` are updated.  
7. All Sanctum tokens are deleted via `$user->tokens()->delete()`.  
8. Action is logged with severity `warning`.  
9. Redirect to index with success message.  

### 11.2 Changing a User’s Role  

1. Admin accesses the user’s detail page or a direct role‑edit interface.  
2. Selects a new role (`User, Author, Editor, Admin`) and submits to `PUT /admin/users/{user}/role`.  
3. `AdminUserRoleRequest` validates the role value; `Gate::authorize('updateRole', $user)` prevents self‑demotion or removal of the last admin.  
4. `syncRoles([$newRole])` assigns the new role (replaces any previous).  
5. The old and new roles are logged.  
6. Redirect to index with success message.

### 11.3 Soft‑Deleting and Restoring  

- **Delete:** `DELETE /admin/users/{user}` → policy check (not self, not last admin), then soft‑delete. The user’s posts are automatically soft‑deleted.  
- **Restore:** `POST /admin/users/{id}/restore` → locates trashed user, policy allows restoration, then restores user and cascades to posts.

---

## 12. Security & Audit  

- **Logging:** Every admin action (role change, ban, unban, delete, restore, force delete) is logged with the performing admin’s ID, target user ID, and any relevant details.  
- **Token revocation on ban:** Ensures that banned users lose API access immediately (though web sessions persist – see §10).  
- **Last admin protection:** The policy prevents removal of the final admin account via role change, ban, or deletion.  
- **Middleware enforcement:** The `role:Admin` middleware and the controller constructor both enforce presence of the Admin role, providing depth of defence.

---

> **Next steps:** After completing this document, the following related docs should be written:  
> - `Authorization & Middleware` (describing `CheckRole`, all Policies, and `AuthServiceProvider` in detail)  
> - `Services & Logging` (covering `ImageService`, log formatters, and event listeners)  
> - `Database & Seeding` (migrations and seeders relevant to admin features)