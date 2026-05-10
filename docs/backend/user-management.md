## 1. Overview

The User Management module governs all aspects of identity, authentication, role‑based access, profile management, and account lifecycle (ban, soft‑delete, restore). It is built on Laravel’s native authentication layer, extended with **Spatie/laravel-permission** and **Laravel Sanctum** for future API token support. The module integrates deeply with blogging features: users own posts and comments, their activity feeds the admin dashboard and public author profiles.

**Core responsibilities:**
- Registration with auto‑assigned `User` role
- Login/logout with ban detection, session regeneration, and last‑login tracking
- Password reset (dev‑friendly flow that skips email)
- Role/permission system (Admin, Editor, Author, User)
- Admin panel user management (search, filter, sort, ban/unban, role change, soft‑delete/restore/force‑delete)
- Profile editing (dashboard and author‑specific routes) with avatar upload using `ImageService`
- Ban system that blocks future logins and revokes API tokens
- Soft‑delete cascade: soft‑deleting a user soft‑deletes their posts; restoration cascades back

---

## 2. File Manifest

| File | Responsibility |
|------|----------------|
| `config/auth.php` | Authentication guards, password broker, defaults |
| `config/permission.php` | Spatie permission settings, tables, caching |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Creates `users`, `password_reset_tokens`, `sessions` |
| `database/migrations/2025_12_22_172347_create_permission_tables.php` | Creates Spatie’s permission tables |
| `database/migrations/2025_12_29_214913_add_ban_fields_to_users_table.php` | Adds ban columns and `softDeletes` to `users` |
| `database/migrations/2025_12_29_234333_create_personal_access_tokens_table.php` | Creates Sanctum’s token table |
| `app/Models/User.php` | Eloquent model: traits, fillable, casts, relationships, scopes |
| `database/seeders/RoleSeeder.php` | Seeds roles and permissions |
| `database/seeders/UserSeeder.php` | Seeds initial users |
| `database/seeders/DatabaseSeeder.php` | Orchestrates seeding |
| `app/Http/Controllers/Auth/LoginController.php` | Login form, login, logout |
| `app/Http/Controllers/Auth/RegisterController.php` | Registration form, account creation |
| `app/Http/Controllers/Auth/ForgotPasswordController.php` | Dev‑mode password reset link |
| `app/Http/Controllers/Auth/ResetPasswordController.php` | Password reset execution |
| `app/Http/Controllers/Admin/UserAdminController.php` | Admin user management CRUD |
| `app/Http/Controllers/DashboardController.php` | User dashboard and profile update |
| `app/Http/Controllers/AuthorController.php` | Public author listing and profile editing (own) |
| `app/Http/Requests/Auth/LoginRequest.php` | Validates login input |
| `app/Http/Requests/Auth/RegisterRequest.php` | Validates registration and provides user data |
| `app/Http/Requests/AdminUserBanRequest.php` | Validates ban reason |
| `app/Http/Requests/AdminUserRoleRequest.php` | Validates role assignment |
| `app/Http/Requests/DashboardProfileRequest.php` | Validates dashboard profile update |
| `app/Http/Requests/ProfileRequest.php` | Validates full author profile (with social links) |
| `app/Policies/UserPolicy.php` | Authorization for admin actions and profile updates |
| `app/Http/Middleware/CheckRole.php` | Role‑based middleware |
| `app/Services/ImageService.php` | Avatar storage and thumbnail generation |
| `app/Console/Commands/CreateStorageDirectories.php` | Creates required storage folders |
| `routes/web.php` | All route definitions |

---

## 3. Database Schema

