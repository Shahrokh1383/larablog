---

```markdown
# Post System Documentation

> **Core packages:** spatie/laravel-permission, spatie/laravel-sluggable, intervention/image (v3, GD driver)

---

## 1. Overview

The Post module implements the central content entity of the Laravel blog platform. Key capabilities:

- Full draft/publish workflow (publication can only happen now or in the future; backdating is prevented by validation).
- Soft deletion with restore (trash) and permanent removal.
- Role‑based access control using **Spatie permissions** and Laravel **Gates & Policies**.
- Route protection via `auth` and a custom `role` middleware (`App\Http\Middleware\CheckRole`).
- Automatic, stable SEO slug generation (slugs never change after creation).
- Multi‑size featured image management: one original + three thumbnails, handled by `App\Services\ImageService`.
- Public visibility logic: guests see only published posts; authors see only their own *published* posts; admins/editors see all non‑trashed posts (including drafts).
- Polymorphic commenting system (dependent on a `Comment` model – see §3.1).
- Audit logging for all significant state changes.
- **Important data‑loss notice:** The `user_id` foreign key has `ON DELETE CASCADE`. If an author’s user account is permanently deleted, **all their posts (including soft‑deleted ones) are permanently erased**. If posts must survive user deletion, change the migration to `SET NULL` or soft‑delete users instead. This behaviour is documented as an explicit design decision.

---

## 2. System Requirements & Dependencies

### 2.1 Server Requirements
- PHP 8.1+ (with the `gd` or `imagick` extension) – required by Intervention/Image v3.
- MySQL 8.0+ (or a compatible engine) – the `posts` table uses a `FULLTEXT` index and an `enum` column.
- Composer.

### 2.2 Installed Packages
- `spatie/laravel-permission` ^6.0
- `spatie/laravel-sluggable` ^3.5
- `intervention/image` ^3.0

### 2.3 Expected External Models
The Post module depends on these models; they must exist and expose the listed public API.

#### `App\Models\Comment`
- **Table:** `comments`
- **Required relationships:**
  - `commentable()` : `morphTo` (the post uses `commentable_type` and `commentable_id`)
  - `author()` : `belongsTo(User::class)` – the comment author.
  - `replies()` : `hasMany(Comment::class, 'parent_id')` – nested replies.
- **Required scope:**
  - `scopeApproved($query)` – must filter only approved comments.  
    Example: `return $query->where('status', 'approved');`
- **Important columns:** `id`, `parent_id` (nullable), `commentable_type`, `commentable_id`, `status` (string), `body`, `user_id`, timestamps.

#### `App\Models\Category`
- **Table:** `categories`
- **Required relationship:**
  - `posts()` : `belongsToMany(Post::class)` – with timestamps.

#### `App\Models\Tag`
- **Table:** `tags`
- **Required relationship:**
  - `posts()` : `belongsToMany(Post::class)` – with timestamps.

#### `App\Models\User`
- Uses `Spatie\Permission\Traits\HasRoles`.
- The `roles` and `permissions` tables are managed by Spatie.

---

## 3. Environment Variables

All relevant `.env` keys:

| Variable | Default | Used in | Description |
|----------|---------|---------|-------------|
| `IMAGE_DISK` | `public` | `config/image.php` | Storage disk for featured images. |
| `IMAGE_QUALITY` | `85` | `config/image.php` | Default JPEG quality for thumbnails (cast to integer). |
| `APP_URL` | `http://localhost` | `asset()` helper | Must be set correctly for absolute image URLs. |

No other custom environment variables are required by the Post module. Logging uses Laravel’s default channel configuration.

---

## 4. Quick Start / Installation

1. **Install dependencies**
   ```bash
   composer install
   ```
2. **Run migrations**
   ```bash
   php artisan migrate
   ```
3. **Create the storage symlink**
   ```bash
   php artisan storage:link
   ```
4. **Create image size directories**
   ```bash
   php artisan storage:mkdirs
   ```
5. **Seed roles and permissions**  
   Prepare a seeder (e.g., `RolesAndPermissionsSeeder`) that creates:
   - Permissions: `create posts`, `edit posts`, `delete posts`, `publish posts`.
   - Roles: `Admin` (all permissions), `Editor` and `Author` (all four post permissions).  
   Then run:
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```
6. **Verify**  
   Assign a user a role, then navigate to `/posts` or `/admin/posts`.

---

## 5. Architecture & Relationships

```
[User] -1----*- [Post] *----*- [Category]
                |    *----*- [Tag]
                |
                | 1-----* [Comment (morphMany)]
