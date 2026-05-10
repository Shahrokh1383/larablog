**Admin Taxonomy Management (Categories & Tags)**  
 
> This document covers **Admin‑exclusive** operations for managing the site’s taxonomies: creating, editing, deleting categories and tags, and viewing their listing. It assumes you are familiar with the general taxonomy concepts described in the linked documents.  

---

## 1. Purpose & Scope  

The admin taxonomy module lets administrators (and in the future, properly permissioned editors, once route restrictions are relaxed) control the categories and tags used across the blog. It provides:

- A paginated list of categories/tags, including the total number of associated posts (counting soft‑deleted ones).  
- Creation of new taxonomies with automatic slug generation (Spatie Sluggable at model level, plus a redundant fallback in Form Requests).  
- Editing of names and slugs (with known slug‑stability caveats – see §10).  
- Protected deletion: a taxonomy can only be removed if **no** live (non‑soft‑deleted) posts are attached.  
- Full transaction‑wrapped writes and structured logging for auditing.  

All actions are currently restricted to the `Admin` role via route middleware. The underlying Policies require specific Spatie permissions (`manage categories`, `manage tags`) which are **not** sufficient to access the routes under the current middleware configuration (see §8).  

---

## 2. Files at a Glance  

| File | Role |
|------|------|
| `app/Http/Controllers/Admin/CategoryAdminController.php` | CRUD for admin categories |
| `app/Http/Controllers/Admin/TagAdminController.php` | CRUD for admin tags |
| `app/Http/Requests/CategoryRequest.php` | Validation & authorization for categories |
| `app/Http/Requests/TagRequest.php` | Validation & authorization for tags |
| `app/Policies/CategoryPolicy.php` | Authorization rules for categories |
| `app/Policies/TagPolicy.php` | Authorization rules for tags |
| `app/Models/Category.php` | Category model (slug, posts relationship) |
| `app/Models/Tag.php` | Tag model (slug, posts relationship) |
| `app/Http/Middleware/CheckRole.php` | Role‑based middleware (`role:Admin`) |
| `app/Providers/AuthServiceProvider.php` | Policy registration & `Gate::before` for Admins |
| `routes/web.php` (admin taxonomy section) | Route definitions under `prefix=admin` |
| `database/migrations/…_create_categories_table.php` | Categories schema |
| `database/migrations/…_create_tags_table.php` | Tags schema |
| `database/migrations/…_create_category_post_table.php` | Category–Post pivot |
| `database/migrations/…_create_post_tag_table.php` | Tag–Post pivot |

---

## 3. Controllers  

Both `CategoryAdminController` and `TagAdminController` follow an identical pattern. They are described together below; only the model class and route names differ.

**Namespace:** `App\Http\Controllers\Admin`  
**Applied middleware:** `auth` + `role:Admin` (from the route group)  

Each public method uses `Gate::authorize()` to enforce the corresponding Policy method, but because all users reaching the controller already have the `Admin` role, the global `Gate::before` (see §8) renders these checks redundant for now.  

### 3.1 `index()`  

**Route:** `GET /admin/categories` (`admin.categories.index`) · `GET /admin/tags` (`admin.tags.index`)  

**Authorization:** `Gate::authorize('viewAny', Category::class)` / `Tag::class`  

**Logic:**  
```php
Model::withCount(['posts' => fn($q) => $q->withTrashed()])
    ->latest()
    ->paginate(15); // categories
    ->paginate(20); // tags
```
- The `posts_count` attribute includes **soft‑deleted** posts, giving admins a complete picture of every association.  
- `latest()` orders by `created_at DESC`.  

**View variables:** `$categories` or `$tags` (paginator instance with `posts_count`).  

---

### 3.2 `create()`  

**Route:** `GET /admin/categories/create` · `GET /admin/tags/create`  

**Authorization:** `Gate::authorize('create', Category::class)` / `Tag::class`  

**Returns:** empty form view.  

---

### 3.3 `store(XRequest $request)`  

**Route:** `POST /admin/categories` · `POST /admin/tags`  

**Authorization:** Performed inside the Form Request (see §4).  

**Logic:**  
1. Wrapped in `DB::transaction`.  
2. Model is created using validated data (`$request->getCategoryData()` / `getTagData()`).  
3. Logged at `info` level: message `Category created` / `Tag created`, with `category_id`/`tag_id`, `name`, `created_by`.  
4. Redirect to index with a success flash message, e.g. `"Category 'Laravel' created successfully!"`.  

