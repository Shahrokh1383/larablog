# Authorization & Middleware — Documentation

> **Purpose:** Complete reference to the authorization system, including role middleware, Spatie permissions, policies, gates, Form Request authorization, and their integration.  
> **Prerequisite knowledge:** Laravel’s authentication, Spatie’s role/permission package, and basic Laravel authorization concepts.  
> **Related documents:**  
> - [Admin User Management](user-management.md) – practical use of policies in admin user operations.  
> - [Admin Dashboard Widgets](dashboard-widgets.md) – gate check `view-admin-panel`.  
> - [Authentication](authentication.md) – login, registration, and role assignment at sign-up.  
> - Module-specific documents for posts, categories, tags, comments (public and admin).

---

## 1. Overview

LaraBlog uses a multi-layered authorization system to protect admin routes and control user actions across the application:

1. **Route Middleware** – `auth` and a custom `CheckRole` (aliased `role`) to immediately reject unauthenticated users or those lacking the required role(s).
2. **Spatie Permissions** – a set of granular permissions (e.g., `manage categories`, `publish posts`) assigned to roles (`Admin`, `Editor`, `Author`, `User`).
3. **Laravel Policies** – model-specific authorization logic for views, updates, deletes, and special actions (ban, approve, etc.).
4. **Gates & Global Bypass** – custom Gates defined in `AuthServiceProvider`, plus a `Gate::before` callback that grants **all** abilities to any user with the `Admin` role, overriding policies unless the policy explicitly returns `false`.
5. **Form Request `authorize()`** – each Form Request checks permissions via Gates or policies before validation, adding a defence-in-depth layer.

Because Admin users are super‑users, the global bypass eliminates most explicit permission checks for them. However, **policy methods that protect critical actions (e.g., banning yourself, deleting the last admin) still explicitly return `false` and take precedence**, ensuring those safeguards cannot be bypassed.

---

## 2. File Manifest

| File | Role |
|------|------|
| `app/Http/Middleware/CheckRole.php` | Custom middleware that checks if the authenticated user possesses one of the required roles. |
| `database/seeders/RoleSeeder.php` | Defines all roles and their initial permissions. |
| `config/permission.php` | Spatie package configuration (models, table names, cache, etc.). |
| `app/Providers/AuthServiceProvider.php` | Registers policies, defines custom Gates, and implements the `Gate::before` Admin bypass. |
| `app/Policies/UserPolicy.php` | Authorization for user management (view, change role, ban, delete, restore). |
| `app/Policies/PostPolicy.php` | Authorization for post operations (view, create, update, delete, publish, restore). |
| `app/Policies/CategoryPolicy.php` | Authorization for category management (view, create, update, delete). |
| `app/Policies/TagPolicy.php` | Authorization for tag management (view, create, update, delete). |
| `app/Policies/CommentPolicy.php` | Authorization for comments (view, create, update, delete, approve). |
| `app/Http/Requests/AdminUserBanRequest.php` | Validates and authorises the ban action (role‑based). |
| `app/Http/Requests/AdminUserRoleRequest.php` | Validates and authorises role changes. |
| `app/Http/Requests/CategoryRequest.php` | Authorises category CRUD via `Gate::allows('manage categories')` or policies. |
| `app/Http/Requests/CommentRequest.php` | Authorises comment creation/update/deletion via policies. |
| `app/Http/Requests/PostRequest.php` | Authorises post CRUD via policies. |
| `app/Http/Requests/TagRequest.php` | Authorises tag CRUD via `Gate::allows('manage tags')` or policies. |
| `routes/web.php` | Defines route groups with `auth` and `role` middleware; also applies `can:` middleware on some routes. |
| `app/Models/User.php` | Provides `hasRole()`, `hasPermissionTo()`, `isAdmin()`, `isBanned()` helpers. |
| `app/Providers/EventServiceProvider.php` | (Not directly authorization, but included for completeness.) |

---

## 3. Roles and Permissions

### 3.1 Spatie Integration

