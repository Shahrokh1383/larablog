---

# Tags Module Documentation

## 1. Overview

The Tags module provides a flat, non‑hierarchical labeling system for blog posts. Each tag can be attached to many posts and each post can have many tags (many‑to‑many).

**Key characteristics (backend):**

- **Unique slug & name** – enforced at database and application level.
- **Immutable slugs** – The slug is automatically generated from the name **only at creation time**. Updating the name does **not** change the slug, preserving SEO and existing URLs.
- **Permanent deletion** – Tags are hard‑deleted; there is no `SoftDeletes`. Deletion is blocked if the tag still has active (non‑soft‑deleted) posts.
- **Admin‑only management** – Tag CRUD routes are accessible only to users with the `Admin` role. The `manage tags` Spatie permission is **not sufficient** to enter the admin tag section (see §5).
- **Public access** – A public route `/tags/{slug}` displays all published posts for that tag.
- **Transactions & logging** – All write operations are wrapped in database transactions and logged with contextual information.

---

## 2. File Inventory

| File | Role |
|------|------|
| `database/migrations/2025_12_22_193102_create_tags_table.php` | Creates the `tags` table |
| `database/migrations/2025_12_22_193616_create_post_tag_table.php` | Creates the `post_tag` pivot table |
| `app/Models/Tag.php` | Tag Eloquent model |
| `app/Models/Post.php` | Post model – relevant parts: `tags()` relationship and `scopeWithTag` |
| `app/Http/Controllers/Admin/TagAdminController.php` | Admin CRUD controller |
| `app/Http/Requests/TagRequest.php` | Form Request for create/update validation & authorization |
| `app/Policies/TagPolicy.php` | Authorization policy for Tag |
| `routes/web.php` | Admin & public route definitions |
| `app/Providers/AuthServiceProvider.php` | Policy registration and the `Admin` bypass gate |

---

## 3. Database Schema

### 3.1 `tags` Table

| Column       | Type              | Constraints                          | Notes                         |
|--------------|-------------------|--------------------------------------|-------------------------------|
| `id`         | `BIGINT UNSIGNED` | `PRIMARY KEY`, auto‑increment        |                               |
| `name`       | `VARCHAR(50)`     | `NOT NULL`, `UNIQUE`                 | Display name                  |
| `slug`       | `VARCHAR(50)`     | `NOT NULL`, `UNIQUE`                 | URL‑safe identifier           |
| `created_at` | `TIMESTAMP`       | nullable                             |                               |
| `updated_at` | `TIMESTAMP`       | nullable                             |                               |

- **Indexes**: Two unique indexes on `name` and `slug`.
- **No soft‑deletes**: Deletion is permanent.

### 3.2 `post_tag` Pivot Table

| Column       | Type              | Constraints                              | Notes                              |
|--------------|-------------------|------------------------------------------|------------------------------------|
| `id`         | `BIGINT UNSIGNED` | `PRIMARY KEY`, auto‑increment            | Needed because `withTimestamps()` requires an `id` |
| `post_id`    | `BIGINT UNSIGNED` | `FOREIGN KEY → posts(id) ON DELETE CASCADE` | |
| `tag_id`     | `BIGINT UNSIGNED` | `FOREIGN KEY → tags(id) ON DELETE CASCADE`  | |
| `created_at` | `TIMESTAMP`       | nullable                                 | |
| `updated_at` | `TIMESTAMP`       | nullable                                 | |

- **Unique constraint**: `UNIQUE(post_id, tag_id)` – prevents duplicate tag‑post associations.
- **Cascade behaviour**:
  - Deleting a post removes its pivot rows.
  - Deleting a tag removes its pivot rows; the posts themselves are **not** deleted.

---

## 4. Models

### 4.1 `Tag` Model (`app/Models/Tag.php`)

**Traits:** `HasFactory`, `HasSlug` (Spatie)  
**Fillable:** `name`, `slug`  
**Route key name:** Default (`id`). The public route uses explicit binding `{tag:slug}` (see §8.2).

#### Slug Generation
```php
public function getSlugOptions(): SlugOptions
{
    return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->doNotGenerateSlugsOnUpdate();   // slug is immutable after creation
}
```
- The slug is generated automatically from `name` only when the model is first persisted.
- Subsequent updates to `name` leave the slug unchanged.

#### Relationships
```php
public function posts(): BelongsToMany
{
    return $this->belongsToMany(Post::class)->withTimestamps();
}
```
- Standard many‑to‑many with `Post`.
- `withTimestamps()` keeps `created_at`/`updated_at` in the pivot table up to date.

