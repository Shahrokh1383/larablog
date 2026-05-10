```markdown
# Admin Content Management – `content-management.md`

> **Module:** Admin panel for managing blog posts.  
> **Complement to:** [`post-system.md`](../post-system.md) – covers public post features, creation, editing by authors, and general post lifecycle.  
> **This document focuses exclusively on admin‑only operations:** listing, filtering, editing any post, soft‑deleting, restoring, force‑deleting, bulk actions, and the additional audit fields (`updated_by`).

---

## 1. Overview

The **Admin Content Management** module provides administrators and editors with a centralised interface to oversee all posts, regardless of authorship or publication status. It extends the standard post system with:

- A searchable, filterable admin post list (including trashed posts).
- The ability to edit **any** post (bypassing ownership checks).
- Soft‑deletion, restoration, and permanent deletion (force delete) of posts.
- Bulk status changes (publish, draft, delete).
- Audit logging of who last updated a post (`updated_by`).
- Full integration with the `ImageService` for featured image management.

All admin post routes are protected by the `auth` middleware and the `role:Admin,Editor` middleware (except force delete, which is restricted to `Admin` only).

---

## 2. Admin‑Specific Capabilities at a Glance

| Action | Admin | Editor | Author (via admin) |
|--------|-------|--------|--------------------|
| View list of all posts (including drafts) | ✅ | ✅ | ❌ (not in admin panel) |
| Filter by status, author, search | ✅ | ✅ | ❌ |
| Edit any post | ✅ | ✅ (by role) | ❌ |
| Soft‑delete any post | ✅ | except posts by Admin | ❌ |
| Restore from trash | ✅ | ✅ | ❌ |
| Force delete (permanent) | ✅ | ❌ | ❌ |
| Bulk publish / draft / delete | ✅ | (bypasses policy – see §9) | ❌ |

*Editors cannot delete posts written by an `Admin` user – enforced by `PostPolicy`.*

---

## 3. Controller: `PostAdminController`

**Class:** `App\Http\Controllers\Admin\PostAdminController`  
**Base URI:** `/admin/posts`  
**Middleware:** `auth`, `role:Admin,Editor` (defined in `routes/web.php`)

### 3.1 `index(Request $request)`

| Method | URI | Route name |
|--------|-----|-------------|
| `GET` | `/admin/posts` | `admin.posts.index` |

**Features:**

- **Status filter:**  
  - `?status=draft` – only draft posts.  
  - `?status=published` – only published posts.  
  - `?status=trashed` – only soft‑deleted posts.
- **Author filter:** `?author={user_id}` – posts by a specific user.
- **Search:** `?search={term}` – searches `title` and `body` using `LIKE`.
- **Sorting:** The query uses `latest('deleted_at')`.  
  - For trashed posts, this shows newest deletions first ✅  
  - For non‑trashed posts, `deleted_at` is always `NULL`, resulting in **unstable ordering** (depends on database). A better approach would be `latest('published_at')` or `latest('created_at')`.
- **Pagination:** 12 posts per page, with `->withQueryString()`.
- **View data:**  
  - `$posts` – paginated posts with `author` and `categories` eager loaded.  
  - `$authors` – all users with roles `Admin`, `Editor`, or `Author` (plucked `name`, `id`).

**Example request:**
```
GET /admin/posts?status=trashed&author=5&search=laravel
```

### 3.2 `edit(Post $post)`

| Method | URI | Route name |
|--------|-----|-------------|
| `GET` | `/admin/posts/{post}/edit` | `admin.posts.edit` |

- **Authorization:** `Gate::authorize('update', $post)` – passes for Admin/Editor.
- **Renders the same edit view** as the public `posts.edit`.  
  ⚠️ **Important:** The edit form’s action points to the **public** `PUT /posts/{post}` route by default. This is a deliberate shortcut (the admin update method delegates to the public controller). If you need a separate admin edit workflow, create a dedicated view and update method.

### 3.3 `update(PostRequest $request, Post $post, ImageService $imageService)`

| Method | URI | Route name |
|--------|-----|-------------|
| `PUT/PATCH` | `/admin/posts/{post}` | `admin.posts.update` |

- **Authorization:** `Gate::authorize('update', $post)`.
- **Implementation:** Delegates 100% to `PostController@update` using:
  ```php
  return app(\App\Http\Controllers\PostController::class)->update($request, $post, $imageService);
  ```
- **Consequences:**  
  - After a successful update, the user is **redirected to the public post page** (`posts.show`), not back to the admin panel.  
  - The `updated_by` field is set via `PostRequest::getPostData()` (see §4.1).  
  - Image replacement or deletion works exactly as in the public controller.
- **If you need to stay in the admin panel**, override this method with a custom redirect.

### 3.4 `destroy(Post $post, ImageService $imageService)`

| Method | URI | Route name |
|--------|-----|-------------|
| `DELETE` | `/admin/posts/{post}` | `admin.posts.destroy` |

- **Authorization:** `Gate::authorize('delete', $post)`.
- **Delegates to** `PostController@destroy` → **soft delete only** (image files are **not** deleted, they are kept to allow restoration).
- Redirects to the **public** `posts.index` (list of posts). This may be unexpected for admins.

### 3.5 `restore($id)`

| Method | URI | Route name |
|--------|-----|-------------|
| `POST` | `/admin/posts/{id}/restore` | `admin.posts.restore` |

- Finds the post using `Post::onlyTrashed()->findOrFail($id)`.
- **Authorization:** `Gate::authorize('restore', $post)` → allows `Admin` or `Editor`.
- Calls `$post->restore()`, logs the action (`info` level), and redirects back.
- **No image handling needed** – the original image path is still in the database.

### 3.6 `forceDelete($id, ImageService $imageService)`

| Method | URI | Route name |
|--------|-----|-------------|
| `DELETE` | `/admin/posts/{id}/force-delete` | `admin.posts.forceDelete` |

- **Authorization:** `Gate::authorize('forceDelete', $post)` → **Admin only**.
- Performed inside a `DB::transaction`:
  1. Detach all categories and tags.
  2. If `featured_image` is not null, call `$imageService->deleteImage()` to permanently remove original and all thumbnails from disk.
  3. Force‑delete the post record (`$post->forceDelete()`).
  4. Log a **warning** with `post_id` and `deleted_by`.
- Redirects back with success message.

### 3.7 `bulkAction(Request $request)`

| Method | URI | Route name |
|--------|-----|-------------|
| `POST` | `/admin/posts/bulk-action` | (no named route) |

- **Input:**  
  - `action` – one of `delete`, `publish`, `draft`  
  - `ids[]` – array of post IDs  
- **⚠️ CRITICAL SECURITY GAP:** The method performs **no per‑post authorization checks**. It directly executes `Post::whereIn('id', $ids)->delete()` or `update(...)`. This means:
  - An **Editor** could bulk‑delete posts written by an **Admin** (violates `PostPolicy::delete`).
  - An **Editor** could bulk‑publish or draft any post without restriction.
- **Implementations:**
  - `delete` – soft‑deletes only non‑trashed posts (trashed posts are ignored).
  - `publish` – sets `status = 'published'` and `published_at = now()`.
  - `draft` – sets `status = 'draft'`.
- **Recommendation:** Replace with a loop that calls `Gate::authorize()` for each post before acting.

---

## 4. Form Request: `PostRequest` (Admin Aspects)

The same `PostRequest` is used by both public and admin controllers. Its behaviour is identical, but admin usage highlights a few points:

### 4.1 Handling of `updated_by`

In the `getPostData()` method:

```php
if ($post) { // update scenario
    unset($validated['user_id']);           // never change author
    $validated['updated_by'] = $currentUserId;
} else { // create scenario
    $validated['user_id'] = $currentUserId;
}
```

- **Admin updates** automatically record who performed the edit in the `updated_by` column.
- The `updated_by` field is **fillable** in the `Post` model.

### 4.2 Validation Rules (Admin‑Relevant)

- `status` – can be `draft` or `published`.
- `published_at` – must be `>= now` (no backdating). Admins cannot bypass this rule.
- `featured_image` – respects `config('image.max_upload_size')` and allowed mime types.
- `allow_comments` – checkbox logic: present → `true`, absent → `false`.

> No additional admin‑specific validation (e.g., forcing a review) is present.

### 4.3 Slug Generation

If `slug` is empty, it is auto‑generated from `title` via `Str::slug()` in `prepareForValidation()`. This applies to both admin and public forms.

---

## 5. Policy: `PostPolicy` (Admin Overrides)

The policy is defined in `app/Policies/PostPolicy.php`. For admin users the key methods are:

| Ability | Admin | Editor | Notes |
|---------|-------|--------|-------|
| `update` | `true` (by role) | `true` (by role) | No ownership check; editors can edit any post. |
| `delete` | `true` | `true` **only if** the post’s author is **not** an Admin | Prevents editors from deleting admin‑authored posts. |
| `restore` | `true` | `true` | |
| `forceDelete` | `true` | `false` | Only Admin can permanently delete. |
| `publish` | (not directly called by admin controller) | | The `publish` ability is used elsewhere. |

> Because of the global `Gate::before` in `AuthServiceProvider` that returns `true` for any user with the `Admin` role, the policy’s explicit checks for `Admin` are redundant but harmless.

---

## 6. Model Fields Relevant to Admin

The `Post` model (`app/Models/Post.php`) includes two fields that are particularly important for admin operations:

- **`updated_by`** – `foreignId` referencing `users.id`.  
  - Set automatically by `PostRequest` during any update.  
  - Allows auditing: “Who last edited this post?”
- **`allow_comments`** – `boolean`, default `true`.  
  - Admins can disable comments globally on a post via the edit form (checkbox).

Both are fillable and cast correctly.

---

## 7. Routes Summary

All routes are defined in `routes/web.php` under the `admin` prefix, with middleware `['auth', 'role:Admin,Editor']`.

| Method | URI | Controller Method | Route Name | Notes |
|--------|-----|-------------------|-------------|-------|
| GET | `/admin/posts` | `index` | `admin.posts.index` | List with filters |
| GET | `/admin/posts/{post}/edit` | `edit` | `admin.posts.edit` | Uses public edit view |
| PUT/PATCH | `/admin/posts/{post}` | `update` | `admin.posts.update` | Delegates to public controller |
| DELETE | `/admin/posts/{post}` | `destroy` | `admin.posts.destroy` | Soft delete, redirects to public index |
| POST | `/admin/posts/{id}/restore` | `restore` | `admin.posts.restore` | Restore from trash |
| DELETE | `/admin/posts/{id}/force-delete` | `forceDelete` | `admin.posts.forceDelete` | Admin only, permanent |
| POST | `/admin/posts/bulk-action` | `bulkAction` | (none) | **No per‑post authorization** |

> The `create` and `store` actions are **not** exposed in the admin panel – post creation is handled via the public `POST /posts` route (which requires the `create posts` permission). Admins can still create posts there.

---

## 8. Image Service Integration

The admin panel relies on the same `ImageService` as the public interface. The key operations are:

- **Update (with new image):**  
  Delegated to `PostController@update`, which calls `$imageService->storeImage()` and deletes the old image.
- **Delete image via checkbox:**  
  When `delete_image=1` is sent, the old image is removed using `$imageService->deleteImage()`.
- **Force delete:**  
  `$imageService->deleteImage($post->featured_image)` is called inside the transaction before `forceDelete()`.

All thumbnail generation and fallback logic is described in [`media-services.md`](../media-services.md). The admin panel does not introduce any new image handling behaviour.

---

## 9. Bulk Actions – Important Caveats

The `bulkAction` method is currently **unsafe** for production use because it bypasses all policy checks. An **Editor** could:

- Delete posts written by an **Admin**.
- Publish any draft post, even without the `publish posts` permission.

**To fix:**  
Instead of a single `whereIn`, retrieve each post and authorise the action:

```php
foreach ($posts as $post) {
    if (Gate::allows($action, $post)) {
        // perform action
    }
}
```

Until this is fixed, either disable the bulk action UI or restrict it to `Admin` only.

---

## 10. Practical Examples

### 10.1 Admin edits a post and stays in admin panel (workaround)

Because the default update redirects to the public post page, you can modify `PostAdminController::update`:

```php
public function update(PostRequest $request, Post $post, ImageService $imageService)
{
    Gate::authorize('update', $post);
    $postController = app(\App\Http\Controllers\PostController::class);
    $response = $postController->update($request, $post, $imageService);
    return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
}
```

### 10.2 Force delete a trashed post

```http
DELETE /admin/posts/42/force-delete
Authorization: Admin user
```

**Result:**  
- Post record permanently removed.  
- All associated category/tag pivot rows deleted.  
- Featured image and all thumbnails deleted from disk.  
- Log entry with `warning` level.

### 10.3 Bulk publish selected posts (current unsafe implementation)

```http
POST /admin/posts/bulk-action
Content-Type: application/x-www-form-urlencoded