The application uses the `spatie/laravel-permission` package. All roles and permissions are stored in the database tables created by `2025_12_22_172347_create_permission_tables.php`. The package configuration (`config/permission.php`) uses:

- **Guard:** `web` (the default guard).
- **Models:** standard Spatie `Permission` and `Role` models.
- **Cache:** 24‑hour cache for permissions, flushed automatically on changes.

The `User` model uses the `HasRoles` trait, which provides methods like `assignRole()`, `hasRole()`, `hasPermissionTo()`, `givePermissionTo()`, and scopes like `scopeRole()`.

### 3.2 Roles (from `RoleSeeder`)

Four roles are seeded, each with a specific set of permissions. The seeder **truncates all existing permission/role data**—suitable only for development.

| Role   | Intent                        | Permissions Assigned |
|--------|-------------------------------|----------------------|
| Admin  | Super‑user, full access       | **All** permissions (full list below). |
| Editor | Content manager               | `view posts`, `create posts`, `edit posts`, `delete posts`, `publish posts`, `manage comments`, `approve comments`, `manage categories`, `manage tags`, `view admin panel`. |
| Author | Content creator (own content) | `view posts`, `create posts`, `edit posts`, `publish posts`, `manage comments`. |
| User   | Regular member (read‑only)    | `view posts`. |

**Important:** The `User` role is automatically assigned to every new registration (see `RegisterController`). The `Admin` role is never assigned automatically; an administrator must manually promote a user via the admin panel.

### 3.3 Permissions List

The complete list of permissions defined in the seeder:

- `view posts`
- `create posts`
- `edit posts`
- `delete posts`
- `publish posts`
- `create comments`
- `manage comments`
- `approve comments`
- `delete comments`
- `manage categories`
- `manage tags`
- `manage users`
- `view admin panel`

**How permissions map to abilities:**  
- Policies often use `$user->hasPermissionTo('permission-name')` to decide authorization. For example, `PostPolicy@create` checks `create posts`.  
- The `can:` route middleware (`can:manage categories`) uses Laravel’s Gate, which thanks to Spatie’s permission registration automatically translates `manage categories` into the corresponding permission check.  
- Custom Gates (`view-admin-panel`, `manage-users`) use `hasRole()` for simplicity, but they could equally rely on permissions.

---

## 4. Middleware: `CheckRole` (alias `role`)

### 4.1 Class Details

**File:** `app/Http/Middleware/CheckRole.php`  
**Priority:** `10` (runs after the `auth` middleware ensures a user is authenticated). The priority is set via `getPriority()` method; Laravel respects this to order middleware execution.

### 4.2 Behaviour

The middleware accepts one or more role names as parameters (e.g., `role:Admin`, `role:Admin|Editor`). Logic:

1. If no roles are passed (`empty($roles)`), it allows the request to proceed. (This fallback is rarely used; all route groups explicitly provide at least one role.)
2. If the user is **not authenticated**, it redirects to the `login` route with an error flash message.
3. It loops through the required roles and calls `$request->user()->hasRole($role)`. If the user has **any** of the required roles, access is granted.
4. If the user lacks **all** required roles, the middleware:
   - Logs a warning with `Log::warning()`, including user ID, required roles, actual roles, URL, and IP address.
   - Aborts with a `403 Forbidden` response.

**Key point:** The check is **OR**‑based: `role:Admin|Editor` grants access to anyone who is either an Admin or an Editor.

### 4.3 Usage in Routes

All admin routes are grouped using this middleware:

```php
// GROUP 1: Strictly Admin Only
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(...);

// GROUP 2: Admin & Editor (Posts)
Route::middleware(['auth', 'role:Admin|Editor'])->prefix('admin')->name('admin.')->group(...);
```

Additionally, the `UserAdminController` constructor duplicates the middleware (`$this->middleware(['auth', 'role:Admin'])`), which is redundant but harmless.

**Effect:**  
- Any route in Group 1 (dashboard, user management, categories, tags, comments) is inaccessible to Editors, Authors, or Users—they receive a 403 before reaching the controller.  
- Group 2 (post management) allows Editors as well.

