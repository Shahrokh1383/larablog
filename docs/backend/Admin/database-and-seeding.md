# Database & Seeding Documentation

> **Scope:** Complete database schema, migration inventory, and initial data seeding for the LaraBlog admin panel.  

---

## 1. Overview

The LaraBlog database is built on MySQL (or compatible) using Laravel’s schema builder. It supports:
- User authentication and role‑based access (Spatie/laravel-permission)
- Blog posts with drafts, publishing, soft‑deletes, and audit trails
- Polymorphic nested commenting with moderation flags
- Many‑to‑many taxonomies (categories, tags)
- Admin‑specific fields for banning users, tracking last editor, and control flags
- API token support via Laravel Sanctum

All migrations are located in `database/migrations/` and seeders in `database/seeders/`. The schema is designed with referential integrity, performance indexes, and cascading deletes where appropriate.

---

## 2. Migration Inventory & Execution Order

The following migrations must be run in the order listed (Laravel’s timestamp order). The sequence guarantees foreign key dependencies are met.

| # | Migration File | What It Creates / Modifies |
|---|---------------|----------------------------|
| 1 | `0001_01_01_000000_create_users_table.php` | `users`, `password_reset_tokens`, `sessions` |
| 2 | `2025_12_22_172347_create_permission_tables.php` | Spatie permission tables (`permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`) |
| 3 | `2025_12_22_193055_create_posts_table.php` | `posts` |
| 4 | `2025_12_22_193059_create_categories_table.php` | `categories` |
| 5 | `2025_12_22_193102_create_tags_table.php` | `tags` |
| 6 | `2025_12_22_193608_create_category_post_table.php` | Pivot: `category_post` |
| 7 | `2025_12_22_193616_create_post_tag_table.php` | Pivot: `post_tag` |
| 8 | `2025_12_23_185337_add_updated_by_to_posts_table.php` | Add `updated_by` FK to `posts` |
| 9 | `2025_12_26_190609_create_comments_table.php` | `comments` |
| 10 | `2025_12_26_220851_add_allow_comments_to_posts_table.php` | Add `allow_comments` to `posts` |
| 11 | `2025_12_29_214913_add_ban_fields_to_users_table.php` | Add ban fields + soft‑deletes to `users` |
| 12 | `2025_12_29_234333_create_personal_access_tokens_table.php` | `personal_access_tokens` (Sanctum) |

**Important:** Migration `2025_12_26_190609_create_comments_table.php` contains a **duplicate index** on `(commentable_id, commentable_type)` (the morphs call already creates it). The extra manual index must be removed to avoid migration errors and redundant storage. See §8 Known Issues.

---

## 3. Core Tables

### 3.1 `users`

Created by `0001_01_01_000000` and `2025_12_29_214913`.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned (PK) | no | auto‑increment | |
| `name` | varchar(255) | no | – | |
| `email` | varchar(255) | no | – | Unique |
| `email_verified_at` | timestamp | yes | null | Set instantly on registration |
| `password` | varchar(255) | no | – | Bcrypt hashed |
| `avatar` | varchar(255) | yes | null | Relative path (original) |
| `bio` | text | yes | null | |
| `social_links` | json | yes | null | Keys: twitter, github, linkedin, website |
| `remember_token` | varchar(100) | yes | null | |
| `banned_at` | timestamp | yes | null | If set, user is banned |
| `banned_by` | foreignId (users.id) | yes | null | Admin who banned, nullOnDelete |
| `ban_reason` | text | yes | null | |
| `deleted_at` | timestamp | yes | null | Soft delete (added with ban migration) |
| `created_at` | timestamp | no | – | |
| `updated_at` | timestamp | no | – | |

**Indexes:**  
- Primary: `id`  
- Unique: `email`  
- None on `banned_at` alone; filtering is done via scope, not indexed.

**Foreign Keys:**  
- `banned_by` → `users.id` ON DELETE SET NULL

**Admin relevance:**  
- Ban fields are set by admins via `UserAdminController@ban`.  
- Soft‑deletion cascades to user’s posts (see model boot logic, not database cascade).  
- **Critical missing column:** `last_login_at` – used in code but **no migration exists**. Must be added.  
- **Missing column `updated_by`** – a relationship exists in the model but column never created; unusable.

---

### 3.2 `password_reset_tokens`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `email` | varchar(255) PK | no | |
| `token` | varchar(255) | no | |
| `created_at` | timestamp | yes | |

---