```

**Post** belongs to an **Author** (`user_id`) and an optional **Updater** (`updated_by`).  
It has many polymorphic **Comments**, and belongs‑to‑many **Categories** and **Tags**.

All relationships are defined in `App\Models\Post`.

---

## 6. Database Schema

### 6.1 Table `posts`

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | `bigint unsigned` | No | auto-increment | |
| `user_id` | `bigint unsigned` | No | – | FK → `users.id` (author). Cascade on delete — **permanently removes all user’s posts** if the user is hard-deleted. |
| `updated_by` | `bigint unsigned` | Yes | null | FK → `users.id` (last editor). Set null on user delete. |
| `title` | `varchar(255)` | No | – | |
| `slug` | `varchar(255)` | No | – | Unique URL slug (route key). |
| `excerpt` | `text` | Yes | null | |
| `body` | `longtext` | No | – | |
| `status` | `enum('draft','published')` | No | `'draft'` | |
| `published_at` | `timestamp` | Yes | null | Must be now or in the future (validated). |
| `featured_image` | `varchar(255)` | Yes | null | Relative path to original file, e.g. `posts/original/abc.jpg`. |
| `views` | `unsigned bigint` | No | `0` | View counter. |
| `allow_comments` | `boolean` | No | `true` | Whether comments can be left. |
| `created_at` | `timestamp` | No | – | |
| `updated_at` | `timestamp` | No | – | |
| `deleted_at` | `timestamp` | Yes | null | Soft delete column. |

**Indexes:**
- Composite `(status, published_at)` – accelerates public listings.
- `user_id` – for author filtering.
- `updated_by` – for admin filtering by editor.
- `FULLTEXT` index on `(title, body)` – **currently not utilised** (the `search()` scope still uses `LIKE`). See §15.

### 6.2 Migration Files

1. `database/migrations/2025_12_22_193055_create_posts_table.php`
2. `database/migrations/2025_12_23_185337_add_updated_by_to_posts_table.php`
3. `database/migrations/2025_12_26_220851_add_allow_comments_to_posts_table.php`

---

## 7. Model Reference — `App\Models\Post`

- **Table:** `posts`
- **Route key name:** `slug` (Laravel resolves `{post}` route parameter using the `slug` column).
- **Traits:** `HasFactory`, `SoftDeletes`, `HasSlug` (Spatie).
- **`$fillable`:**
  ```php
  'user_id', 'updated_by', 'title', 'slug', 'excerpt', 'body',
  'status', 'published_at', 'featured_image', 'allow_comments'
  ```
  ⚠️ `views` is **not** fillable (use the `incrementViews()` method). For seeding, set `$post->views = ...` directly.
- **Casts:** `status` → `string`, `published_at` → `datetime`, `views` → `integer`, `allow_comments` → `boolean`.

### 7.1 Slug Generation
- **Source:** `title`
- **Behaviour:** `doNotGenerateSlugsOnUpdate()` – slug remains unchanged when a post is edited (SEO stability).
- **Max length:** 200 characters, separator: `-`.

### 7.2 Boot Logic
In the `booted()` method: when a post’s `status` is changed to `'published'` and `published_at` is still `null`, it is automatically set to `now()`. This acts as a safety net; normally `PostRequest` already sets the timestamp.

### 7.3 Relationships

| Method | Return type | Related model | Key / Notes |
|--------|-------------|---------------|-------------|
| `author()` | `BelongsTo` | `User` | `user_id` |
| `updater()` | `BelongsTo` | `User` | `updated_by` |
| `categories()` | `BelongsToMany` | `Category` | pivot with timestamps |
| `tags()` | `BelongsToMany` | `Tag` | pivot with timestamps |
| `comments()` | `MorphMany` | `Comment` | `commentable_type`, `commentable_id` |

### 7.4 Query Scopes

| Scope | Signature | SQL Logic |
|-------|-----------|-----------|
| `published()` | – | `where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())` |
| `draft()` | – | `where('status', 'draft')` |
| `byAuthor(int $userId)` | – | `where('user_id', $userId)` |
| `inCategory(string $slug)` | – | `whereHas('categories', fn($q) => $q->where('slug', $slug))` |
| `withTag(string $slug)` | – | `whereHas('tags', fn($q) => $q->where('slug', $slug))` |
| `search(string $query)` | – | `LIKE '%...%'` on `title`, `excerpt`, `body` (fulltext index not used) |
| `popular()` | – | `orderByDesc('views')` |

### 7.5 Accessors & Helpers

- `$post->url` : `route('posts.show', $post->slug)`
- `$post->reading_time` : integer minutes (200 words/minute, minimum 1).
- `$post->image_url` : alias for `getImage('original')`.
- `$post->getImage(string $size = 'original')` : smart image URL resolver (see §8.4).
- `$post->isPublished()` : returns `true` if status is `'published'`, `published_at` is not null, and `published_at->isPast()`.
- `$post->publish()` : sets status to `'published'` and `published_at` to `now()`.
- `$post->unpublish()` : sets status to `'draft'` and `published_at` to `null`.
- `$post->incrementViews()` : atomic `increment('views')`.

---

## 8. Image Handling

### 8.1 Storage Configuration
- **Disk:** defined by `config('image.disk')` (default `public`).
- **Folder structure under the disk:**
  ```
  posts/
    original/    ← raw uploaded file
    social/      ← 1200×630
    card/        ← 400×300
    thumbnail/   ← 150×150
  ```
- **Quality** and **fit** are defined per size (see §8.3).

### 8.2 ImageService (`App\Services\ImageService`)

**Core methods:**

- `storeImage(UploadedFile $file, string $folder, array $sizes = []): string`  
  Generates a unique filename (8 random chars + SHA‑256 hash), stores the original, creates thumbnails for all sizes defined in `config('image.sizes')`, and returns the relative path to the original (e.g., `posts/original/filename.jpg`).

- `deleteImage(?string $path): void`  
  Deletes original and all thumbnail files listed in config.  
  ⚠️ If thumbnail sizes are later removed from config, old thumbnail files become orphans; consider a cleanup routine.

- `updateImage(UploadedFile $newFile, ?string $oldPath, string $folder, array $sizes = []): string`  
  Deletes the old image set, then stores the new one.

### 8.3 Image Configuration (`config/image.php`)

The actual configuration file provided contains only `disk`, `quality`, `sizes`, and `optimize`. The keys `allowed_mimes` and `max_upload_size` are **not present** in the file; however, `PostRequest` reads them with fallback defaults:

```php
'mimes:' . implode(',', $this->getAllowedMimeTypes()), // defaults to jpeg,png,gif,webp
'max:' . config('image.max_upload_size', 5120),        // defaults to 5120 KB
```

For production clarity, add these keys to `config/image.php`:

```php
return [
    'disk'             => env('IMAGE_DISK', 'public'),
    'quality'          => (int) env('IMAGE_QUALITY', 85),
    'sizes' => [
        'social'    => ['width' => 1200, 'height' => 630, 'quality' => 90, 'fit' => true],
        'card'      => ['width' => 400,  'height' => 300, 'quality' => 80, 'fit' => true],
        'thumbnail' => ['width' => 150,  'height' => 150, 'quality' => 75, 'fit' => true],
    ],
    'optimize'         => true,
    'allowed_mimes'    => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'max_upload_size'  => 5120, // KB
];
```

### 8.4 The `getImage(string $size)` Method

Resolves the public URL of an image:

1. If no `featured_image`, returns a Picsum placeholder: `https://picsum.photos/seed/{post_id}/800/600.jpg`.
2. For `$size = 'original'`, returns `asset('storage/' . $this->featured_image)`.
3. Constructs the sized path by replacing the substring `'original'` with the given size name (e.g., `posts/original/abc.jpg` → `posts/social/abc.jpg`).  
   *Note: This simple `str_replace` could produce incorrect paths if the string `'original'` appears elsewhere in the path or filename. A more robust solution using `dirname()` and `basename()` is recommended for production.*