### 3.1 `users` table
Created by: `0001_01_01_000000_create_users_table.php` and `2025_12_29_214913_add_ban_fields_to_users_table.php`.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned (PK) | no | auto‑increment | Primary key |
| `name` | varchar(255) | no | – | Display name |
| `email` | varchar(255) | no | – | Unique (indexed) |
| `email_verified_at` | timestamp | yes | null | Set to current time on registration |
| `password` | varchar(255) | no | – | Bcrypt hashed |
| `avatar` | varchar(255) | yes | null | Path relative to storage disk (original) |
| `bio` | text | yes | null | Short biography |
| `social_links` | json | yes | null | JSON object: `twitter`, `github`, `linkedin`, `website` |
| `remember_token` | varchar(100) | yes | null | For “remember me” |
| `banned_at` | timestamp | yes | null | If set, user is banned |
| `banned_by` | foreignId (`users.id`) | yes | null | Admin who banned (nullOnDelete) |
| `ban_reason` | text | yes | null | Reason for ban |
| `deleted_at` | timestamp | yes | null | Soft delete |
| `created_at` | timestamp | no | – | |
| `updated_at` | timestamp | no | – | |

**Important missing column:**  
`last_login_at` – used in `LoginController` and `User::getLastActivityAttribute()` but **no migration is provided**. The application will throw a database error unless added manually. This is a **critical bug**.

**Implicit missing column:**  
`updated_by` – referenced by the `updater()` relationship in the model but no column exists. The relationship is unusable.

### 3.2 `password_reset_tokens`
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `email` | varchar(255) PK | no | User email |
| `token` | varchar(255) | no | Reset token |
| `created_at` | timestamp | yes | |

### 3.3 `sessions`
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | varchar(255) PK | no | Session ID |
| `user_id` | bigint unsigned (indexed) | yes | |
| `ip_address` | varchar(45) | yes | |
| `user_agent` | text | yes | |
| `payload` | longtext | no | Serialized session data |
| `last_activity` | int (indexed) | no | |

### 3.4 Spatie Permission Tables
All migrated by `2025_12_22_172347_create_permission_tables.php`. Teams feature **disabled**.

| Table | Key columns | Purpose |
|-------|-------------|---------|
| `permissions` | `id`, `name`, `guard_name` (unique pair) | Permission definitions |
| `roles` | `id`, `name`, `guard_name` (unique pair) | Role definitions |
| `model_has_permissions` | `permission_id`, `model_type`, `model_id` (composite PK) | Direct permissions on models |
| `model_has_roles` | `role_id`, `model_type`, `model_id` (composite PK) | Role assignments to models |
| `role_has_permissions` | `permission_id`, `role_id` (composite PK) | Permissions linked to roles |

All use `guard_name = 'web'`.

### 3.5 `personal_access_tokens` (Sanctum)
| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint unsigned (PK) | no | |
| `tokenable_type` | varchar | no | |
| `tokenable_id` | bigint unsigned | no | |
| `name` | text | no | |
| `token` | varchar(64) unique | no | Hashed token |
| `abilities` | text | yes | JSON |
| `last_used_at` | timestamp | yes | |
| `expires_at` | timestamp (indexed) | yes | |
| `created_at` | timestamp | no | |
| `updated_at` | timestamp | no | |

---

## 4. Models

### 4.1 `User` (`App\Models\User`)
**Traits:** `HasApiTokens`, `HasFactory`, `Notifiable`, `HasRoles`, `SoftDeletes`  

**Fillable:**  
`name`, `email`, `password`, `avatar`, `bio`, `social_links`, `email_verified_at`, `banned_at`, `banned_by`, `ban_reason`

**Hidden:** `password`, `remember_token`

**Casts:**
- `email_verified_at` → `datetime`
- `password` → `hashed` (Laravel 10+)
- `social_links` → `array`
- `banned_at` → `datetime`

**Relationships:**
- `posts()`: `HasMany` to `Post` (foreign key `user_id`)
- `bannedBy()`: `BelongsTo` to `User` (foreign key `banned_by`) – admin who banned this user
- `updater()`: `BelongsTo` to `User` (foreign key `updated_by`) – **⚠️ Column `updated_by` does not exist in the database**; this relationship is non‑functional

**Accessors:**
- `getAvatarUrlAttribute()`: Returns Gravatar if no avatar; otherwise checks for thumbnail version (`thumb/...`), falls back to original; returns full public URL

**Scopes:**
- `scopeNotBanned($query)`: `whereNull('banned_at')`
- `scopeBanned($query)`: `whereNotNull('banned_at')`