### 3.3 `sessions`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | varchar(255) PK | no | |
| `user_id` | bigint unsigned (indexed) | yes | |
| `ip_address` | varchar(45) | yes | |
| `user_agent` | text | yes | |
| `payload` | longtext | no | |
| `last_activity` | int (indexed) | no | |

---

### 3.4 `personal_access_tokens` (Sanctum)

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint unsigned | no | |
| `tokenable_type` | string | no | |
| `tokenable_id` | bigint unsigned | no | |
| `name` | string (text in migration) | no | |
| `token` | varchar(64) unique | no | Hashed token |
| `abilities` | text | yes | JSON |
| `last_used_at` | timestamp | yes | |
| `expires_at` | timestamp (indexed) | yes | |
| `created_at` | timestamp | no | |
| `updated_at` | timestamp | no | |

**Admin usage:** When an admin bans a user, all tokens of that user are deleted (`UserAdminController@ban`), revoking API access immediately.

---

### 3.5 Spatie Permission Tables

All tables use `guard_name = 'web'`. Teams feature is disabled.

- **`permissions`**: `id`, `name`, `guard_name`, `timestamps`. Unique (`name`, `guard_name`).
- **`roles`**: `id`, `name`, `guard_name`, `timestamps`. Unique (`name`, `guard_name`).
- **`model_has_permissions`**: composite PK (`permission_id`, `model_type`, `model_id`). FK → permissions on delete cascade.
- **`model_has_roles`**: composite PK (`role_id`, `model_type`, `model_id`). FK → roles on delete cascade.
- **`role_has_permissions`**: composite PK (`permission_id`, `role_id`). FKs to both tables with cascade.

---

### 3.6 `posts`

Created by `2025_12_22_193055`, modified by `2025_12_23_185337` and `2025_12_26_220851`.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | no | auto‑increment | |
| `user_id` | foreignId | no | – | Author, FK → users.id ON DELETE CASCADE |
| `updated_by` | foreignId | yes | null | Last editor, FK → users.id ON DELETE SET NULL |
| `title` | varchar(255) | no | – | |
| `slug` | varchar(255) | no | – | Unique |
| `excerpt` | text | yes | null | |
| `body` | longtext | no | – | |
| `status` | enum('draft','published') | no | 'draft' | |
| `published_at` | timestamp | yes | null | Must be ≥ now (validation) |
| `featured_image` | varchar(255) | yes | null | Relative path (original) |
| `views` | unsignedBigInteger | no | 0 | Atomic increment only |
| `allow_comments` | boolean | no | true | Checkbox in post form |
| `created_at` | timestamp | no | – | |
| `updated_at` | timestamp | no | – | |
| `deleted_at` | timestamp | yes | null | Soft delete |

**Indexes:**  
- Composite (`status`, `published_at`) – speeds up public listings.  
- `user_id` – for author filters.  
- `updated_by` – for admin filtering by editor.  
- `FULLTEXT` on (`title`, `body`) – **currently not utilized** (search uses LIKE).  
- `slug` unique.

**Foreign Keys:**  
- `user_id` → `users.id` ON DELETE CASCADE (**hard delete of a user permanently deletes all posts**).  
- `updated_by` → `users.id` ON DELETE SET NULL.

**Admin relevance:**  
- `updated_by` is set automatically on admin/post updates via `PostRequest`.  
- `allow_comments` toggle editable in admin.  
- Soft‑delete is used; force‑delete permanently removes image files and pivot entries.

---

### 3.7 `categories`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | no | auto‑increment | |
| `name` | varchar(100) | no | – | Unique |
| `slug` | varchar(100) | no | – | Unique |
| `description` | text | yes | null | |
| `created_at` | timestamp | yes | null | |
| `updated_at` | timestamp | yes | null | |

**Indexes:** Unique on `name`, `slug`.

**Admin relevance:** Admin CRUD via `CategoryAdminController`. Deletion blocked if any **non‑soft‑deleted** posts are linked.

---

### 3.8 `tags`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | no | auto‑increment | |
| `name` | varchar(50) | no | – | Unique |
| `slug` | varchar(50) | no | – | Unique |
| `created_at` | timestamp | yes | null | |
| `updated_at` | timestamp | yes | null | |

Same admin management pattern as categories (via `TagAdminController`).

---

### 3.9 Pivot Tables

#### `category_post`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint unsigned | no | |
| `post_id` | foreignId | no | FK → posts.id CASCADE |
| `category_id` | foreignId | no | FK → categories.id CASCADE |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

Unique constraint: (`post_id`, `category_id`).

#### `post_tag`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| `id` | bigint unsigned | no | |
| `post_id` | foreignId | no | FK → posts.id CASCADE |
| `tag_id` | foreignId | no | FK → tags.id CASCADE |
| `created_at` | timestamp | yes | |
| `updated_at` | timestamp | yes | |