4. If the sized file exists on disk, returns its `asset()` URL; otherwise falls back to the original image URL.

---

## 9. Authorization, Roles & Permissions

The system uses **Spatie Laravel Permission**.

### 9.1 Permissions
- `create posts`
- `edit posts`
- `delete posts`
- `publish posts`

### 9.2 Role Setup
- **Admin** – all permissions (`*`)
- **Editor** – all four post permissions
- **Author** – all four post permissions

> Having a permission does not grant unlimited power. The `PostPolicy` further restricts actions based on ownership and role.

### 9.3 `PostPolicy` – Full Access Matrix (exactly as implemented)

| Ability | Admin | Editor | Author | Notes |
|---------|-------|--------|--------|-------|
| `view` | always | always | only if published **or** is the owner | The `view` policy is only checked for drafts; published posts bypass the Gate entirely. |
| `create` | if has `create posts` permission | same | same | |
| `update` | always (by role) | always (by role) | only if owns the post AND has `edit posts` permission | Editor can edit any post. |
| `delete` | always | can delete if the author is **not** Admin AND has `delete posts` permission | only if owns the post AND has `delete posts` permission | Prevents editors from deleting admin‑authored posts. |
| `publish` | if has `publish posts` permission | if has `publish posts` permission | if has `publish posts` permission | **No ownership check is performed.** Any user with the `publish posts` permission can publish **any** post. |
| `restore` | always (by role) | always (by role) | denied | |
| `forceDelete` | always (by role) | denied | denied | |