action=publish&ids[]=5&ids[]=7&ids[]=12
```

**Current behaviour:** Sets `status='published'` and `published_at=now()` on posts 5, 7, 12 regardless of who wrote them.  
**Recommended behaviour after fix:** check each post against `PostPolicy::publish` or `update`.

### 10.4 Filtering the admin post list

```http
GET /admin/posts?status=draft&author=3&sort=title&dir=asc
```

*Note: The current controller ignores custom `sort`/`dir` parameters – the sorting is hardcoded to `latest('deleted_at')`. Extend the controller to support custom sorting if needed.*

---

## 11. Known Issues & Technical Debt

| Issue | Severity | Impact & Fix |
|-------|----------|--------------|
| Bulk actions bypass all policies | **Critical** | Editors can delete admin posts, publish without permission. Fix by adding per‑post `Gate::authorize`. |
| Admin update/destroy redirect to public routes | Medium | Confusing UX; admins expect to stay in admin panel. Override methods with custom redirects. |
| Sorting on admin index uses `latest('deleted_at')` for all | Low | Non‑trashed posts have `deleted_at = NULL` → random order. Change to `latest('published_at')` or `latest('created_at')` for active posts. |
| Admin edit form points to public update route | Low | The form’s `action` attribute is hardcoded to `route('posts.update', $post)`. Change it to `route('admin.posts.update', $post)` if you create a dedicated admin update method. |
| No admin‑specific post creation interface | Low | Admins must use the public `POST /posts` route (which requires `create posts` permission). Consider adding an admin create view for consistency. |
| Missing `create` and `store` in admin resource | Intentional | The resource uses `except(['create', 'store', 'show'])`. This is by design – post creation is done via public form. |

---

## 12. Cross‑References to Existing Documentation

| Topic | Document | Section |
|-------|----------|---------|
| General post creation, editing by authors, public routes | [`post-system.md`](../post-system.md) | Entire document |
| Image upload, thumbnail generation, deletion | [`media-services.md`](../media-services.md) | §§5, 6, 8 |
| Role & permission setup, `Gate::before`, role middleware | [`authentication.md`](../authentication.md) | Role checks, Spatie integration |
| Admin user management (ban, role change) | [`user-management.md`](../user-management.md) | Not directly related, but shares same middleware |
| Categories & tags admin management | [`categories.md`](../categories.md), `tags.md` | Admin CRUD for taxonomies |

---

## 13. Troubleshooting Quick Reference

| Symptom | Likely Cause | Solution |
|---------|--------------|----------|
| After editing a post, I’m redirected to the public post page | `PostAdminController::update` delegates to `PostController` | Override method and redirect to `admin.posts.index`. |
| Bulk delete does not work on trashed posts | `whereIn('id', $ids)->delete()` ignores soft‑deleted rows | Use `Post::withTrashed()->whereIn(...)->delete()` if you want to bulk‑delete already trashed posts. |
| An editor can bulk‑delete an admin’s post | No per‑post policy check in `bulkAction` | Implement authorisation loop (see §9). |
| The admin post list order seems random (non‑trashed) | Sorting by `deleted_at` which is always `NULL` for active posts | Change query to `$query->latest('published_at')` when status is not `trashed`. |
| Image thumbnails not showing after admin upload | Thumbnails not generated? Check `config/image.php` sizes | Run `php artisan storage:mkdirs` and verify GD extension is installed. |
| Editor cannot delete a post (403) even though they own it | The post’s author might be an `Admin` | Policy blocks editors from deleting admin‑authored posts. Check `$post->author->hasRole('Admin')`. |

---

## 14. Extending the Module

- **Add a new admin‑only post field** (e.g., `internal_notes`):  
  1. Create migration to add column to `posts` table.  
  2. Add field to `$fillable` in `Post` model.  
  3. Extend `PostRequest` rules and `getPostData()` to include it.  
  4. Update the admin edit view to display the field.  
- **Add custom bulk action** (e.g., `archive`):  
  1. Extend `bulkAction` switch statement.  
  2. Ensure you add proper authorisation (do not repeat the existing security gap).  
- **Implement soft‑delete permanent cleanup:**  
  Create a scheduled command that force‑deletes posts trashed for >30 days using `Post::onlyTrashed()->where('deleted_at', '<', now()->subDays(30))->each(...)` with image cleanup.

---