---

## 5. Gates and Global Admin Bypass

### 5.1 `AuthServiceProvider::boot()`

The provider registers policies and then sets up two mechanisms:

```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Admin') ? true : null;
});
```

This is a **global before callback**. For every authorization check (Gate, Policy, `can` middleware, etc.), if the authenticated user has the `Admin` role, the callback returns `true` immediately, **bypassing any policy method**.  

**Exception:** If a policy method explicitly returns `false` (not `null`), that `false` overrides the `true` from the `before` callback. Laravel resolves this as: `before` can return a result, but if a policy returns a concrete `false`, it takes precedence. This is documented Laravel behavior. Therefore, critical safeguards (e.g., “Admin cannot ban themselves”) are still enforced because the policy returns `false` directly.

### 5.2 Custom Gates

Two custom gates are defined:

- **`view-admin-panel`** – returns `$user->hasRole('Admin|Editor')`. This gate is used in the `AdminDashboardController` via `Gate::authorize('view-admin-panel')`. Because the route middleware already restricts that route to `Admin` only, this check is currently redundant for Admins, but it would allow Editors if the route middleware were ever relaxed.
- **`manage-users`** – returns `$user->hasRole('Admin')`. It is defined but **not called anywhere** in the current codebase. It serves as documentation and could be used in views or future middleware.

Both gates bypass the policy system because `Gate::before` will grant Admin immediate `true`; for non‑admins, the `hasRole` check runs normally.

---

## 6. Policy Registration

Policies are registered in `AuthServiceProvider::$policies`:

```php
protected $policies = [
    Post::class     => PostPolicy::class,
    Category::class => CategoryPolicy::class,
    Tag::class      => TagPolicy::class,
    User::class     => UserPolicy::class,
];
```

**Critical omission:** `CommentPolicy` is **not** registered. Laravel’s auto‑discovery of policies is not enabled (the class does not override `shouldDiscoverPolicies()` to return `true`). As a result, any call to `Gate::authorize('update', $comment)` or `$this->authorize('delete', $comment)` will fail with an “unauthorized” exception **unless** the Admin global bypass kicks in (which returns `true` for Admins). For non‑admin users, all policy‑based comment actions will be denied even if the policy would normally allow them. This is a known issue that must be fixed (see §10).

The `UserPolicy` is registered, so all `Gate::authorize` calls in `UserAdminController` work as intended.

---

## 7. Detailed Policy Definitions

Each policy class contains methods that receive the currently authenticated user (`$user`) and, where applicable, the model instance being acted upon (`$subject`). The following tables summarise each policy’s rules.

### 7.1 `UserPolicy`

| Ability      | Parameters        | Conditions |
|--------------|-------------------|------------|
| `viewAny`    | $user             | `$user->hasRole('Admin')` |
| `view`       | $user, $subject   | `$user->hasRole('Admin')` |
| `updateRole` | $user, $subject   | 1. `$user->hasRole('Admin')` <br/> 2. `$user->id !== $subject->id` (cannot change own role) <br/> 3. If `$subject->isAdmin()` then `! $this->isLastAdmin()` (cannot demote last admin) |
| `ban`        | $user, $subject   | 1. `$user->hasRole('Admin')` <br/> 2. `$user->id !== $subject->id` <br/> 3. If `$subject->isAdmin()`, then `! $this->isLastAdmin()` (cannot ban last admin) <br/> 4. ` ! $subject->isBanned()` (target not already banned) |
| `unban`      | $user, $subject   | `$user->hasRole('Admin')` and `$subject->isBanned()` |
| `delete`     | $user, $subject   | 1. `$user->hasRole('Admin')` <br/> 2. `$user->id !== $subject->id` <br/> 3. If `$subject->isAdmin()`, then `! $this->isLastAdmin()` |
| `restore`    | $user, $subject   | `$user->hasRole('Admin')` |
| `forceDelete`| $user, $subject   | `$user->hasRole('Admin')` and `$subject->trashed()` |
| `update`     | $user, $subject   | `$user->id === $subject->id` **or** `$user->hasRole(['Admin', 'Editor'])` (used for public profile updates, not admin panel) |