**Important:** The `publish` ability in `PostPolicy` currently only verifies that the user possesses the `publish posts` permission. It does **not** check post ownership. This means that any role with that permission can publish any post, regardless of author.

---

## 10. HTTP API Reference – User‑Facing & Authenticated

All public/index routes begin with `/posts`. All data‑modifying routes require authentication (`auth` middleware) and a CSRF token for `POST`, `PUT`, `PATCH`, `DELETE` requests.

### 10.1 List all visible posts
**`GET /posts`**  
- **Middleware:** none  
- **View:** `posts.index`  
- **Query parameters (all optional):**
  - `search` – string
  - `category` – category slug
  - `tag` – tag slug
- **Visibility logic (depends on authenticated user):**
  - Guest → only `published()` scope.
  - Authenticated with role `Admin` or `Editor` → **all** non‑trashed posts (drafts included).
  - Authenticated with role `Author` → only the author’s own posts that are **published** (drafts are hidden).
- **Response:** HTML page. View receives `$posts` (paginator with `author`, `categories`, `tags` eager loaded and approved comment count). When accessed via category/tag routes, an extra `$filter` string is passed (see §12).

### 10.2 View a single post
**`GET /posts/{post}`**  
- **Middleware:** none  
- **View:** `posts.show`  
- **Route model binding:** resolves `{post}` by `slug`.
- **Access control:**  
  - If the post is published → always visible.
  - If not published → `Gate::allows('view', $post)` is checked. If denied, returns 403 “This post is not published yet.”
- **Actions:**  
  - View counter is incremented (atomic `incrementViews()`).
  - Top‑level, approved comments (with author and nested replies) are loaded and passed as `$comments`.
- **View data:** `$post` (with `author`, `categories`, `tags`), `$comments` (collection of `Comment`).

### 10.3 Create a new post (form)
**`GET /posts/create`**  
- **Middleware:** `auth`  
- **Authorization:** `Gate::authorize('create', Post::class)` (controller).
- **View:** `posts.create`  
- **View data:** `$categories` (all `Category` id/name), `$tags` (all `Tag` id/name).

### 10.4 Store a new post
**`POST /posts`**  
- **Middleware:** `auth`  
- **Authorization:** inside `PostRequest` → `Gate::allows('create posts')`.  
- **Body (form‑data, `Content-Type: multipart/form-data`):**
  - `title` (required, string, max 255)
  - `slug` (optional, unique, regex: lowercase letters, numbers, hyphens)
  - `excerpt` (optional, max 500)
  - `body` (required, string, min 10)
  - `status` (required, `draft` or `published`)
  - `published_at` (optional date, must be `>= now` — past dates are rejected)
  - `featured_image` (optional file, image, max size from config, allowed mimes from config)
  - `categories[]` (optional array of category IDs, max 5, each must exist)
  - `tags[]` (optional array of tag IDs, max 10, each must exist)
  - `allow_comments` (optional checkbox – when present, value becomes `true`; otherwise `false`)
- **Auto‑slug:** If `slug` is omitted, it is generated from `title` using `Str::slug()`.
- **Auto‑published_at:** If `status` is `published` and no `published_at` given, it is set to `now()`.
- **Success response:**  
  Redirect to `route('posts.show', $post->slug)` with flash `success` message (“Post published successfully!” or “Draft saved successfully!”).  
  A database transaction ensures atomicity; on failure all changes are rolled back.