Unique: (`post_id`, `tag_id`).

**Cascade behavior:** Deleting a post or a taxonomy hard‑deletes the pivot records. Soft‑deleting a post leaves pivot rows intact, making restoration seamless.

**Admin note:** Admin delete of a category/tag is rejected if there are live post associations (extra check in controller). If allowed, cascade removes pivots automatically.

---

### 3.10 `comments`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | no | auto‑increment | |
| `user_id` | unsignedBigInteger | yes | null | FK → users.id ON DELETE SET NULL |
| `commentable_id` | bigint unsigned | no | – | Polymorphic ID |
| `commentable_type` | string | no | – | FQCN of commented model |
| `parent_id` | unsignedBigInteger | yes | null | FK → comments.id ON DELETE CASCADE |
| `body` | text | no | – | |
| `approved` | boolean | no | false | 0 = pending, 1 = published |
| `guest_name` | varchar(255) | yes | null | |
| `guest_email` | varchar(255) | yes | null | |
| `ip_address` | varchar(45) | yes | null | |
| `user_agent` | text | yes | null | |
| `created_at` | timestamp | no | – | |
| `updated_at` | timestamp | no | – | |

**Indexes:**  
- `comments_parent_id_index` (on `parent_id`)  
- `comments_ip_address_index`  
- `comments_approved_index` (on `approved`)  
- Composite: (`approved`, `created_at`)  
- Composite: (`parent_id`, `approved`)  
- Composite (from `morphs`): (`commentable_type`, `commentable_id`) – **duplicate manual index exists and must be removed** (see §8).

**Foreign Keys:**  
- `parent_id` → `comments.id` ON DELETE CASCADE – deleting a comment automatically deletes all descendants at the database level.  
- `user_id` → `users.id` ON DELETE SET NULL – preserves comment if author deleted.

**Admin relevance:**  
- `approved` flag drives the moderation queue in admin.  
- Admin can approve, reject, or delete comments. Deletion cascades to nested replies.

---

## 4. Key Relationships & Constraints

- **User → Posts:** `HasMany` (`user_id`). Soft‑delete behavior is implemented in model boot (not FK). If a user is force‑deleted, all posts are cascade‑removed by FK.
- **Post → Categories / Tags:** Many‑to‑many via pivot tables with cascading deletes.
- **Post → Comments:** MorphMany (`commentable`). The `parent_id` on comments creates a tree; cascade ensures clean deletion of threaded replies.
- **User → Ban:** `banned_by` references the admin who banned; set null if that admin is deleted.
- **Post → Updater:** `updated_by` references the last editor (set null on user deletion).
- **Comments → User:** Nullable; set null if comment author is deleted.

---

## 5. Indexes & Performance Notes

- **Full‑text index on posts (`title`, `body`):** currently not leveraged in the search scope. For large datasets, either switch to `MATCH ... AGAINST` or remove the index to save space.
- **Composite index on `(status, published_at)`:** optimizes public post listings.
- **Comment indexes:** Support the pending queue (`approved` + `created_at`) and nested retrieval (`parent_id` + `approved`).
- **Missing index on `users.banned_at`** – may affect performance for banned‑user queries if table grows large. Consider adding.
- All pivot tables have unique composite keys to prevent duplicates.

---

## 6. Database Seeding

Seeders are called in order via `DatabaseSeeder::run()`.

### 6.1 RoleSeeder

- Clears all permission data (tables truncated, foreign key checks disabled).
- Creates 13 permissions:
  - `view posts`, `create posts`, `edit posts`, `delete posts`, `publish posts`
  - `create comments`, `manage comments`, `approve comments`, `delete comments`
  - `manage categories`, `manage tags`, `manage users`, `view admin panel`
- Creates four roles and assigns permissions:

| Role    | Permissions |
|---------|-------------|
| Admin   | All 13 (full access) |
| Editor  | `view posts`, `create posts`, `edit posts`, `delete posts`, `publish posts`, `manage comments`, `approve comments`, `manage categories`, `manage tags`, `view admin panel` |
| Author  | `view posts`, `create posts`, `edit posts`, `publish posts`, `manage comments` |
| User    | `view posts` only |

- After seeding, the permission cache is refreshed.

### 6.2 UserSeeder

Creates four fixed accounts and five random Author users.

| Email | Password | Role |
|-------|----------|------|
| admin@larablog.test | password123 | Admin |
| editor@larablog.test | password123 | Editor |
| author@larablog.test | password123 | Author |
| user@larablog.test | password123 | User |