**Helper:** `isLastAdmin()` returns `true` if the total number of users with the `Admin` role is ≤ 1. It uses `User::role('Admin')->count() <= 1`.

### 7.2 `PostPolicy`

| Ability      | Parameters        | Conditions |
|--------------|-------------------|------------|
| `viewAny`    | $user             | `true` (public) |
| `view`       | ?$user, $post     | Public if post is published (`$post->isPublished()`). Otherwise, only if `$user` is the author or has `Admin|Editor` role. |
| `create`     | $user             | `$user->hasPermissionTo('create posts')` |
| `update`     | $user, $post      | `Admin|Editor` can update any post. Authors can update own post if they have `edit posts` permission. |
| `delete`     | $user, $post      | Admin can delete any. Editor can delete if the post author is **not** an Admin. Authors can delete own post with `delete posts` permission. |
| `publish`    | $user             | `$user->hasPermissionTo('publish posts')` |
| `restore`    | $user, $post      | `Admin|Editor` |
| `forceDelete`| $user, $post      | `Admin` only |

### 7.3 `CategoryPolicy`

| Ability      | Parameters           | Conditions |
|--------------|----------------------|------------|
| `viewAny`    | $user                | `$user->hasPermissionTo('manage categories')` |
| `view`       | $user, $category     | `true` (public) |
| `create`     | $user                | `$user->hasPermissionTo('manage categories')` |
| `update`     | $user, $category     | `$user->hasPermissionTo('manage categories')` |
| `delete`     | $user, $category     | Admin can always delete. Others with `manage categories` permission **cannot** delete if the category has associated posts (`$category->posts()->exists()`). |

### 7.4 `TagPolicy`

| Ability      | Parameters      | Conditions |
|--------------|-----------------|------------|
| `viewAny`    | $user           | `$user->hasPermissionTo('manage tags')` |
| `view`       | $user, $tag     | `true` (public) |
| `create`     | $user           | `$user->hasPermissionTo('manage tags')` |
| `update`     | $user, $tag     | `$user->hasPermissionTo('manage tags')` |
| `delete`     | $user, $tag     | Admin always. Others with `manage tags` cannot delete if tag has posts (`$tag->posts()->exists()`). |

### 7.5 `CommentPolicy`

| Ability      | Parameters          | Conditions |
|--------------|---------------------|------------|
| `viewAny`    | $user               | `$user->hasPermissionTo('manage comments')` |
| `view`       | $user, $comment     | Public if `$comment->approved`. Otherwise, visible to users with `approve comments` permission **or** the comment author. |
| `create`     | ?$user = null       | Guests are allowed if `config('blog.allow_guest_comments', true)` is `true`. Authenticated users require `create comments` permission (or if null, defaults to `true` because of `?? true`). |
| `update`     | $user, $comment     | Author can edit within 15 minutes of creation. Users with `manage comments` permission can edit any. |
| `delete`     | $user, $comment     | Admin can delete any. Editor can delete if comment author is not Admin. Comment author can delete own with `delete comments` permission. |
| `approve`    | $user               | `$user->hasPermissionTo('approve comments')` |

**Note:** Because `CommentPolicy` is unregistered, all policy-based authorization for comments will fail for non‑Admin users (see §10).

---

## 8. Form Request Authorization

Each Form Request’s `authorize()` method provides an additional authorization layer before validation runs.