---

### 3.4 `edit(Model $model)`  

**Route:** `GET /admin/categories/{category}/edit` · `GET /admin/tags/{tag}/edit`  

**Authorization:** `Gate::authorize('update', $model)`  

**Returns:** edit view with the existing model.  

---

### 3.5 `update(XRequest $request, Model $model)`  

**Route:** `PUT/PATCH /admin/categories/{category}` · `PUT/PATCH /admin/tags/{tag}`  

**Authorization:** Handled by the Form Request.  

**Logic:**  
1. Transaction: save old name, update model with validated data, log old/new name and `updated_by`.  
2. Redirect to index with success.  

> ⚠️ **Slug behaviour on update:** If the `slug` field is left empty, the Form Request’s `prepareForValidation()` will generate a new slug from the current `name` (even if the name did not change – see §4.2). Always pass the existing slug value in update forms to keep it stable.  

---

### 3.6 `destroy(Model $model)`  

**Route:** `DELETE /admin/categories/{category}` · `DELETE /admin/tags/{tag}`  

**Authorization:**  
- `Gate::authorize('delete', $model)` – Policy check (admin always passes).  
- **Additional hard guard in the controller:**  
  ```php
  if ($model->posts()->exists()) {
      return back()->with('error', "Cannot delete [taxonomy] '{$model->name}' because it has associated posts.");
  }
  ```
  This check uses the default relationship, which **excludes soft‑deleted** posts. So a taxonomy with only trashed posts **can** be deleted.  

**Logic (if allowed):**  
1. Transaction: capture name, delete model (cascading pivot rows), log `deleted_by`.  
2. Redirect to index with success flash.  

---

## 4. Form Requests  

### 4.1 Authorization  

Both `CategoryRequest` and `TagRequest` share a critical bug in their `authorize()` methods.  

```php
public function authorize(): bool
{
    return match($this->method()) {
        'POST'          => Gate::allows('create categories'), // or 'create tags'
        'PUT', 'PATCH'  => Gate::allows('update', $this->route('category')), // or 'tag'
        'DELETE'        => Gate::allows('delete', $this->route('category')), // dead code
        default         => false,
    };
}
```

- **`POST` line uses a non‑existent gate ability** (`create categories` / `create tags`). No such gate is defined anywhere.  
- **Why it currently works:** The global `Gate::before` returns `true` for any `Admin` user, so `Gate::allows('create categories')` becomes `true` for them. For non‑Admins (who cannot reach the controller because of the `role:Admin` middleware) it would always return `false`.  
- **Recommended fix:** Replace with `Gate::allows('create', Category::class)` / `Gate::allows('create', Tag::class)` to invoke the Policy correctly.  

The `DELETE` branch is never executed because the controller does not inject the Form Request for deletion; it is harmless dead code.  

---

### 4.2 Validation Rules  

**CategoryRequest**  

| Field         | Rules | Notes |
|---------------|-------|-------|
| `name`        | required, string, min:2, max:100, unique (ignoring current ID on update) | |
| `slug`        | nullable, string, min:2, max:100, unique, regex:`/^[a-z0-9-]+$/` | |
| `description` | nullable, string, max:1000 | |

**TagRequest**  

| Field | Rules | Notes |
|-------|-------|-------|
| `name` | required, string, min:2, max:50, unique | |
| `slug` | nullable, string, min:2, max:50, unique, regex:`/^[a-z0-9-]+$/` | |

---

### 4.3 Slug Auto‑generation (`prepareForValidation`)  

Both requests contain:  
```php
if (!$this->slug && $this->name) {
    $this->merge(['slug' => \Illuminate\Support\Str::slug($this->name)]);
}
```

This runs on **every create and update request** when `slug` is empty.  
- On **create**: complements the model‑level Spatie slug generation (harmless redundancy).  
- On **update**: it can silently **change the slug** if the admin left the slug field empty and the name was edited—or even if the name was *not* changed but the slug field was cleared.  

**Best practice:** Always include the current slug value in edit forms to preserve SEO stability.  

---

### 4.4 Data Output Methods  

- `getCategoryData()` / `getTagData()` → returns `$this->validated()`.  
- Used directly in `::create()` and `->update()`.  

---

## 5. Policies  

### 5.1 `CategoryPolicy` & `TagPolicy`  