#### Accessors
```php
public function getPostsCountAttribute(): int
{
    return $this->posts()->count();
}
```
- Returns the count of **non‑soft‑deleted** posts associated with the tag.
- **Note:** The admin index uses a different count (includes soft‑deleted posts) via `withCount`; this accessor is for other contexts.

### 4.2 `Post` Model (relevant snippets)

```php
public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class)->withTimestamps();
}

public function scopeWithTag($query, string $tagSlug): void
{
    $query->whereHas('tags', fn($q) => $q->where('slug', $tagSlug));
}
```
- `scopeWithTag` can be used anywhere to filter posts by a tag slug.

---

## 5. Authorization & Permissions

Authorization is implemented through a combination of:
- Spatie Roles & Permissions
- Laravel Policies (`TagPolicy`)
- Middleware on routes
- An Admin bypass gate

### 5.1 Roles and Permissions

The module expects a Spatie permission named:  
**`manage tags`**

- This permission is intended for content managers (e.g., `Editor` role).  
- However, **the current route protection does not allow Editors to access tag management** (see §5.2).

Setup example (seeder):
```php
Permission::create(['name' => 'manage tags']);
$editor = Role::findByName('Editor');
$editor->givePermissionTo('manage tags');
```

### 5.2 Route Middleware for Admin Panel

In `web.php`, the entire admin section for tags is wrapped with:

```php
Route::middleware(['auth', 'role:Admin'])    // <-- requires Admin role
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ...
        Route::resource('tags', TagAdminController::class)
            ->except(['show'])
            ->middleware('can:manage tags');  // <-- additional gate check
    });
```

**Critical behaviour:**
- The `role:Admin` middleware (from Spatie) restricts access to users with the `Admin` role. **Users with the `Editor` role, even if they have the `manage tags` permission, cannot access `/admin/tags`.**
- Inside the group, the resource route adds `middleware('can:manage tags')`. This checks a **gate ability** named `manage tags`. **No gate with that name is defined** anywhere in the application. Consequently:
  - For **Admin** users: `Gate::before` (see §5.3) returns `true` for everything, so they pass regardless.
  - For any other user: the gate check fails, making the middleware redundant for Admins and blocking non‑Admins even if the role middleware were removed.

**⚠️ Known issue:** The `can:manage tags` middleware is effectively dead code because the `role:Admin` middleware already restricts access, and the ability itself is undefined. If you later want to allow Editors to manage tags, you must:
1. Remove the `role:Admin` middleware from the group (or adjust it).
2. Define a gate or policy ability for `manage tags` (for example, map it to `TagPolicy@viewAny`).

### 5.3 Admin Bypass Gate

In `AuthServiceProvider::boot()`:
```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Admin') ? true : null;
});
```
- Users with the `Admin` role automatically pass **every** gate and policy check, regardless of permissions. This includes `TagPolicy` methods.

### 5.4 `TagPolicy` (`app/Policies/TagPolicy.php`)

Registered in `AuthServiceProvider`:
```php
protected $policies = [
    Tag::class => TagPolicy::class,
];
```

| Method     | Logic                                      | Effect |
|------------|---------------------------------------------|--------|
| `viewAny`  | `$user->hasPermissionTo('manage tags')`    | Controls who can list tags in admin (when policy is called via `Gate::authorize('viewAny', Tag::class)`) |
| `view`     | `return true;`                              | Everyone may view a tag (public) |
| `create`   | `$user->hasPermissionTo('manage tags')`    | Controls tag creation via policy |
| `update`   | `$user->hasPermissionTo('manage tags')`    | Controls tag editing via policy |
| `delete`   | If `$user->hasRole('Admin')` → `true`<br>Else if `$tag->posts()->exists()` → `false`<br>Else `$user->hasPermissionTo('manage tags')` | Admins can delete any tag; non‑admins with `manage tags` can delete tags that have no active posts |

**Important:** The policy methods are only invoked when you explicitly check a policy ability using the correct method name (e.g., `Gate::authorize('viewAny', Tag::class)`, `Gate::authorize('update', $tag)`). The route middleware `can:manage tags` does **not** trigger `viewAny` automatically.

### 5.5 Authorization Flow in the Controller

The controller explicitly calls `Gate::authorize()` for most actions:
- `index()` → `Gate::authorize('viewAny', Tag::class)` → invokes `TagPolicy::viewAny()`
- `create()` → `Gate::authorize('create', Tag::class)` → invokes `TagPolicy::create()`
- `edit($tag)` → `Gate::authorize('update', $tag)` → invokes `TagPolicy::update()`
- `destroy($tag)` → `Gate::authorize('delete', $tag)` → invokes `TagPolicy::delete()`