Additional 5 authors are created via `User::factory(5)->create()` and assigned the `Author` role.  
All fixed users have `email_verified_at = now()`, no avatar, a short bio, and some social links. Passwords are hashed.

- Remember tokens are set to `Str::random(60)`.
- None of the seeded users are banned or soft‑deleted.

### 6.3 DatabaseSeeder

```php
$this->call([
    RoleSeeder::class,
    UserSeeder::class,
]);
```

No other seeders are called. Admin users can create sample content through the application after login.

---

## 7. Admin‑Specific Data Features

### 7.1 Ban System
- Columns: `banned_at`, `banned_by`, `ban_reason` on `users`.
- Admin sets via `UserAdminController@ban`; ban reason validated by `AdminUserBanRequest` (min 10 chars).
- On ban, all Sanctum tokens of the user are deleted. Web sessions are **not** invalidated (known issue).
- Unban clears all ban fields.

### 7.2 Soft‑Delete Behaviour
- **Users** use `SoftDeletes`. Deleting a user soft‑deletes their posts (via model event). Restoration restores posts. Force‑delete permanently removes everything.
- **Posts** use `SoftDeletes`. Deleting keeps image files and pivot rows. Force‑delete clears images and pivots.
- **Comments** are **hard‑deleted**. Their cascades ensure nested comments are removed. No trash for comments.

### 7.3 Audit Trail
- `posts.updated_by` records the last admin/editor who edited a post. It is automatically filled by `PostRequest` during update.
- Ban records (`banned_by`) track which admin banned a user.
- All admin actions are logged via Laravel’s `Log` facade (outside DB). See admin module docs.

### 7.4 Comment Moderation
- `comments.approved` default `false` (pending). Admin can approve/reject from dedicated interface. Approving a pending comment triggers `CommentPosted` event (post‑author notification).

---

## 8. Known Issues & Required Fixes

| Issue | Severity | Description & Fix |
|-------|----------|-------------------|
| **Duplicate index in `comments` migration** | High | Migration `2025_12_26_190609` has `$table->index(['commentable_id','commentable_type']);` after `morphs()`, causing migration failure. **Remove that line.** |
| **Missing `last_login_at` column** | Critical | The `User` model’s `getLastActivityAttribute()` and login logic rely on it. Add migration: `$table->timestamp('last_login_at')->nullable();` to `users`. |
| **Missing `updated_by` on `users`** | Medium | Model defines `updater()` relation but column never created. Either remove relation or add column if needed. |
| **Full‑text index on posts unused** | Low | Search scope uses `LIKE`, wasting storage. Either implement `MATCH ... AGAINST` or drop index. |
| **No index on `banned_at`** | Performance | Queries filtered by ban status may slow down on large user bases. Add index if needed. |
| **Duplicate dashboard route** | Critical (app) | Not database but must be fixed to run app. Mentioned for completeness; see admin routes doc. |

---

## 9. Migration Customization & Extending

When adding new features, follow these guidelines:

1. **New columns:** Create a dedicated migration with `Schema::table(...)`. Always provide a `down()` method.
2. **Adding a new table:** Place migration timestamp after the last existing one to maintain order.
3. **Foreign keys:** Use `constrained()` and explicit `onDelete()` behavior matching business logic (CASCADE for dependents, SET NULL for optional references).
4. **Indexes:** Add indexes for columns used in `WHERE`, `ORDER BY`, `GROUP BY`, and joins.
5. **Seeding:** Append to existing seeders or create new ones. Call them from `DatabaseSeeder` in the correct order (permissions first, then roles, then users, then content).
6. **Admin‑specific data fields:** Always document which fields are writable only by admins (e.g., `banned_at`). Ensure policies protect them.

---

## 10. Cross‑References to Other Documentation

- **User management (general & admin):** [user-management.md](../user-management.md) and [Admin/modules/user-management.md](modules/user-management.md)
- **Post system (public & admin):** [post-system.md](../post-system.md) and [Admin/modules/content-management.md](modules/content-management.md)
- **Categories & Tags (admin):** [categories.md](../categories.md), [tags.md](../tags.md), [Admin/modules/taxonomy-management.md](modules/taxonomy-management.md)
- **Comments & moderation:** [comments-system.md](../comments-system.md), [Admin/modules/comment-moderation.md](modules/comment-moderation.md)
- **Authorization middleware & policies:** [authorization-and-middleware.md](authorization-and-middleware.md)
- **Services & Logging:** [services-and-logging.md](services-and-logging.md)

---