| Method    | Logic | Notes |
|-----------|-------|-------|
| `viewAny` | `$user->hasPermissionTo('manage categories')` / `'manage tags'` | |
| `view`    | `return true` | Public access; not used in admin |
| `create`  | `$user->hasPermissionTo('manage categories')` / `'manage tags'` | |
| `update`  | `$user->hasPermissionTo('manage categories')` / `'manage tags'` | |
| `delete`  | If Admin → `true`<br>Else if model has live posts → `false`<br>Else `$user->hasPermissionTo(...)` | The Admin check is redundant with `Gate::before` but acts as documentation |

- The global `Gate::before` (registered in `AuthServiceProvider`) returns `true` for any `Admin` user, bypassing all policy methods.  
- For any role other than Admin, the `viewAny`, `create`, `update`, and `delete` methods require the corresponding Spatie permission. The `delete` method additionally blocks deletion when live posts exist.  

**Important:** The Policy methods are currently only reached if the `role:Admin` middleware is removed.  

---

## 6. Models – Admin‑specific Aspects  

### 6.1 Category (`App\Models\Category`)  

- **Fillable:** `name`, `slug`, `description`  
- **Slug generation (Spatie):**  
  - `doNotGenerateSlugsOnUpdate()` – the model itself never changes the slug on update.  
  - The Form Request’s `prepareForValidation()` may override this.  
- **Relationship:** `posts()` – `belongsToMany(Post::class)`, with timestamps.  
- **Accessor `posts_count`:** returns count of only **live** posts (excludes soft‑deleted).  
  - Note: The admin index overrides this with a `withCount` that includes trashed posts.  

### 6.2 Tag (`App\Models\Tag`)  

- **Fillable:** `name`, `slug`  
- Identical slug and relationship configuration to Category (no `description`).  

---

## 7. Routes  

### 7.1 Admin Routes  

Both sets of routes are defined inside:  
```php
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('categories', CategoryAdminController::class)
            ->except(['show'])
            ->middleware('can:manage categories');

        Route::resource('tags', TagAdminController::class)
            ->except(['show'])
            ->middleware('can:manage tags');
    });
```

**Generated endpoints (identical for categories and tags):**  

| Method     | URI                          | Name                     | Controller Action |
|------------|------------------------------|--------------------------|-------------------|
| GET        | `/admin/categories`          | `admin.categories.index` | `index`           |
| GET        | `/admin/categories/create`   | `admin.categories.create`| `create`          |
| POST       | `/admin/categories`          | `admin.categories.store` | `store`           |
| GET        | `/admin/categories/{category}/edit` | `admin.categories.edit` | `edit`            |
| PUT/PATCH  | `/admin/categories/{category}` | `admin.categories.update`| `update`          |
| DELETE     | `/admin/categories/{category}` | `admin.categories.destroy`| `destroy`         |

(Tags replace `categories` with `tags` in the URI and route names.)  

**Route model binding:** Default `id` field. The public routes use `{category:slug}` / `{tag:slug}`.  

### 7.2 Middleware `can:manage categories/tags` – Ineffective  

- The `can` middleware checks for a gate ability named `manage categories` or `manage tags`. **No such gate is defined** in `AuthServiceProvider` or the Policies.  
- Because of the global `Gate::before`, Admin users pass automatically. If the `role:Admin` middleware were ever relaxed to allow Editors, this middleware would block them (gate undefined → `false`).  
- **Fix:** Either define the gate (e.g., `Gate::define('manage categories', [CategoryPolicy::class, 'viewAny'])`) or remove the middleware and rely on controller‑level authorization.  

---

## 8. Authorization Flow  

For every admin taxonomy request, the following chain is enforced:  

1. **`auth` middleware** – user must be authenticated.  
2. **`role:Admin` middleware** (`CheckRole`) – only users with Spatie role `Admin` proceed.  
3. **Global `Gate::before`** in `AuthServiceProvider::boot()`:  
   ```php
   Gate::before(fn($user, $ability) => $user->hasRole('Admin') ? true : null);
   ```  
   Admin users receive `true` for **every** ability, effectively bypassing all further policy checks.  
4. **Controller `Gate::authorize()` / Form Request `authorize()`** – these calls are still made, but return `true` instantly for admins because of step 3.  
5. **Hard guard in `destroy()`** – even after all authorizations pass, the controller explicitly checks `$model->posts()->exists()` and may refuse deletion. This is the only check that can stop an admin.  

If the `role:Admin` middleware is later adjusted to also allow Editors with the proper Spatie permission, steps 3–4 would become meaningful: the `Gate::before` would return `null` (no bypass), and the Policy methods would evaluate the `manage categories` / `manage tags` permission.  