### 10.5 Edit a post (form)
**`GET /posts/{post}/edit`**  
- **Middleware:** `auth`  
- **Authorization:** `Gate::authorize('update', $post)` (controller).
- **View:** `posts.edit` (also used by admin – see §11.2 note).
- **View data:** `$post`, `$categories`, `$tags`.

### 10.6 Update a post
**`PUT/PATCH /posts/{post}`**  
- **Middleware:** `auth`  
- **Authorization:** `PostRequest` uses `Gate::allows('update', $post)`.
- **Body:** same as store, except:
  - The original `user_id` is never overwritten. The `updated_by` field is automatically set to the authenticated user.
  - The optional `delete_image` parameter can be sent as a checkbox. If truthy and a `featured_image` exists, the old image and thumbnails are deleted and `featured_image` set to `null`.
- **Image replacement:**  
  If a new file is uploaded, the old image is deleted first, then the new one stored.
- **Success response:** Redirect to `route('posts.show', $post->slug)` with flash `success`.

### 10.7 Delete a post (soft delete)
**`DELETE /posts/{post}`**  
- **Middleware:** `auth`  
- **Authorization:** `Gate::authorize('delete', $post)` (controller).
- **Behaviour:** Soft delete only. Relationships (categories, tags) and image files are **preserved** to enable restoration. Only `$post->delete()` is called inside a transaction.
- **Success response:** Redirect to `route('posts.index')` with flash `success` “Post moved to trash.”

---

## 11. HTTP API Reference – Admin Panel

All admin routes are prefixed with `/admin` and named `admin.`. They require `auth` middleware and the `role:Admin,Editor` middleware.

### 11.1 Admin list (index)
**`GET /admin/posts`**  
- **Middleware:** `auth`, `role:Admin,Editor`  
- **View:** `admin.posts.index`  
- **Query parameters:**
  - `status` : `draft`, `published`, or `trashed`
  - `author` : user ID of an author
  - `search` : string (searches `title` and `body` only)
- **Sorting (actual behaviour):**  
  The query always applies `->latest('deleted_at')`. This means:
  - For `status=trashed` the trashed posts are ordered by deletion time (newest first), which is correct.
  - For non‑trashed posts (`status` is `draft` or `published`), `deleted_at` is `NULL` for all rows, resulting in **unreliable order** (typically database‑dependent). This is a known issue; a fix would be to use `latest('published_at')` for non‑trashed posts.
- **View data:** `$posts` (paginator with `author` and `categories`), `$authors` (all users with Admin/Editor/Author roles, `pluck('name', 'id')`).

### 11.2 Admin edit form
**`GET /admin/posts/{post}/edit`**  
- **Authorization:** `Gate::authorize('update', $post)`.
- **Renders the same `posts.edit` view** as the user side. Be mindful that the form action will post to the public `/posts/{post}` route by default unless the view is adapted or a separate admin edit view is used.

### 11.3 Admin update
**`PUT/PATCH /admin/posts/{post}`**  
- **Authorization:** `Gate::authorize('update', $post)`.
- **Current implementation:** The admin controller **forwards the request** to `PostController::update`:
  ```php
  return app(\App\Http\Controllers\PostController::class)->update($request, $post, $imageService);
  ```
  Consequently, after a successful update the user is **redirected to `posts.show` (the public post page)**, not to the admin index. This is a known behaviour. If the desired flow is to stay in the admin panel, the admin controller must implement its own update logic and redirect to `admin.posts.index`.

### 11.4 Admin delete (soft delete)
**`DELETE /admin/posts/{post}`**  
- **Authorization:** `Gate::authorize('delete', $post)`.
- **Current implementation:** Delegates to `PostController::destroy`, which performs a soft delete and then **redirects to `posts.index` (the public list)**. Same caveat as update applies.

### 11.5 Restore from trash
**`POST /admin/posts/{id}/restore`**  
- **Authorization:** `Gate::allows('restore', $post)` (only Admin and Editor).
- Finds the post in `onlyTrashed()`, calls `$post->restore()`, logs the action.
- **Success:** redirect back with a flash message.