| Form Request | Authorization Logic |
|--------------|---------------------|
| `AdminUserBanRequest` | `$this->user()->hasRole('Admin')` – pure role check, bypassing policies. |
| `AdminUserRoleRequest` | `$this->user()->hasRole('Admin')` – same. |
| `CategoryRequest` | Uses `match($this->method())`: `POST` → `Gate::allows('create categories')` (which maps to `create` ability in CategoryPolicy); `PUT/PATCH` → `Gate::allows('update', $this->route('category'))`; `DELETE` → `Gate::allows('delete', $this->route('category'))`. |
| `CommentRequest` | `POST` (guest) allows if `config('blog.allow_guest_comments')` is true; authenticated uses `Gate::allows()` for update/delete on the comment route model. Authorization for `POST` when authenticated is left to the controller. |
| `PostRequest` | `POST` → `Gate::allows('create posts')` (PostPolicy@create); `PUT/PATCH` → `Gate::allows('update', $post)`; `DELETE` → `Gate::allows('delete', $post)`. |
| `TagRequest` | Same pattern as CategoryRequest: uses `Gate::allows('create tags')`, `update`, `delete` abilities. |
| `DashboardProfileRequest` | `return true` – no authorization needed; it’s for the user’s own profile update, handled by a separate gate/policy? Actually, the user dashboard controller uses this; it’s safe because it only updates the authenticated user’s data. |

**Impact of Admin bypass:** For Admin users, all these `Gate::allows()` calls will return `true` because of the global `Gate::before`, unless the policy returns `false` for a specific reason (e.g., deleting a category with posts is prevented by `CategoryPolicy@delete` returning `false`, which overrides the bypass).

---

## 9. Route Middleware & Access Control Summary

### 9.1 Guest Routes

All authentication routes (login, register, password reset) are wrapped in `Route::middleware('guest')`. No role checks.

### 9.2 Authenticated User Routes

Routes like `/dashboard`, `/dashboard/profile`, `POST /logout`, and the front-end post creation/editing are protected by `auth` only. No role restrictions.

### 9.3 Admin Panel – Strictly Admin (Group 1)

**Middleware:** `auth` + `role:Admin`  
**Access:** Only Admins.  
**Routes covered:** `admin.dashboard`, all `admin.users.*`, `admin.categories.*`, `admin.tags.*`, `admin.comments.*`.  
**Additional middleware on categories/tags:** `can:manage categories` and `can:manage tags` are also applied. For Admins, the `can` middleware passes because of the global bypass (or the permission directly). For Editors, these routes are already blocked by `role:Admin`, so the `can` middleware would never be reached.

### 9.4 Admin Panel – Posts (Group 2)

**Middleware:** `auth` + `role:Admin|Editor`  
**Access:** Admins and Editors.  
**Routes covered:** `admin.posts.*` (resource plus bulk action, restore, force‑delete).  
No additional `can` middleware; authorization is handled inside the controller via `$this->authorize()` and the Form Requests.

### 9.5 Author Routes

**Middleware:** `auth` + `role:Admin|Editor|Author`  
**Access:** All content-creating roles. Used for the author profile edit page.

**Note:** The public `/authors` and `/author/{user}` routes are open; they merely list public profiles and do not require roles.

---

## 10. Integration with Admin Modules

### 10.1 User Management Workflow

- Route group: `role:Admin`.
- Controller methods call `Gate::authorize('ability', $user)` explicitly (e.g., `Gate::authorize('ban', $user)`).
- For Admins, the `Gate::before` returns `true`, but policy checks like “cannot ban self” still apply because the policy method returns `false` directly.
- Form Requests `AdminUserBanRequest` and `AdminUserRoleRequest` perform their own `hasRole('Admin')` check, ensuring only Admins can submit.

### 10.2 Dashboard Access

- Route `GET /admin` is in Group 1 (`role:Admin`), so only Admins can load it.
- The controller also calls `Gate::authorize('view-admin-panel')`, which for Admins is instantly granted by the bypass. If the middleware were changed to include Editors, that gate would allow them.

### 10.3 Category/Tag Management

- Routes are protected by both `role:Admin` and `can:manage categories / manage tags`. The `can` middleware checks the gate, which for Admins is always true (bypass). For an Editor (if they somehow bypassed the `role:Admin` middleware), the `can` middleware would check the permission `manage categories`, which they possess, so they would pass. Thus the double protection is safe.

### 10.4 Post Management (Admin & Editor)