---

## 9. Logging & Auditing  

Every write operation logs an `info` entry with structured context.  

| Event   | Message          | Context keys |
|---------|------------------|--------------|
| Create  | Category created / Tag created | `category_id` / `tag_id`, `name`, `created_by` |
| Update  | Category updated / Tag updated | `category_id` / `tag_id`, `old_name`, `new_name`, `updated_by` |
| Delete  | Category deleted / Tag deleted | `category_id` / `tag_id`, `name`, `deleted_by` |

**Important:** Logs are written inside `DB::transaction` blocks. If a transaction rolls back, the log entry **is not** rolled back. This can lead to false‑positive audit trails in rare failure scenarios.  

---

## 10. Known Issues & Constraints  

| Issue | Severity | Impact |
|-------|----------|--------|
| **Form Request `authorize()` uses undefined gate ability** | **High** | `POST` (store) actions depend entirely on the Admin bypass; non‑Admins cannot create even if they reach the controller. Fix: use `Gate::allows('create', Model::class)`. |
| **`can:manage categories/tags` middleware has no effect** | Medium | The gate is undefined; removal or proper definition required before allowing non‑Admin roles to manage taxonomies. |
| **Slug instability on update** | Medium | If the admin clears the slug field (even accidentally), a new slug is generated from the current name. This can break URLs/SEO. Mitigation: always pre‑fill the slug in the edit form. |
| **Admin delete guard only checks live posts** | Low | A taxonomy with only soft‑deleted posts can be deleted, which may or may not be the intended behaviour. |
| **Redundant slug generation** | Low | The Form Request `prepareForValidation()` duplicates the model‑level Spatie sluggable. Harmless but adds confusion. |
| **Logs persist after transaction rollback** | Low | Log entries may exist for operations that ultimately failed. |

---

## 11. Practical Workflows  

### 11.1 Creating a Category / Tag  

1. Admin navigates to the create form (`GET /admin/categories/create`).  
2. Fills in the name (required) and optionally a slug.  
3. Submits to `POST /admin/categories`.  
4. `CategoryRequest` validates; authorize passes because Admin bypass.  
5. Controller creates the model inside a transaction. The slug is set by Spatie if empty, or the provided one is used.  
6. Action is logged.  
7. Redirect to index with success message.  

### 11.2 Updating a Taxonomy  

1. Admin opens the edit form (`GET /admin/categories/{id}/edit`).  
2. Changes the name (and optionally the slug). **If slug field is emptied, a new slug will be generated.**  
3. Form posts to `PUT /admin/categories/{id}`; validation and authorization succeed.  
4. Model is updated; log records old/new name.  
5. Redirect with success message.  

### 11.3 Deleting a Taxonomy  

1. Admin triggers deletion (e.g., from the index page) → `DELETE /admin/categories/{id}`.  
2. The Policy `delete` check passes (admin bypass).  
3. **Controller hard check:** if the taxonomy has any non‑soft‑deleted posts, the action is blocked and an error flash message is shown.  
4. If no live posts exist, the taxonomy is permanently deleted (hard delete; pivot rows cascade).  
5. Action logged, success flash returned.  

---

## 12. Cross‑References to General Documentation  

This document builds on the base taxonomy modules:  

- For the database schema, model relationships, public filtering routes, and the overall slug/validation logic, refer to:  
  - [Categories Module](../categories.md)  
  - [Tags Module](../tags.md)  

- The authorization middleware (`CheckRole`) and the global `Gate::before` are described in the `Authorization & Middleware` document (to be created).  

- The general user management admin module follows the same pattern and is documented in [user-management.md](../../Admin/modules/user-management.md).  

---

## 13. Security & Audit  

- **Admin‑only access:** The `role:Admin` middleware ensures that only the highest‑privileged users can manage taxonomies.  
- **Logging:** Every destructive or mutating action is logged with the actor’s ID and relevant details.  
- **Transaction safety:** Database changes are atomic; failure at any point rolls back the entire operation (except logs).  
- **Deletion protection:** A double layer (Policy + controller check) prevents accidental removal of taxonomies still in use.  

---

> **Next steps:** After completing this document, the following related admin docs should be revisited :  
> - `Authorization & Middleware` (to document the `CanManage` gate and the `CheckRole` middleware in detail)  
> - `Content Management` (post admin operations)  
> - `Comment Moderation` (admin comment management)