**Custom Methods:**
- `isAdmin()`, `isEditor()`, `isAuthor()` – check role via Spatie
- `isBanned()`: `$this->banned_at !== null`
- `unreadNotificationsCount()`: unread notifications count (native `Notifiable`)
- `getPublishedPostsCountAttribute()`: count of published posts
- `getLastActivityAttribute()`: returns human‑readable time of last login (`last_login_at`) or last post, whichever is newer

**Boot Logic:**
- `deleting`: If force‑deleting (`isForceDeleting()`), force‑delete all related posts; otherwise soft‑delete them
- `restoring`: Restores all soft‑deleted posts of this user

---

## 5. Roles & Permissions

Seeded by `RoleSeeder`. All guard names are `web`. Each role has exactly one set of permissions (users have only one role at a time; `syncRoles` ensures that).

| Role | Permissions |
|------|-------------|
| **Admin** | All 13 permissions (full access) |
| **Editor** | `view posts`, `create posts`, `edit posts`, `delete posts`, `publish posts`, `manage comments`, `approve comments`, `manage categories`, `manage tags`, `view admin panel` |
| **Author** | `view posts`, `create posts`, `edit posts`, `publish posts`, `manage comments` |
| **User** | `view posts` only |

---

## 6. Seeding

`DatabaseSeeder` calls `RoleSeeder` then `UserSeeder`.  

**RoleSeeder** truncates all permission data, recreates permissions, and assigns them to roles.  
**UserSeeder** creates four fixed users (`admin@larablog.test`, `editor@...`, `author@...`, `user@...`, all password `password123`) and 5 random `Author` users via factories.

---

## 7. Authentication Flow

### 7.1 Registration
1. Guest reaches `GET /register` (view `auth.register`).
2. Submission `POST /register` validated by `RegisterRequest`.
   - Name (3‑255 chars, letters/spaces), email (unique, confirmed), password (strong, confirmed), terms accepted.
   - `getUserData()` returns `name`, `email`, hashed password, `email_verified_at = now()`.
3. `RegisterController` adds `remember_token`, default bio `"New member at LaraBlog"`, empty `social_links`, `null` avatar, creates user.
4. Assigns `User` role.
5. Fires `Registered` event (no listener configured for email verification).
6. Auto‑logs in the user and redirects to `dashboard` with success message.

### 7.2 Login
1. `GET /login` shows `auth.login`.
2. `POST /login` : `LoginRequest` validates email (exists), password, optional `remember`.
3. `Auth::attempt(credentials, remember)`.
4. On success:
   - Session regenerated, CSRF token regenerated.
   - **Ban check:** if `isBanned()` is true, immediately logout, invalidate session, regenerate CSRF token, redirect back with error.
   - Otherwise updates `last_login_at` (⚠️ missing column) and redirects to intended (`dashboard`) with success.
5. On failure: logs warning, throws `ValidationException`.

### 7.3 Logout
- `POST /logout` (auth required)
- Logs out user, invalidates session, regenerates CSRF token, logs action, redirects to home.

### 7.4 Password Reset (Development Mode)
`ForgotPasswordController` does **not** send emails. Instead:
1. `POST /password/email` validates email existence.
2. Creates a reset token via `Password::createToken($user)`.
3. Redirects directly to `GET /password/reset/{token}` with `email` and `token` as query parameters.
4. `ResetPasswordController@showResetForm` shows `auth.passwords.reset`.
5. `POST /password/reset` validates token, email, strong password (confirmed). Uses `Password::reset` to update password, fires `PasswordReset` event, logs, redirects to login.

Real email sending code is commented out; to enable it, uncomment and configure Laravel’s notification system.

---

## 8. User Dashboard & Profile Management

### 8.1 Dashboard (`DashboardController`)
- **`GET /dashboard`** (auth) – Shows dashboard view with user info; for creators (Admin, Editor, Author) adds stats: published posts count, total comments, total views.
- **`PUT /dashboard/profile`** (auth) – Updates name, email, bio. Validated by `DashboardProfileRequest`:
  - Name/email unique (ignoring current user), bio max 1000 chars.
  - Avatar image (nullable, max 2MB, jpeg/png/jpg/gif/webp).
  - `delete_avatar` boolean to remove avatar.
  - Within a DB transaction, updates user fields, handles avatar upload/delete via `ImageService`, logs.