For `store()` and `update()`, authorization is handled by the Form Request (see §6).

**Result:** Because of the `role:Admin` middleware, the controller is only reachable by Admins. However, the explicit policy checks still provide a second layer and would be effective if the middleware were relaxed.

---

## 6. Form Request: `TagRequest` (`app/Http/Requests/TagRequest.php`)

This class is injected into `store()` and `update()` and handles both authorization and validation.

### 6.1 Authorization

```php
public function authorize(): bool
{
    return match($this->method()) {
        'POST'         => Gate::allows('create tags'),                  // ⚠️ error
        'PUT', 'PATCH' => Gate::allows('update', $this->route('tag')),
        'DELETE'       => Gate::allows('delete', $this->route('tag')),
        default        => false,
    };
}
```

**Critical bug:**  
For `POST` requests, it uses a **non‑existent gate ability** `'create tags'`. No policy method or gate definition exists for this string. As a result:
- `Gate::allows('create tags')` returns `false` for **everyone**, **including Admins** (the Admin bypass only works when a gate/policy is actually defined; undefined abilities return `false`).
- **Consequence:** Currently **no one** can create a new tag through this Form Request. The `store()` method will fail with a 403 Forbidden response.

**Fix:** Replace the `POST` line with:
```php
'POST' => Gate::allows('create', Tag::class),
```
This will invoke `TagPolicy::create()` and behave as expected: Admins (via bypass) and users with `manage tags` permission will be authorized.

> **Note:** Even after this fix, the `role:Admin` middleware still prevents non‑Admins from reaching the controller. The fix is important for policy consistency and future role changes.

### 6.2 Validation Rules

```php
$tagId = $this->route('tag')?->id;

return [
    'name' => [
        'required', 'string', 'min:2', 'max:50',
        Rule::unique('tags')->ignore($tagId),
    ],
    'slug' => [
        'nullable', 'string', 'min:2', 'max:50',
        Rule::unique('tags')->ignore($tagId),
        'regex:/^[a-z0-9-]+$/',
    ],
];
```

- `slug` is optional. If empty or missing, a fallback slug is generated before validation (see §6.3).
- The `regex` ensures slug contains only lowercase letters, digits, and hyphens.
- The unique rule ignores the current tag’s ID when updating.

### 6.3 Data Preparation

```php
protected function prepareForValidation(): void
{
    if (!$this->slug && $this->name) {
        $this->merge([
            'slug' => \Illuminate\Support\Str::slug($this->name),
        ]);
    }
}
```
If no `slug` is provided, a slug is generated from `name` using `Str::slug`. This is **redundant** because the Spatie sluggable trait also generates the slug on creation. However, it does no harm and ensures the slug field is present for validation.

### 6.4 Retrieving Validated Data

```php
public function getTagData(): array
{
    return $this->validated();
}
```
Used by the controller to obtain the array `['name' => ..., 'slug' => ...]` for `Tag::create()` or `$tag->update()`.

---

## 7. Controller: `TagAdminController`

All methods are protected by route middleware (`auth`, `role:Admin`). Additional authorization inside methods is performed as described below.

### 7.1 `index()`

- **Authorization:** `Gate::authorize('viewAny', Tag::class)` (redundant with middleware, but kept for correctness)
- **Logic:**
  ```php
  $tags = Tag::withCount(['posts' => fn($q) => $q->withTrashed()])
            ->latest()
            ->paginate(20);
  ```
  - Counts **all** posts, including soft‑deleted ones. This is a deliberate design choice to show the total historical usage of a tag.
  - `latest()` orders by `created_at DESC`.
- **View variables:** `$tags` (paginator instance of `Tag` with `posts_count` attribute).

### 7.2 `create()`

- **Authorization:** `Gate::authorize('create', Tag::class)`
- **View:** `admin.tags.create` – no additional data passed.

### 7.3 `store(TagRequest $request)`

- **Authorization:** Performed by `TagRequest::authorize()` (see §6.1 for current bug).
- **Logic:**
  ```php
  DB::transaction(function () use ($request) {
      $tag = Tag::create($request->getTagData());
      Log::info('Tag created', [
          'tag_id'     => $tag->id,
          'name'       => $tag->name,
          'created_by' => Auth::id(),
      ]);
      return $tag;
  });
  ```
- **Redirect:** `route('admin.tags.index')` with a success flash message `"Tag '{$tag->name}' created successfully!"`.

### 7.4 `edit(Tag $tag)`

- **Authorization:** `Gate::authorize('update', $tag)`
- **Route model binding:** Resolves the Tag by its primary key (`id`). The URI is `/admin/tags/{tag}`.
- **View variable:** `$tag`