### 11.6 Force delete (permanent)
**`DELETE /admin/posts/{id}/force-delete`**  
- **Authorization:** `Gate::allows('forceDelete', $post)` (Admin only).
- Inside a transaction: detaches categories/tags, deletes all image files via `ImageService`, then `$post->forceDelete()`.
- Logged with `warning` level.
- **Success:** redirect back with a flash message.

### 11.7 Bulk actions
**`POST /admin/posts/bulk-action`**  
- **Body:** `action` (one of `delete`, `publish`, `draft`) and `ids[]` (array of post IDs).
- **Critical note:** The current code performs **no authorization checks** on the selected posts. It directly executes `whereIn` queries. This means any admin/editor can bulk‑delete, publish, or draft **any** posts, bypassing the Policy restrictions (e.g., an editor could delete an admin’s post). A proper implementation must load each post and call `Gate::authorize()` for the appropriate ability before acting.
- **Behaviour details:**
  - `delete`: calls `Post::whereIn('id', $ids)->delete()`. This soft‑deletes only non‑trashed posts; already trashed posts are ignored.
  - `publish`: sets `status = 'published'` and `published_at = now()`.
  - `draft`: sets `status = 'draft'`.
- **Success:** redirect back with a flash message.

### 11.8 Admin `show` route excluded
The resource registration deliberately omits the `show` route:
```php
Route::resource('posts', PostAdminController::class)
    ->except(['create', 'store', 'show']);
```

---

## 12. Additional Public Routes (Categories & Tags)

Two closure‑based routes provide filtered post listings:

**`GET /categories/{category:slug}`**  
- Displays published posts belonging to the category.
- View: `posts.index`, with an extra `$filter` string `"Category: {$category->name}"`.

**`GET /tags/{tag:slug}`**  
- Same as categories, but filtering by tag. `$filter = "Tag: #{$tag->name}"`.

These routes are public and require no authentication.

---

## 13. Workflow & State Diagram

```
   +---------+       publish()        +-----------+
   |  draft  | ---------------------> | published |
   +---------+       unpublish()      +-----------+
        |                                   |
        | soft delete (trash)               | soft delete
        V                                   V
   +---------+       restore()          +---------+
   | trashed | <----------------------  | trashed |
   +---------+                          +---------+
        |                                   |
        | forceDelete (Admin only)          | forceDelete
        V                                   V
   Permanently deleted               Permanently deleted
```

- **Draft** – invisible to guests and authors (in public lists); visible to Admins/Editors everywhere.
- **Published** (with `published_at <= now()`) – visible to all users.
- **Trashed** – only accessible in admin with `status=trashed`; can be restored or permanently deleted.
- **Force delete** – Admin only; removes DB record, pivot data, and all image files.

---

## 14. Logging

All major actions are logged using the `Log` facade on Laravel’s default channel.

| Action | Level | Logged Context |
|--------|-------|----------------|
| Post created | `info` | `post_id`, `author_id`, `title`, `status`, `has_image` |
| Post updated | `info` | `post_id`, `author_id`, `title`, `status`, `image_updated` |
| Soft deleted | `info` | `post_id`, `title`, `image_kept` |
| Restored from trash | `info` | `post_id`, `restored_by` |
| Force deleted | **warning** | `post_id`, `deleted_by` |
| Published (via dead‑code controller methods) | `info` | `post_id`, `published_by` |
| Status toggled (dead code) | `info` | `post_id`, `new_status`, `changed_by` |

The `PostController` contains `publish()` and `toggleStatus()` methods that are currently **not exposed by any route** (no routes are defined for them). They are left in the controller but unreachable; the logging calls inside them are therefore dormant.

---

## 15. Performance Notes

- **View counter:** Atomic increment; for high‑traffic sites consider offloading to a queued job or Redis.
- **Search:** The `search()` scope uses `LIKE '%...%'`, which cannot utilise the `FULLTEXT` index. For large tables, switch to `MATCH ... AGAINST` in Boolean mode or remove the index to save space.
- **Eager loading:** The public index eager‑loads `author`, `categories`, `tags`, and counts approved comments with `withCount` – no N+1 queries.
- **Image processing:** Thumbnails are generated synchronously. Uploading large images may slow down the request; a queue‑based approach is recommended for production.

---

## 16. Maintenance & Housekeeping

- **Orphan thumbnail files:** If thumbnail sizes are removed from config, their folders remain on disk. Implement a scheduled command to clean them up.
- **Trash auto‑purge (optional):** Add a scheduled command to force‑delete posts that have been trashed for more than 30 days.
- **`views` column:** It is not fillable; always increment via `incrementViews()`.