### 8.2 Author Profile (`AuthorController`)
Used by content creators to view public profiles and edit their own profile.

- **`GET /authors`** – Public list of users with Admin/Editor/Author roles, searchable, paginated (12).
- **`GET /author/{user}`** – Public author profile with published posts, stats.
- **`GET /profile/edit`** (auth + role: Admin|Editor|Author) – Shows edit form **only for the authenticated user’s own profile** (hardcoded check `$user->id === auth()->id()`). This overrides generic policy.
- **`PUT /profile`** (auth + same role middleware) – Same strict own‑profile check. Uses `ProfileRequest` (extends base profile fields with `social_links` validation). Within transaction updates profile, handles avatar. Redirects to author profile.

**Key difference from Dashboard:** Author profile editing includes social links and uses a different request class, but still enforces the user can only edit themselves.

---

## 9. Admin User Management (`UserAdminController`)

**Middleware applied in constructor:** `auth` and `role:Admin` (in addition to route middleware, creating redundancy but harmless).

All actions are also protected by `Gate::authorize()` (using `UserPolicy`) inside methods.

### 9.1 List Users
`GET /admin/users` – provides:
- **Search:** `?search=` searches `name` or `email`.
- **Role filter:** `?role=Admin|Editor|Author|User`.
- **Status filter:** `?status=banned|active|deleted`.
- **Sort:** `?sort=column&dir=asc|desc` (default: `created_at desc`).
- **Pagination:** 15 per page, with query string appended.
- Includes statistics: total (count of non‑trashed users), active, banned, deleted counts.

### 9.2 View User
`GET /admin/users/{user}` – Loads user with posts, roles, permissions, bannedBy; displays activity summary (last post, counts).

### 9.3 Update Role
`PUT /admin/users/{user}/role` – Validated by `AdminUserRoleRequest`: role must be one of `User, Author, Editor, Admin`.  
Policy checks: admin only, cannot change own role, cannot demote the last admin.  
Performs `syncRoles([$newRole])`, logs change.

### 9.4 Ban / Unban
- **Ban:** `POST /admin/users/{user}/ban` – requires `reason` (min 10 chars). Policy: admin only, cannot ban self, cannot ban last admin, cannot ban already banned user. Sets `banned_at`, `banned_by`, `ban_reason`, deletes all Sanctum tokens. Logs.
- **Unban:** `POST /admin/users/{user}/unban` – Policy: admin + target must be banned. Clears ban fields. Logs.

### 9.5 Soft Delete & Restoration
- **Delete:** `DELETE /admin/users/{user}` – Policy: admin only, cannot delete self, cannot delete last admin. Soft‑deletes user (triggers boot cascade for posts). Logs.
- **Restore:** `POST /admin/users/{id}/restore` – Finds trashed user by ID. Policy: admin only. Restores user and cascading posts. Logs.
- **Force Delete:** `DELETE /admin/users/{id}/force-delete` – Policy: admin + subject must be trashed. Permanently deletes user and all posts (via boot cascade). Logs.

---

## 10. Authorization Policies (`UserPolicy`)

All methods receive authenticated user (`$user`) and subject user (`$subject`). Helper `isLastAdmin()` returns `true` if count of Admins ≤ 1.

| Ability | Logic |
|---------|-------|
| `viewAny` | `Admin` |
| `view` | `Admin` |
| `updateRole` | Admin, cannot modify own role, cannot demote last admin |
| `ban` | Admin, cannot ban self, cannot ban last admin, not already banned |
| `unban` | Admin, target must be banned |
| `delete` | Admin, cannot delete self, cannot delete last admin |
| `restore` | Admin |
| `forceDelete` | Admin, target must be trashed |
| `update` (profile) | Own profile **or** Admin/Editor |

**Note:** `AuthorController` imposes an extra own‑profile check, making `update` policy irrelevant there.

---

## 11. Middleware