### 7.5 `update(TagRequest $request, Tag $tag)`

- **Authorization:** `TagRequest::authorize()` (calls `Gate::allows('update', $tag)`)
- **Logic:**
  ```php
  DB::transaction(function () use ($request, $tag) {
      $oldName = $tag->name;
      $tag->update($request->getTagData());
      Log::info('Tag updated', [
          'tag_id'     => $tag->id,
          'old_name'   => $oldName,
          'new_name'   => $tag->name,
          'updated_by' => Auth::id(),
      ]);
  });
  ```
- **Redirect:** `route('admin.tags.index')` with success flash `"Tag '{$tag->name}' updated successfully!"`.

### 7.6 `destroy(Tag $tag)`

- **Authorization:** `Gate::authorize('delete', $tag)` (policy checks if user is Admin or if tag has posts)
- **Additional guard in controller:**
  ```php
  if ($tag->posts()->exists()) {
      return redirect()->back()
          ->with('error', "Cannot delete tag '{$tag->name}' because it has associated posts.");
  }
  ```
  This check only considers **active (non‑soft‑deleted)** posts. See §11.3.
- **Deletion logic:**
  ```php
  DB::transaction(function () use ($tag) {
      $name = $tag->name;
      $tag->delete();
      Log::info('Tag deleted', [
          'tag_id'    => $tag->id,
          'name'      => $name,
          'deleted_by' => Auth::id(),
      ]);
  });
  ```
- **Redirect:** `route('admin.tags.index')` with success flash.

---

## 8. Routes

### 8.1 Admin Routes (Backend)

Defined inside:
- `prefix: admin`
- `name prefix: admin.`
- `middleware: auth, role:Admin`

```php
Route::resource('tags', TagAdminController::class)
    ->except(['show'])
    ->middleware('can:manage tags');
```

Produces the following endpoints:

| HTTP Method | URI                    | Name                | Controller Action |
|-------------|------------------------|---------------------|-------------------|
| GET         | `/admin/tags`          | `admin.tags.index`   | `index`           |
| GET         | `/admin/tags/create`   | `admin.tags.create`  | `create`          |
| POST        | `/admin/tags`          | `admin.tags.store`   | `store`           |
| GET         | `/admin/tags/{tag}/edit` | `admin.tags.edit`  | `edit`            |
| PUT/PATCH   | `/admin/tags/{tag}`    | `admin.tags.update`  | `update`          |
| DELETE      | `/admin/tags/{tag}`    | `admin.tags.destroy` | `destroy`         |

- `{tag}` is resolved by primary key (`id`) via default route model binding.
- `show` is excluded – no admin detail page exists.

### 8.2 Public Route

```php
Route::get('tags/{tag:slug}', function (\App\Models\Tag $tag) {
    $posts = $tag->posts()
                ->with(['author', 'categories', 'tags'])
                ->published()
                ->latest('published_at')
                ->paginate(12);

    return view('posts.index', [
        'posts'   => $posts,
        'filter'  => "Tag: #{$tag->name}",
    ]);
})->name('tags.show');
```

- Explicit route model binding `{tag:slug}` resolves the tag using the `slug` column.
- Only published posts are shown (`->published()` scope).
- The same `posts.index` view is reused, with an extra `$filter` variable.

---

## 9. View Backend Contract

**Note:** Frontend implementation details will be covered separately. Below are the backend data contracts that each view receives.

### 9.1 `admin.tags.index`
- **Provided variable:** `$tags` – instance of `LengthAwarePaginator` containing `Tag` models, each with an additional `posts_count` attribute (total posts including trashed).
- **Expected functionality:** Table of tags, links to edit/delete, link to create form, pagination.

### 9.2 `admin.tags.create`
- **Provided variables:** none (aside from shared data like `$errors`).
- **Expected form fields:** `name` (required), `slug` (optional).

### 9.3 `admin.tags.edit`
- **Provided variables:** `$tag` – the `Tag` model instance.
- **Expected form fields:** `name` pre‑filled with `$tag->name`, `slug` pre‑filled with `$tag->slug`.

### 9.4 `posts.index` (public page filtered by tag)
- **Provided variables:**
  - `$posts` – paginator of `Post` models (only published).
  - `$filter` – string like `"Tag: #laravel"`.
- **Expected behaviour:** Display the filter label prominently, list the posts.

---

## 10. Integration with Posts (Backend)

### 10.1 Attaching Tags to a Post

Tag assignment is handled outside the Tags module (in `PostAdminController`). The standard pattern:
```php
$post->tags()->sync($request->input('tags', []));
```
Where `$request->tags` is an array of tag IDs.