- The `PostAdminController` uses policy‑based `$this->authorize()` calls. For Admins, the bypass applies; for Editors, the policy methods are actually evaluated.
- Example: Editor can delete a post only if the post author is not an Admin. This is enforced by `PostPolicy::delete()`.

### 10.5 Comment Moderation

- Routes are strictly Admin only (`role:Admin`), so only Admins can reach `CommentAdminController`. The controller methods likely use `Gate::authorize` or `$this->authorize()` with the comment abilities, but because `CommentPolicy` is unregistered, **these calls would fail for non‑Admins**—but since non‑Admins are already blocked by middleware, the bug is latent. Nevertheless, it should be fixed for correctness.

---

## 11. Known Issues & Critical Gaps

| Issue | Severity | Impact |
|-------|----------|--------|
| **`CommentPolicy` not registered** | **Critical** | `Gate::authorize('update', $comment)` etc. will throw an authorization exception for any user without the Admin role (because Admin bypass hides the bug). If an Editor were ever granted access to comment moderation via route middleware change, they would be unable to moderate. **Fix:** Add `Comment::class => CommentPolicy::class` to `AuthServiceProvider::$policies`. |
| **Redundant `role` middleware in `UserAdminController` constructor** | Low | No functional impact; duplicate check. |
| **`Gate::before` Admin bypass may mask missing permission checks** | Medium | Developers might assume certain policy logic is active when it is being skipped. Ensure that critical restrictions are implemented with explicit `false` returns. |
| **`manage-users` gate defined but unused** | Low | No immediate issue; can be removed or used in views. |
| **Missing `last_login_at` and `updated_by` columns (from authentication module)** | Info | These issues are documented in `authentication.md`; they do not affect authorization directly but can cause runtime errors in user activity tracking. |

**Recommendation:** Immediately register `CommentPolicy` and verify that all policy classes are covered. Consider writing an integration test that iterates over all policies to ensure they are correctly mapped.

---

## 12. Troubleshooting Quick Reference

| Symptom | Probable Cause | Solution |
|---------|----------------|----------|
| 403 Forbidden on `/admin` for an Admin user | Middleware `role:Admin` expects user to have the `Admin` role. Check `$user->hasRole('Admin')` returns true. Verify Spatie roles are seeded and the user actually has the role. | Run `php artisan db:seed --class=RoleSeeder` and assign the role to the user via tinker: `User::find(id)->assignRole('Admin')`. |
| Editor cannot access `/admin/posts` | The route group uses `role:Admin|Editor`. Ensure the user has the `Editor` role, not just permissions. | Check `$user->roles`; assign via `$user->assignRole('Editor')`. |
| `Gate::authorize('update', $comment)` throws unauthorized even for Admin | Admin global bypass should make it pass; if it fails, the policy may be throwing an exception earlier (e.g., missing model). | Verify the comment model exists and is passed correctly; also check that `CommentPolicy` is not registered (unregistered policy would cause Gate to find no policy, but the bypass should still trigger; however, if Gate cannot find a policy, it returns `false`? Actually, `Gate::authorize` without a policy returns `false` by default, but the `before` callback would still intercept and return `true` for Admins. So it should work). If it’s failing, confirm `AuthServiceProvider::boot` is executed and `Gate::before` is properly set. |
| Non‑admin user can access admin dashboard after changing route middleware | The controller uses `Gate::authorize('view-admin-panel')`, which checks `$user->hasRole('Admin|Editor')`. If the user has neither, they get a 403. | Ensure the user has at least the `Editor` role or update the gate definition. |
| `manage categories` route middleware blocks Admin | The `can:manage categories` middleware checks the permission. Admin bypass should override. If it fails, the `Gate::before` may not be registered. | Confirm `AuthServiceProvider` is properly booted; check for typos. |

---

> **Next:** For a complete overview of the admin functionality, refer to the [Admin Dashboard](dashboard-widgets.md) and the individual admin module documents: [user management](user-management.md), content management, taxonomy management, comment moderation. This authorization document serves as the foundational reference for all access control decisions throughout the admin panel.