### 11.1 `CheckRole` (`role`)
Aliased as `role`. Accepts comma‑separated role names, e.g., `role:Admin,Editor`.

- If no roles given → pass.
- If not authenticated → redirect to login with error.
- Loops through required roles; if any match → pass.
- Otherwise logs warning (user ID, required roles, actual roles, URL, IP) and aborts 403.

Priority set to 10 (higher than auth middleware).

---

## 12. Form Requests & Validation

### `LoginRequest`
- `email` required, email, max:255, exists in users table
- `password` required, string, `Password::defaults()`  
- `remember` nullable, boolean (cast by `prepareForValidation`)
- Custom message: `email.exists` → “These credentials do not match our records.”

### `RegisterRequest`
- `name`: required, 3‑255 chars, regex `/^[a-zA-Z\s]+$/`
- `email`: required, email, unique, confirmed (`email_confirmation` field)
- `password`: required, confirmed, strong rules (min 8, letters, mixed case, numbers, symbols, uncompromised)
- `terms`: required, accepted
- `getUserData()` returns `name`, `email`, hashed password, `email_verified_at = now()`

### `AdminUserBanRequest`
- `reason`: required, string, min 10, max 500

### `AdminUserRoleRequest`
- `role`: required, in: `User, Author, Editor, Admin`

### `DashboardProfileRequest`
- `name`: required, unique ignoring current user
- `email`: required, email, unique ignoring current user
- `bio`: nullable, max 1000
- `avatar`: nullable, image (jpeg,png,jpg,gif,webp), max 2048 KB
- `delete_avatar`: nullable, boolean

### `ProfileRequest`
- Extends `DashboardProfileRequest` with:
  - `social_links` array, each key (`twitter`,`github`,`linkedin`,`website`) nullable URL max 255
- `prepareForValidation()` removes empty social link entries
- `getProfileData()` returns validated data **excluding** `avatar` and `delete_avatar` (handled separately)

---

## 13. ImageService

Located at `app/Services/ImageService.php`. Uses Intervention Image v3 (GD driver).  
**Configuration:** relies on `config/image.php` (assumed to exist) for:
- `image.disk` (default `'public'`)
- `image.sizes` an associative array, e.g., `'thumb' => ['width'=>150,'height'=>150,'fit'=>true,'quality'=>85]`
- `image.quality` (default 85)

**Methods:**
- `storeImage(UploadedFile, folder, sizes[])` – Generates unique filename (SHA‑256 + random prefix), stores original in `{folder}/original/`, creates thumbnails for each size, returns original path.
- `deleteImage(?path)` – Deletes original and all thumbnail versions.
- `updateImage(newFile, oldPath, folder, sizes)` – Delete old + store new.

Thumbnail generation uses `cover` (cropping) if `fit` is true, else `scale`. All thumbs encoded as JPEG.

---

## 14. Routes (Complete User‑Related)

Routes are defined in `routes/web.php`. Below are only the user‑centric ones.

### 14.1 Guest Routes (middleware `guest`)
```
GET   /login                  → LoginController@showLoginForm         name: login
POST  /login                  → LoginController@login
GET   /register               → RegisterController@showRegistrationForm name: register
POST  /register               → RegisterController@register
GET   /password/reset         → ForgotPasswordController@showLinkRequestForm   name: password.request
POST  /password/email         → ForgotPasswordController@sendResetLinkEmail    name: password.email
GET   /password/reset/{token} → ResetPasswordController@showResetForm         name: password.reset
POST  /password/reset         → ResetPasswordController@reset                 name: password.update
```

### 14.2 Authenticated Routes (middleware `auth`)
```
POST  /logout                 → LoginController@logout               name: logout
GET   /dashboard              → DashboardController@index            name: dashboard
PUT   /dashboard/profile      → DashboardController@updateProfile    name: dashboard.update
```

⚠️ **Critical bug:** The `/dashboard` route is defined **twice**: also inside the `auth` group further down, causing a route name conflict. This will cause a Laravel exception. The duplicate must be removed.