### 10.2 Querying Posts by Tag

Use the `Post` model’s `scopeWithTag`:
```php
$posts = Post::withTag('laravel')->published()->get();
```
Or directly with `whereHas`:
```php
$posts = Post::whereHas('tags', fn($q) => $q->where('slug', 'laravel'))->get();
```

---

## 11. Logging & Auditing

All write operations are logged using `Log::info` with structured context.

| Operation | Context keys                        | Example message           |
|-----------|-------------------------------------|---------------------------|
| Create    | `tag_id`, `name`, `created_by`     | `Tag created`             |
| Update    | `tag_id`, `old_name`, `new_name`, `updated_by` | `Tag updated`     |
| Delete    | `tag_id`, `name`, `deleted_by`     | `Tag deleted`             |

Logs are written to the default Laravel log channel (`storage/logs/laravel.log`).

---

## 12. Business Rules & Edge Cases (Backend)

1. **Immutable slugs** – Slugs are set once and never change. Use the `TagRequest` `prepareForValidation` and the model’s `SlugOptions` to guarantee this.
2. **Name uniqueness** – Enforced at DB level (`UNIQUE` index) and by Form Request validation. Be aware that the database collation (typically `utf8mb4_unicode_ci`) treats names case‑insensitively.
3. **Deletion rules** – A tag cannot be deleted if it has **active** posts (not soft‑deleted). The check uses `$tag->posts()->exists()`. Soft‑deleted posts are ignored; a tag with only trashed posts **can** be deleted. If this is not desired, change the condition to `$tag->posts()->withTrashed()->exists()`.
4. **Pivot cascade** – Deleting a tag removes all its pivot entries; respective posts remain untouched.
5. **Admin bypass** – Users with the `Admin` role can perform any action on tags regardless of the `manage tags` permission.
6. **Current access restriction** – Because of `role:Admin` middleware, **only Admins** can access the admin tag pages. The `manage tags` permission alone is not enough.

---

## 13. Known Issues & Recommended Fixes

### Issue 1: `TagRequest` uses non‑existent `create tags` ability
- **Effect:** `store()` always returns 403.
- **Fix:** In `TagRequest::authorize()`, change:
  ```php
  'POST' => Gate::allows('create', Tag::class),
  ```

### Issue 2: Route middleware `can:manage tags` is undefined
- **Effect:** The middleware is ineffective; it only works for Admins through the bypass. Should you later allow Editors, it will block them.
- **Fix (if Editors need access):**
  1. Create a gate: `Gate::define('manage tags', [TagPolicy::class, 'viewAny']);` or simply remove the `can:manage tags` middleware and rely on controller–level `Gate::authorize`.
  2. Remove or adjust the `role:Admin` middleware to allow roles with the `manage tags` permission.

### Issue 3: Redundant slug fallback in `TagRequest`
- Not harmful, but can be removed for clarity since Spatie handles slug generation.

---

## 14. Testing Recommendations

### 14.1 Unit Tests
- **Tag model:** slug generation on create (slug exists, immutable on update).
- **Accessor:** `getPostsCountAttribute` with active/trashed posts.
- **Policy:** each method with Admin, user with `manage tags`, user without, and delete with/without associated posts.
- **TagRequest:** validation rules – required fields, duplicate name/slug on create vs update, invalid slug characters.

### 14.2 Feature Tests (Backend)
- **As Admin:**
  - `GET /admin/tags` → 200, sees paginated list.
  - `POST /admin/tags` with valid data → 302, tag created in DB, log entry present.
  - `GET /admin/tags/{id}/edit` → 200.
  - `PUT /admin/tags/{id}` → 302, name changed, slug unchanged.
  - `DELETE /admin/tags/{id}` (no posts) → 302, tag deleted.
- **As Editor (without Admin role)** – all admin tag routes should return 403 (due to `role:Admin` middleware).
- **Public:** `GET /tags/{slug}` → 200, only published posts, correct filter text.
- **Delete with associated posts** → 302 with error flash message, tag remains.
- **Tag with only soft‑deleted posts** → deletion succeeds (if that is the intended behaviour).

---

## 15. Future Improvements (Backend)
- Define a proper `manage tags` gate/policy mapping and adjust middleware to allow non‑Admin roles to manage tags.
- Add an API resource for tags.
- Implement caching for tag lists on public pages.
- Add a `deleted_at` column (SoftDeletes) if restoration might be needed.
- Strengthen deletion guard to optionally block when trashed posts exist.
- Remove slug fallback duplication in `TagRequest`.

---