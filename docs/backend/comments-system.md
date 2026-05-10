## Comments Module — Documentation

> **Coverage:** Migrations, models, request validation, controllers, policies, events, listeners, notifications, routes, configuration  
> **Excluded:** Blade templates & front‑end JavaScript (documented separately)  
> **Front‑end note:** The current Blade views render comments in a **flat thread** style; however, the backend fully supports nested replies via `parent_id`. All server‑side rules described below remain valid and must be respected by any future nested UI.

---

## Table of Contents

1. [Overview & Architecture](#1-overview--architecture)  
2. [Installation & Setup Checklist](#2-installation--setup-checklist)  
3. [File Manifest](#3-file-manifest)  
4. [Database Schema](#4-database-schema)  
5. [Models & Relationships](#5-models--relationships)  
6. [Request Validation](#6-request-validation)  
7. [Controllers & Endpoints](#7-controllers--endpoints)  
8. [Policies & Authorization](#8-policies--authorization)  
9. [Events, Listeners & Notifications](#9-events-listeners--notifications)  
10. [Routes](#10-routes)  
11. [Configuration Reference](#11-configuration-reference)  
12. [Required Code Fixes (from provided codebase to final version)](#12-required-code-fixes-from-provided-codebase-to-final-version)  
13. [Frontend Integration Notes](#13-frontend-integration-notes)  
14. [Migration & Provider Corrections](#14-migration--provider-corrections)  
15. [Performance Considerations](#15-performance-considerations)  

---

## 1. Overview & Architecture

A polymorphic, nested commenting system. By default comments attach to the `Post` model, but any Eloquent model can become **commentable**.  
All **security, depth limits, approval and notification logic is enforced server‑side** – the front end only needs to hide the reply button when depth is exhausted.

| Rule | Final behaviour |
|------|----------------|
| **Guest comments** | Allowed only when `config('blog.allow_guest_comments') === true` **and** the store route is public (not inside `auth` middleware). |
| **Auto‑approval** | Authenticated users with at least one role listed in `config('blog.auto_approve_roles')` have their comments instantly approved. |
| **Moderation queue** | All other comments (guests, regular authenticated users) are saved with `approved = false`. |
| **Banned users** | Blocked from posting – checked in `CommentPolicy::create()`. |
| **Reply depth** | Maximum level enforced server‑side using `config('blog.max_comment_nesting_depth')`. A new reply to a comment at maximum depth is rejected. |
| **Notifications** | Fired **only when a comment becomes published** – either instantly for auto‑approved comments, or when an admin manually approves. No duplicate firing. |
| **Global notification toggle** | Listener respects `config('blog.comment_notifications')`. If `false`, no notification is dispatched. |
| **Comment deletion** | Hard delete; due to the `ON DELETE CASCADE` foreign key on `parent_id`, deleting a comment automatically removes **all** its descendants at the database level. The application code explicitly deletes only direct children using `allReplies()` to trigger any Eloquent events and log the action, but the cascade guarantees complete removal of the tree. |
| **Admin pagination** | Uses `config('blog.comments_per_page')` for index and pending lists. |

**Nested structure**  
Each comment stores a `parent_id` pointing to the parent comment (self‑referencing). The public `replies()` relationship returns **only approved children** and **does not** eager‑load deeper levels automatically – you must use the `withNestedReplies($depth)` scope to build a tree. The `allReplies()` relationship returns **every child** (approved + unapproved) and is used for admin hard‑deletion.

---

## 2. Installation & Setup Checklist

Perform the steps **in order** after pulling the code.

1. **Apply database migrations**  
   Make sure the corrected migration files (see [§14](#14-migration--provider-corrections)) exist.  
   ```bash
   php artisan migrate
   ```
2. **Create/update configuration file** `config/blog.php`  
   Merge or create the file with the content shown in [§11](#11-configuration-reference).  
   If the file already exists, add the missing comment keys.
3. **Register Comment policy**  
   In `app/Providers/AuthServiceProvider.php` add:
   ```php
   use App\Models\Comment;
   use App\Policies\CommentPolicy;

   protected $policies = [
       // ... existing
       Comment::class => CommentPolicy::class,
   ];
   ```
4. **Fix `EventServiceProvider`**  
   See [§14](#14-migration--provider-corrections) for the correct parent class and content.
5. **Seed Spatie permissions**  
   The following permissions are used by the policy. Seed them and assign to roles:
   - `create comments`
   - `approve comments`
   - `delete comments`
   - `manage comments` (allows viewing all comments, editing any)

   Recommended assignment:
   - **Admin** → all permissions
   - **Editor** → `manage comments`, `approve comments`, `delete comments`
6. **Adjust routes**  
   Ensure the comment store is **outside** the `auth` middleware group, the reply route is **inside** `auth`, and admin routes are **inside** `auth` + `role:Admin` (see [§10](#10-routes)).
7. **Add `url` accessor to commentable models**  
   The notification uses `$comment->commentable->url`. Every model that accepts comments **must** expose a `getUrlAttribute()` accessor.  
   Example for `Post`:
   ```php
   // App\Models\Post
   public function getUrlAttribute(): string
   {
       return route('posts.show', $this->slug);
   }
   ```
8. **Apply all code fixes** listed in [§12](#12-required-code-fixes-from-provided-codebase-to-final-version).  
   *These correct security holes, bugs, and align the implementation with this documentation.*
9. **Queue worker (production)**  
   The notification listener is queued. Set `QUEUE_CONNECTION` in `.env` and run:
   ```bash
   php artisan queue:work
   ```
   For local development `QUEUE_CONNECTION=sync` works without a worker.

---

## 3. File Manifest

| File | Purpose |
|------|---------|
| `database/migrations/..._create_comments_table.php` | Core table with indexes and foreign keys |
| `database/migrations/..._add_allow_comments_to_posts_table.php` | Adds `allow_comments` flag to `posts` |
| `database/migrations/..._add_ban_fields_to_users_table.php` | Adds `banned_at`, `banned_by`, `ban_reason` and `softDeletes` to `users` |
| `app/Models/Comment.php` | Model, relationships, scopes, accessors, helpers |
| `app/Models/User.php` (comment‑relevant parts) | Ban checks, role usage |
| `app/Http/Requests/CommentRequest.php` | Validation, data assembly, auto‑approval logic |
| `app/Http/Controllers/CommentController.php` | Public store & reply |
| `app/Http/Controllers/Admin/CommentAdminController.php` | Admin moderation (pending, approve, reject, delete) |
| `app/Policies/CommentPolicy.php` | Authorization for all actions |
| `app/Events/CommentPosted.php` | Event fired when comment becomes published |
| `app/Listeners/SendCommentNotification.php` | Queued listener for notifications |
| `app/Notifications/NewCommentNotification.php` | Mail + database notification |
| `routes/web.php` (comment‑related segments) | Route definitions |
| `config/blog.php` | Module configuration |
| `app/Http/Middleware/CheckRole.php` | Role middleware (used by `role:Admin` routes) |
| `app/Providers/EventServiceProvider.php` | Event‑listener registration (must be corrected) |
| `app/Providers/AuthServiceProvider.php` | Policy registration + Admin gate bypass |

---

## 4. Database Schema

### `comments` table

*(after removing the duplicate index – see §14)*

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | bigint unsigned | no | auto‑increment | |
| `user_id` | bigint unsigned | yes | NULL | FK → `users.id`, ON DELETE SET NULL |
| `commentable_id` | bigint unsigned | no | – | Polymorphic ID |
| `commentable_type` | varchar | no | – | FQCN, e.g. `App\Models\Post` |
| `parent_id` | bigint unsigned | yes | NULL | FK → `comments.id`, ON DELETE CASCADE |
| `body` | text | no | – | |
| `approved` | boolean | no | `false` | `0` = pending, `1` = published |
| `guest_name` | varchar(255) | yes | NULL | Required for guests |
| `guest_email` | varchar(255) | yes | NULL | Required for guests |
| `ip_address` | varchar(45) | yes | NULL | |
| `user_agent` | text | yes | NULL | |
| `created_at` | timestamp | no | – | |
| `updated_at` | timestamp | no | – | |

**Indexes**
- `comments_parent_id_index` (`parent_id`)
- `comments_ip_address_index` (`ip_address`)
- `comments_commentable_id_commentable_type_index` (compound, created by `$table->morphs('commentable')`)
- `comments_approved_index` (`approved`)
- `comments_approved_created_at_index` (`approved`, `created_at`)
- `comments_parent_id_approved_index` (`parent_id`, `approved`)

**Foreign keys**
- `parent_id` → `comments.id` ON DELETE CASCADE
- `user_id` → `users.id` ON DELETE SET NULL

### `posts` addition

| Column | Type | Default |
|--------|------|---------|
| `allow_comments` | boolean | `true` |

*This column is NOT NULL; every post has an explicit toggle.*

### `users` ban fields

| Column | Type | Nullable |
|--------|------|----------|
| `banned_at` | timestamp | yes |
| `banned_by` | bigint unsigned | yes |
| `ban_reason` | text | yes |

*`softDeletes` is also added via the migration.*

---

## 5. Models & Relationships

### Comment Model (`App\Models\Comment`)

**Traits:** `HasFactory` (no SoftDeletes – deletions are hard).  
**Fillable:** `user_id`, `commentable_id`, `commentable_type`, `parent_id`, `body`, `approved`, `guest_name`, `guest_email`, `ip_address`, `user_agent`.  
**Casts:** `'approved' => 'boolean'`.

#### Relationships

| Method | Return | Description |
|--------|--------|-------------|
| `commentable()` | MorphTo | Parent model (e.g. Post) |
| `author()` | BelongsTo(User) | Registered commenter; `null` for guests |
| `parent()` | BelongsTo(Comment) | Immediate parent comment |
| `replies()` | HasMany | **Only approved** child comments. **Does NOT eager‑load deeper levels** (see below). |
| `allReplies()` | HasMany | **All children** (approved + unapproved) – used exclusively for admin deletion cascades |

```php
// FINAL CORRECTED RELATIONSHIPS
public function replies(): HasMany
{
    return $this->hasMany(Comment::class, 'parent_id')
                ->where('approved', true)
                ->latest();
}

public function allReplies(): HasMany
{
    return $this->hasMany(Comment::class, 'parent_id');
}
```

> **Important:** The previous `replies()` included `->with('replies')` which caused uncontrolled recursive eager loading. **That line has been removed.** Now you must explicitly use the `withNestedReplies($depth)` scope to load a comment tree.

#### Scopes

```php
public function scopeApproved($query)
{
    return $query->where('approved', true);
}

public function scopeTopLevel($query)
{
    return $query->whereNull('parent_id');
}

/**
 * Eager‑load nested approved replies up to a given depth.
 * Default depth is taken from config('blog.max_comment_nesting_depth').
 *
 * @param int|null $depth Maximum levels to load (0 = no children).
 */
public function scopeWithNestedReplies($query, ?int $depth = null)
{
    $depth = $depth ?? config('blog.max_comment_nesting_depth', 3);
    if ($depth <= 0) {
        return $query;
    }
    return $query->with(['replies' => function ($q) use ($depth) {
        $q->withNestedReplies($depth - 1);
    }]);
}
```

#### Accessors

```php
public function getDisplayNameAttribute(): string
{
    return $this->author?->name ?? $this->guest_name ?? 'Anonymous';
}

public function getAvatarUrlAttribute(): string
{
    return $this->author?->avatar_url ?? asset('images/default-guest-avatar.png');
}
```

#### Helper Methods

```php
public function approve(): void
{
    $this->update(['approved' => true]);
}

public function reject(): void
{
    $this->update(['approved' => false]);
}

public function isReply(): bool
{
    return $this->parent_id !== null;
}

public function canBeModeratedBy(User $user): bool
{
    return $user->hasPermissionTo('approve comments');
}

/**
 * Count ancestors of this comment (depth).
 * Depth 0 = top‑level comment.
 *
 * @warning This method uses lazy loading; see §15 Performance Considerations.
 */
public function countAncestors(): int
{
    $count = 0;
    $parent = $this->parent;
    while ($parent) {
        $count++;
        $parent = $parent->parent;
    }
    return $count;
}

/**
 * Collect all descendants recursively (approved + unapproved).
 * Used by admin to ensure complete deletion (together with DB cascade).
 */
public function getAllReplies(): \Illuminate\Support\Collection
{
    $all = collect();
    foreach ($this->allReplies as $reply) {
        $all->push($reply);
        $all = $all->merge($reply->getAllReplies());
    }
    return $all;
}
```

### User Model (comment‑relevant excerpts)

```php
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, SoftDeletes;

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
}
```

### Required `url` Accessor on Commentable Models

The notification class accesses `$comment->commentable->url`. **Every model that can be commented must provide this attribute.**  
Example for `Post`:

```php
// App\Models\Post
public function getUrlAttribute(): string
{
    return route('posts.show', $this->slug);
}
```

---

## 6. Request Validation

**Class:** `App\Http\Requests\CommentRequest`

### Authorization

| Request type | Logic |
|--------------|-------|
| `POST` (guest) | `config('blog.allow_guest_comments') === true` |
| `POST` (authenticated) | `true` (further checks in Policy & Controller) |
| `PUT/PATCH` | `Gate::allows('update', $comment)` |
| `DELETE` | `Gate::allows('delete', $comment)` |

### Validation Rules

- `body`: required, string, min:10, max:1000
- `parent_id`: nullable, exists:comments,id; **prohibited** if `commentable_id` is present (prevents nesting injection)
- Guest users (`!auth()->check()`):
  - `guest_name`: required, string, min:2, max:50
  - `guest_email`: required, email, max:255

### `getCommentData()` — Final version

```php
public function getCommentData(): array
{
    $data = $this->validated();
    $data['ip_address'] = $this->ip();
    $data['user_agent'] = $this->userAgent();
    $data['user_id']    = auth()->id();

    // Auto‑approval based on config roles
    $data['approved'] = false;
    if (auth()->check() && auth()->user()->hasRole(config('blog.auto_approve_roles', ['Admin', 'Editor']))) {
        $data['approved'] = true;
    }

    return $data;
}
```

---

## 7. Controllers & Endpoints

### 7.1 Public `CommentController`

All methods redirect back with a flash message. Authorization, depth checks, and post‑comment‑disabled checks are performed here.

#### `store` — POST `/posts/{post}/comments` (public)

```php
public function store(CommentRequest $request, Post $post)
{
    // Check if comments are disabled on the post
    if (($post->allow_comments ?? true) === false) {
        return redirect()->back()->with('error', 'Comments are disabled for this post.');
    }

    // Policy: create (checks guest permission & banned users)
    $this->authorize('create', Comment::class);

    $comment = DB::transaction(function () use ($request, $post) {
        $data = $request->getCommentData();
        $data['commentable_id']   = $post->id;
        $data['commentable_type'] = Post::class;

        $comment = Comment::create($data);

        if ($comment->approved) {
            event(new CommentPosted($comment));
        } else {
            Log::info('Comment pending moderation', ['comment_id' => $comment->id]);
        }

        return $comment;
    });

    $message = $comment->approved
        ? 'Comment posted successfully!'
        : 'Comment submitted and is pending moderation.';

    return redirect()->back()->with('success', $message);
}
```

#### `reply` — POST `/comments/{comment}/reply` (authenticated)

```php
public function reply(CommentRequest $request, Comment $comment)
{
    $this->authorize('create', Comment::class); // guest reply not allowed here

    if (!$comment->approved) {
        return redirect()->back()->with('error', 'Cannot reply to an unapproved comment.');
    }

    // Depth enforcement
    $maxDepth = config('blog.max_comment_nesting_depth', 3);
    if (($comment->countAncestors() + 1) > $maxDepth) {
        return redirect()->back()->with('error', 'Maximum reply depth reached.');
    }

    $reply = DB::transaction(function () use ($request, $comment) {
        $data = $request->getCommentData();
        $data['commentable_id']   = $comment->commentable_id;
        $data['commentable_type'] = $comment->commentable_type;
        $data['parent_id']        = $comment->id;

        $reply = Comment::create($data);

        if ($reply->approved) {
            event(new CommentPosted($reply));
        }

        return $reply;
    });

    $message = $reply->approved
        ? 'Reply posted successfully!'
        : 'Reply submitted and is pending moderation.';

    return redirect()->back()->with('success', $message);
}
```

*Other methods (`approve`, `reject`, `destroy`) exist but are not used by public routes; admin operations are handled exclusively by `CommentAdminController`.*

### 7.2 Admin `CommentAdminController`

Access is enforced by the `auth` + `role:Admin` middleware on routes.

#### `index` — GET `/admin/comments`

```php
public function index()
{
    Gate::authorize('viewAny', Comment::class);

    $comments = Comment::with(['commentable', 'author'])
        ->latest()
        ->paginate(config('blog.comments_per_page', 20));

    return view('admin.comments.index', compact('comments'));
}
```

#### `pending` — GET `/admin/comments/pending`

```php
public function pending()
{
    // Class‑level authorization to view pending list
    Gate::authorize('approveAny', Comment::class);

    $comments = Comment::where('approved', false)
        ->with(['commentable', 'author'])
        ->latest()
        ->paginate(config('blog.comments_per_page', 20));

    return view('admin.comments.pending', compact('comments'));
}
```

#### `approve` — POST `/admin/comments/{comment}/approve`

*Fires the `CommentPosted` event only when truly approving a previously pending comment.*

```php
public function approve(Comment $comment)
{
    Gate::authorize('approve', $comment);

    if ($comment->approved) {
        return redirect()->back()->with('info', 'Comment is already approved.');
    }

    $comment->approve();
    event(new \App\Events\CommentPosted($comment));

    Log::info('Comment approved', [
        'comment_id'   => $comment->id,
        'approved_by'  => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Comment approved!');
}
```

#### `reject` — POST `/admin/comments/{comment}/reject`

```php
public function reject(Comment $comment)
{
    Gate::authorize('approve', $comment);

    if (!$comment->approved) {
        return redirect()->back()->with('info', 'Comment is already rejected.');
    }

    $comment->reject();

    Log::info('Comment rejected', [
        'comment_id'  => $comment->id,
        'rejected_by' => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Comment rejected and hidden.');
}
```

#### `destroy` — DELETE `/admin/comments/{comment}`

*Deletes the comment and all its descendants thanks to the database foreign key cascade. The code explicitly removes direct children via `allReplies()` to trigger any Eloquent events and logs.*

```php
public function destroy(Comment $comment)
{
    Gate::authorize('delete', $comment);

    DB::transaction(function () use ($comment) {
        // Delete direct children; database cascade will then eliminate deeper levels
        $comment->allReplies()->delete();
        $comment->delete();

        Log::info('Comment deleted (with all descendants)', [
            'comment_id' => $comment->id,
            'deleted_by' => auth()->id(),
        ]);
    });

    return redirect()->back()->with('success', 'Comment deleted!');
}
```

---

## 8. Policies & Authorization

**Policy:** `App\Policies\CommentPolicy`  
Registered in `AuthServiceProvider::$policies`.

### Global Admin bypass (in `AuthServiceProvider::boot()`)

```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Admin') ? true : null;
});
```

This bypass means that any `Gate::allows()` or `$this->authorize()` will immediately return `true` for users with the `Admin` role, before the policy methods are even called.

### Policy methods (final, complete)

```php
public function viewAny(User $user): bool
{
    return $user->hasPermissionTo('manage comments');
}

public function view(User $user, Comment $comment): bool
{
    if ($comment->approved) return true;
    return $user->hasPermissionTo('approve comments') || $user->id === $comment->user_id;
}

public function create(?User $user = null): bool
{
    if (!$user) {
        return config('blog.allow_guest_comments', true);
    }
    if ($user->isBanned()) {
        return false;
    }
    return $user->hasPermissionTo('create comments') ?? true;
}

public function update(User $user, Comment $comment): bool
{
    // Author can edit within 15 minutes
    if ($user->id === $comment->user_id && $comment->created_at->diffInMinutes(now()) < 15) {
        return true;
    }
    return $user->hasPermissionTo('manage comments');
}

public function delete(User $user, Comment $comment): bool
{
    if ($user->hasRole('Admin')) return true;
    if ($user->hasRole('Editor') && !$comment->author?->hasRole('Admin')) {
        return $user->hasPermissionTo('delete comments');
    }
    return $user->id === $comment->user_id && $user->hasPermissionTo('delete comments');
}

/**
 * Instance‑level approval – used by approve/reject actions.
 */
public function approve(User $user, Comment $comment): bool
{
    return $user->hasPermissionTo('approve comments');
}

/**
 * Class‑level approval – used for listing pending comments.
 */
public function approveAny(User $user): bool
{
    return $user->hasPermissionTo('approve comments');
}
```

---

## 9. Events, Listeners & Notifications

### `CommentPosted` Event

- Holds the `Comment` model.
- Constructor eager‑loads `author` and `commentable.author` to avoid N+1 issues in the listener.

### `SendCommentNotification` Listener

- Implements `ShouldQueue`.  
- The `shouldQueue()` method ensures the job is only queued when the comment is approved and the commentable has an author; otherwise the listener is not queued (reducing resources).  
- Respects the global config toggle `config('blog.comment_notifications')`.  
- Skips notification if the comment author is the same as the post author.

**Final corrected listener:**

```php
class SendCommentNotification implements ShouldQueue
{
    public function handle(CommentPosted $event): void
    {
        if (!config('blog.comment_notifications', true)) {
            return;
        }

        $comment = $event->comment;
        $post = $comment->commentable;

        if (!$post || !$post->author) {
            return;
        }
        if ($comment->user_id === $post->user_id) {
            Log::info('Skipping notification - comment author is post author', [
                'comment_id' => $comment->id,
                'post_id'    => $post->id,
            ]);
            return;
        }

        $post->author->notify(new NewCommentNotification($comment));

        Log::info('Comment notification sent', [
            'comment_id'      => $comment->id,
            'post_id'         => $post->id,
            'notified_user_id'=> $post->user_id,
        ]);
    }

    /**
     * Only queue the job when the comment is approved and the post has an author.
     */
    public function shouldQueue(CommentPosted $event): bool
    {
        return $event->comment->approved && $event->comment->commentable->author;
    }
}
```

### `NewCommentNotification`

- Channels: `mail`, `database`.
- Uses `$comment->display_name`, `$comment->commentable->title`, `$comment->commentable->url`.
- Mail action buttons link to the comment permalink and to admin comment management.

### Notification Flow Summary

| Scenario | Notification? |
|----------|---------------|
| Auto‑approved comment (Admin/Editor) | ✅ Yes (fired immediately in `store`/`reply`) |
| Pending comment (guest/regular user) | ❌ No |
| Admin manually approves a pending comment | ✅ Yes (fired once in `approve()`) |
| Second approval attempt on already approved comment | ❌ No (guard in controller) |

---

## 10. Routes

Final comment‑related routes extracted from `routes/web.php`.  
**Critical:** The store route is public, reply is authenticated, admin routes require `auth` + `role:Admin`.

```php
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Admin\CommentAdminController;

// Public comment store (allows guests)
Route::resource('posts.comments', CommentController::class)->only(['store']);

// Authenticated reply
Route::middleware('auth')->group(function () {
    Route::post('comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
});

// Admin‑only comment management (inside an existing admin prefix block)
Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('comments', [CommentAdminController::class, 'index'])->name('comments.index');
    Route::get('comments/pending', [CommentAdminController::class, 'pending'])->name('comments.pending');
    Route::post('comments/{comment}/approve', [CommentAdminController::class, 'approve'])->name('comments.approve');
    Route::post('comments/{comment}/reject', [CommentAdminController::class, 'reject'])->name('comments.reject');
    Route::delete('comments/{comment}', [CommentAdminController::class, 'destroy'])->name('comments.destroy');
});
```

*The `role:Admin` middleware resolves to `App\Http\Middleware\CheckRole` and verifies the user has the `Admin` role.*

---

## 11. Configuration Reference (`config/blog.php`)

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Blog / Comments Settings
    |--------------------------------------------------------------------------
    */

    // Allow guests (unauthenticated users) to comment
    'allow_guest_comments'       => true,

    // Roles whose comments are automatically approved
    'auto_approve_roles'         => ['Admin', 'Editor'],

    // Maximum nesting depth (0 = no replies, 3 = replies to replies to replies)
    'max_comment_nesting_depth'  => 3,

    // Pagination for admin comment lists
    'comments_per_page'          => 20,

    // Master switch for comment notifications (emails & database)
    'comment_notifications'      => true,
];
```

---

## 12. Required Code Fixes (from provided codebase to final version)

Apply these changes to the original codebase. The sections above already show the corrected versions; use this list as a punchlist.

| # | File | Change description |
|---|------|---------------------|
| 1 | `routes/web.php` | Move `Route::resource('posts.comments', ...)->only(['store']);` **outside** any `auth` middleware group. |
| 2 | `CommentController@store` | Add `$this->authorize('create', Comment::class);`. Change post‑disabled check to `($post->allow_comments ?? true) === false`. |
| 3 | `CommentController@reply` | Add `$this->authorize('create', Comment::class);` and depth limit check using `($comment->countAncestors() + 1) > $maxDepth`. |
| 4 | `CommentRequest@getCommentData` | Replace hard‑coded roles `['Admin','Editor']` with `config('blog.auto_approve_roles', ['Admin','Editor'])`. |
| 5 | `CommentPolicy@create` | Add banned user check: `if ($user->isBanned()) return false;`. |
| 6 | `CommentAdminController@index` and `@pending` | Replace `paginate(20)` with `paginate(config('blog.comments_per_page', 20))`. |
| 7 | `CommentAdminController@approve` | Add guard against double approval and fire `event(new CommentPosted($comment))` after approval. |
| 8 | `CommentAdminController@destroy` | Replace `$comment->replies()->delete()` with `$comment->allReplies()->delete()` (use unfiltered relationship). The deeper cascade is handled by the database foreign key. |
| 9 | `Comment` model | **Remove `->with('replies')` from `replies()` relationship.** Add `allReplies()`, `countAncestors()`, and modify `withNestedReplies` to accept an optional `$depth` parameter defaulting to `config('blog.max_comment_nesting_depth')`. |
| 10 | `CommentPolicy` | Add `approve(User $user, Comment $comment)` for instance‑level and `approveAny(User $user)` for class‑level; update `CommentAdminController@pending` to call `Gate::authorize('approveAny', Comment::class)`. |
| 11 | `SendCommentNotification@handle` | Add `if (!config('blog.comment_notifications', true)) return;` at the start. Keep the `shouldQueue()` method to avoid unnecessary queue entries. |
| 12 | Commentable models (e.g. `Post`) | Add `getUrlAttribute()` accessor. |
| 13 | Migration `create_comments_table` | Remove the duplicate `$table->index(['commentable_id','commentable_type']);` line. |
| 14 | `EventServiceProvider` | Change parent class to `Illuminate\Foundation\Support\Providers\EventServiceProvider` and keep `$listen` array. |
| 15 | `AuthServiceProvider` | Register `Comment::class => CommentPolicy::class` in `$policies`. |
| 16 | `User` model | Ensure `isBanned()` and `bannedBy()` exist (already present in the provided code). |

---

## 13. Frontend Integration Notes

While the Blade templates are documented separately, keep these backend‑driven rules in mind:

- **Flat thread rendering**  
  The backend returns the full comment tree, but your views may flatten it for display. To fetch approved top‑level comments with nested replies eager‑loaded (for a nested UI, or to manually flatten) use:
  ```php
  Comment::where('commentable_type', Post::class)
         ->where('commentable_id', $post->id)
         ->topLevel()
         ->approved()
         ->withNestedReplies(config('blog.max_comment_nesting_depth'))
         ->latest()
         ->get();
  ```
  For the current flat display, you may still use this to retrieve the entire tree and then flatten it in the Blade layer.

- **Depth limit in the UI**  
  Use `$comment->countAncestors()` to disable the reply button when `$comment->countAncestors() + 1 > config('blog.max_comment_nesting_depth')`.

- **Guest form fields**  
  Show `guest_name` and `guest_email` fields only when the user is not authenticated.

- **Admin views**  
  Use the paginated collections provided by the admin controller (`$comments`). Each comment object includes `author`, `commentable`, and the `display_name` accessor.

- **Comment permalink**  
  The permalink format is `{commentable_url}#comment-{id}` – ensure the frontend adds an anchor for each comment if you switch to a nested view later.

---

## 14. Migration & Provider Corrections

### Migration Duplicate Index

The original migration has:
```php
$table->morphs('commentable'); // creates commentable_id, commentable_type + compound index
$table->index(['commentable_id', 'commentable_type']); // DUPLICATE – remove this line
```
**Action:** Delete the manual `->index(...)` line immediately after `morphs()`.

### `EventServiceProvider` Inheritance

The file must extend the Laravel framework’s base event provider, not a generic `ServiceProvider`.  

**Correct file:**

```php
<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\CommentPosted;
use App\Listeners\SendCommentNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CommentPosted::class => [
            SendCommentNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
```

---

## 15. Performance Considerations

### Depth Calculation (`countAncestors()`)

The current implementation of `countAncestors()` traverses the parent chain using lazy loading. Each call executes one SQL query per ancestor. In a heavily nested thread (e.g., depth 5), this can lead to multiple extra queries.

**Recommendation for production:** Add an integer `depth` column to the `comments` table, maintained automatically when a comment is created:

```php
// When creating a reply:
$data['depth'] = $comment->depth + 1;
```

Then replace `$comment->countAncestors()` with `$comment->depth` for O(1) depth checks. This eliminates the lazy‑loading overhead entirely.

If you cannot alter the schema immediately, at least eager‑load the parent chain when expecting many depth checks:

```php
$comment->load('parent.parent'); // up to max depth
// then iterate with ->parent without additional queries
```
---