### 14.3 Author Directory & Profiles
```
GET   /authors                → AuthorController@index               name: authors.index          (public)
GET   /author/{user}          → AuthorController@show                name: author.show            (public)
```
**Group (auth + role:Admin|Editor|Author):**
```
GET   /profile/edit           → AuthorController@edit                name: author.edit
PUT   /profile                → AuthorController@update              name: author.update
```

### 14.4 Admin Panel (prefix `admin`, middleware `auth + role:Admin`)
```
GET   admin/                      → Admin\DashboardController@index  name: admin.dashboard
GET   admin/users                 → UserAdminController@index        name: admin.users.index
GET   admin/users/{user}          → UserAdminController@show         name: admin.users.show
PUT   admin/users/{user}/role     → UserAdminController@updateRole   name: admin.users.updateRole
POST  admin/users/{user}/ban      → UserAdminController@ban          name: admin.users.ban
POST  admin/users/{user}/unban    → UserAdminController@unban        name: admin.users.unban
DELETE admin/users/{user}         → UserAdminController@destroy      name: admin.users.destroy
POST  admin/users/{id}/restore    → UserAdminController@restore      name: admin.users.restore
DELETE admin/users/{id}/force-delete → UserAdminController@forceDelete name: admin.users.forceDelete
```

---

## 15. Artisan Command

**`storage:mkdirs`** – `CreateStorageDirectories`  
Creates subdirectories under `storage/app/public/` for each folder (`posts`, `users`, `categories`, `tags`) and each size defined in `config/image.sizes`. Skips existing directories. Required before avatar uploads can succeed.

---

## 16. Edge Cases & Business Rules

1. **Ban on login:** Banned users can submit credentials; after successful authentication they are immediately logged out with an error message. New logins are blocked, but **existing web sessions are not destroyed** – only Sanctum tokens are removed on ban. This means a banned user may continue using the application until their session expires or they log out.
2. **Last admin protection:** The `UserPolicy` prevents removal of the last admin’s role, banning, or deletion. This guarantees at least one admin exists.
3. **Soft‑delete cascade:** Soft‑deleting a user soft‑deletes all their posts. Restoring the user restores those posts. Force‑deleting permanently deletes all posts. This maintains referential integrity.
4. **Password reset dev mode:** No email is sent; token and email are passed via redirect. To enable real emails, uncomment the `Password::sendResetLink` block and configure mail.
5. **Email verification skipped:** `email_verified_at` is set instantly; the `Registered` event fires but no listener acts, so no verification mail is sent.
6. **Profile editing restrictions:** The author profile edit routes have a strict own‑profile check (`$user->id === auth()->id()`). Even Admin/Editor cannot edit someone else’s profile via that route; they would need an admin‑side profile editor (not implemented).
7. **Avatar fallback:** If no avatar is stored, `getAvatarUrlAttribute` returns a Gravatar based on the user’s email.
8. **One role per user:** `syncRoles` ensures only one role is assigned at a time.
9. **`last_login_at` column missing:** This will cause a database error on login. Must be added via migration.
10. **Duplicate dashboard route:** Needs to be resolved to avoid Laravel crash.

---

## 17. Known Issues & Limitations

| Issue | Severity | Description |
|-------|----------|-------------|
| `last_login_at` column missing | **Critical** | Login throws SQL error; migration required |
| Duplicate `/dashboard` route | **Critical** | Route naming conflict prevents app from running |
| `updated_by` column missing | Medium | `updater()` relationship defined but unusable |
| Ban does not invalidate web sessions | Medium | Banned user may remain logged in until session expires |
| No admin interface to edit another user’s profile | Low | Only role and ban management exist; full profile editing for others not available |
| No email delivery in password reset | Low | Dev convenience; production must be reconfigured |

---

## 18. Recommendations for Future Releases

- Add migration for `last_login_at` column.
- Remove duplicate `/dashboard` route (keep one).
- Either remove unused `updater()` relationship and `updated_by` idea, or add the column if needed.
- After ban, actively destroy the user’s session (e.g., using session driver’s `logoutOtherDevices` or custom session invalidation).
- Implement email verification and password reset emails for production.
- Create an admin profile editing feature if needed.

---