---

## 17. Comment Configuration (Important Dependency)

The Post module relies on `config/blog.php` for comment‑related settings. This file must be present and contain:

- `allow_guest_comments` – whether guests can comment (default `true`).
- `auto_approve_roles` – array of roles whose comments are auto‑approved (e.g., `['Admin', 'Editor']`).
- `max_comment_nesting_depth` – maximum reply depth (default `3`).
- `comments_per_page` – pagination (default `20`).
- `comment_notifications` – enable/disable notifications (default `true`).

The `Comment` model and its service should respect these values. The Post module only checks the `allow_comments` flag and the `approved()` scope on comments.

---

## 18. Validation Notes

- `published_at` is validated with `after_or_equal:now`. Backdating posts is not allowed through the form. If backdating is needed, adjust the rule.
- Slug validation regex: `/^[a-z0-9-]+$/`. To avoid validation errors, simply leave the slug field empty – it will be auto‑generated.
- The `allow_comments` checkbox is correctly handled: present in request → `true`, absent → `false`.
- Image dimension validation is commented out in the code; enable it if required.

---

## 19. Artisan Commands

- **`php artisan storage:mkdirs`**  
  Creates the directory structure inside `storage/app/public/` for all configured image sizes. Run after deployment or when adding a new size.

- **`php artisan storage:link`**  
  Standard Laravel command; creates the `public/storage` symlink. Required for images to be publicly accessible.

---

## 20. Testing Recommendations

- **Unit tests:** `PostPolicy` for every role/permission combination; `PostRequest` validation edge cases; `ImageService` path generation and deletion.
- **Feature tests:**  
  - Create/update with various roles.  
  - Image upload, deletion, replacement.  
  - Bulk actions (including authorization gaps).  
  - Admin redirect correctness (currently known to redirect to public routes).  
- **Integration:** Verify that views receive the correct data for guest, author, editor, and admin users.

---

## 21. Extending the Module

### Adding a new status (e.g., `archived`)
1. Change the `enum` column or switch to a `varchar` with validation.
2. Update `PostRequest` validation `Rule::in([...])`.
3. Add a scope like `scopeArchived()`.
4. Adjust `isPublished()`, `publish()`/`unpublish()` logic as needed.
5. Update `PostPolicy` if the new status affects access control.

### Adding a new image size (e.g., `banner`)
1. Add the size definition to `config/image.php` → `sizes`.
2. Run `php artisan storage:mkdirs`.
3. New uploads will automatically generate the size. For existing posts, create a regeneration command.

### Changing the image disk
1. Update `config/image.php` `disk` key.
2. Ensure the new disk is configured in `config/filesystems.php`.
3. Existing images will not be moved automatically; a migration script is needed.

---

## 22. Troubleshooting Quick Reference

| Symptom | Likely Cause | Solution |
|---------|--------------|----------|
| 404 on post page (`/posts/slug`) | `getRouteKeyName()` not returning `'slug'` | Verify the `Post` model. |
| Images show broken links | Missing storage symlink or directories | Run `php artisan storage:link` and `storage:mkdirs`. |
| Comment error “Call to undefined method approved()” | `Comment` model missing `scopeApproved` | Add the scope (see §2.3). |
| Admin update redirects to public post page | Controller delegates to `PostController` | Implement independent update logic and redirect to `admin.posts.index`. |
| Admin list displays posts in random order (non‑trashed) | Sorting uses `deleted_at` for all | Modify the query to use `latest('published_at')` when not viewing trashed. |
| Cannot set publish date in the past | Validation rule `after_or_equal:now` | Intended design. Adjust the rule if backdating is required. |
| Slug validation fails for uppercase | Regex only allows lowercase | Leave slug empty for auto‑generation or use lowercase manually. |
| Entire post history lost on user deletion | `ON DELETE CASCADE` on `user_id` | Consider soft‑deleting users or changing the FK to `SET NULL`. |
| Bulk action “delete” does nothing for trashed posts | `whereIn` ignores soft‑deleted rows | Use `Post::withTrashed()->whereIn(...)` if you need to target trashed posts. |
| Bulk actions bypass all policies | No authorization checks inside `bulkAction` | Implement per‑post `Gate::authorize` calls before executing the action. |

---
```