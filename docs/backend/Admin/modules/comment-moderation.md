# Comment Moderation (Admin) — Documentation

> **Complement to:** [comments-system.md](../comments-system.md)    
> **Focus:** Administrative tools for approving, rejecting, deleting, and viewing all comments.  
> **Prerequisites:** Roles & permissions (see [user-management.md](../user-management.md)), overall admin structure (see [admin-dashboard.md](../admin-dashboard.md)).

---

## 1. Overview & Purpose

The admin comment moderation panel gives privileged users the ability to:

- View a **paginated list of all comments** (approved & pending).
- See only **pending comments** awaiting review.
- **Approve** a pending comment (making it publicly visible and **triggering the post‑author notification**).
- **Reject** an already approved comment (hiding it from public view).
- **Delete** a comment **and all its nested replies** permanently.
- Enforce role‑based access: only `Admin` has full moderation capabilities (middleware + policies).

All moderation operations are protected by Spatie’s permission system combined with Laravel’s Gate policies. The `Admin` role gets an **automatic bypass** in `AuthServiceProvider`, but the underlying policies are documented here for completeness and to support a possible future `Editor`‑moderation role.

---

## 2. Key Files (Admin‑Specific)

| File | Role |
|------|------|
| `app/Http/Controllers/Admin/CommentAdminController.php` | All moderation endpoints |
| `app/Policies/CommentPolicy.php` | `approve`, `approveAny`, `delete`, `viewAny` |
| `app/Models/Comment.php` | `approve()`, `reject()`, `allReplies()`, `getAllReplies()` |
| `routes/web.php` (admin group) | Route definitions under `prefix=admin`, `middleware=auth,role:Admin` |
| `app/Providers/AuthServiceProvider.php` | Admin bypass + policy registration |
| `app/Http/Middleware/CheckRole.php` | Role verification (applied via `role:Admin`) |
| `config/blog.php` | `comments_per_page`, notification toggle (indirect) |

*General comment files (model, migration, public controller, etc.) are covered in [comments-system.md](../comments-system.md).*

---

## 3. Database Schema (Relevant Fields)

The `comments` table is fully described in the general documentation. The admin panel primarily interacts with these columns:

| Column | Type | Usage in Moderation |
|--------|------|---------------------|
| `id` | bigint | Route model binding |
| `approved` | boolean (`0`/`1`) | `false` → pending list; `true` → publicly visible |
| `body` | text | Displayed in admin views |
| `user_id` | nullable FK | Author (null for guests) |
| `parent_id` | nullable FK (self‑ref) | Determines nesting; cascading delete |
| `commentable_id` + `commentable_type` | polymorphic | Link to the commented model (Post, etc.) |
| `guest_name` / `guest_email` | nullable | Shown when commenter is not logged in |
| `ip_address` | varchar(45) | Can be displayed to admin for abuse detection |

**Cascade rule:** `parent_id` has `ON DELETE CASCADE` → deleting a comment automatically deletes all descendants at the database level. The application code additionally deletes direct children using `allReplies()` to fire Eloquent events and logging.

---

## 4. Authorization & Roles

### 4.1 Route Middleware

All admin comment routes are grouped under:

```php
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () { ... });
```

- `auth`: ensures the user is logged in.
- `role:Admin`: uses `CheckRole` middleware; only users with the `Admin` role pass. (Editors are **excluded**.)

### 4.2 Admin Bypass

In `AuthServiceProvider::boot()`:

```php
Gate::before(function ($user, $ability) {
    return $user->hasRole('Admin') ? true : null;
});
```

This means any authorization check (`Gate::allows`, `$this->authorize`, `Gate::authorize`) **immediately returns `true`** for users with the `Admin` role, **without executing the policy method**. Therefore, the policy rules below are primarily relevant if you later extend moderation to the `Editor` role.

### 4.3 Comment Policy Methods (for Completeness)

**Policy:** `App\Policies\CommentPolicy`  
**Registered in:** `AuthServiceProvider::$policies` (must include `Comment::class => CommentPolicy::class`)

| Method | Logic | Used by |
|--------|-------|---------|
| `viewAny(User $user): bool` | `$user->hasPermissionTo('manage comments')` | `index()` |
| `approve(User $user, Comment $comment): bool` | `$user->hasPermissionTo('approve comments')` | `approve()`, `reject()` |
| `approveAny(User $user): bool` | `$user->hasPermissionTo('approve comments')` | `pending()` (class‑level) |
| `delete(User $user, Comment $comment): bool` | Admin role check + permission fallback (see policy code) | `destroy()` |

**Important:** The `pending()` method must use the class‑level `approveAny` gate, **not** the instance‑level `approve`. The corrected controller (see §5) uses `Gate::authorize('approveAny', Comment::class)`.

### 4.4 Required Permissions

Seed these Spatie permissions and assign to the `Admin` role:

- `manage comments` – view any comment (including unapproved)
- `approve comments` – approve / reject comments
- `delete comments` – delete comments

*The `Admin` role typically has **all** permissions, and the `Gate::before` bypass makes them redundant for Admin, but they are still necessary if the bypass is ever removed or other roles are added.*

---

## 5. Controller & Endpoints

**Class:** `App\Http\Controllers\Admin\CommentAdminController`  
All methods return a redirect back with a flash message (`success`/`info`/`error`).  
*The code blocks below show the **corrected final version** after applying all fixes from the general comments documentation.*

### 5.1 `index` — List All Comments

**Route:** `GET /admin/comments` → `admin.comments.index`  
**Gate:** `viewAny`  
**Pagination:** from `config('blog.comments_per_page', 20)`  
**Eager‑loading:** `commentable` and `author` to avoid N+1.

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

### 5.2 `pending` — Pending Comments Queue

**Route:** `GET /admin/comments/pending` → `admin.comments.pending`  
**Gate:** `approveAny` (class‑level, not instance)  
**Filter:** `where('approved', false)`

```php
public function pending()
{
    Gate::authorize('approveAny', Comment::class);   // Corrected from original code

    $comments = Comment::where('approved', false)
        ->with(['commentable', 'author'])
        ->latest()
        ->paginate(config('blog.comments_per_page', 20));

    return view('admin.comments.pending', compact('comments'));
}
```

### 5.3 `approve` — Approve a Comment

**Route:** `POST /admin/comments/{comment}/approve` → `admin.comments.approve`  
**Gate:** `approve` (instance)  
**Side effects:**

1. Sets `approved = true` via `$comment->approve()`.
2. **Fires `CommentPosted` event** — this is what triggers the post‑author notification.  
   ⚠️ The original code did **not** fire the event; the corrected version adds it.
3. Guards against double‑approval: if already approved, redirects with an info message.
4. Logs the action with `approved_by`.

```php
public function approve(Comment $comment)
{
    Gate::authorize('approve', $comment);

    if ($comment->approved) {
        return redirect()->back()->with('info', 'Comment is already approved.');
    }

    $comment->approve();
    event(new \App\Events\CommentPosted($comment));   // trigger notification

    Log::info('Comment approved', [
        'comment_id'   => $comment->id,
        'approved_by'  => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Comment approved!');
}
```

### 5.4 `reject` — Reject (Unapprove) a Comment

**Route:** `POST /admin/comments/{comment}/reject` → `admin.comments.reject`  
**Gate:** `approve` (instance)  
**Behavior:**

- Sets `approved = false` via `$comment->reject()`.
- Only allowed if comment is currently **approved**; otherwise redirects with info.
- Logs the action.

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

### 5.5 `destroy` — Delete Comment (Cascade)

**Route:** `DELETE /admin/comments/{comment}` → `admin.comments.destroy`  
**Gate:** `delete` (instance)  
**Cascade mechanism:**

1. Explicitly deletes **direct** children using the **unfiltered** `allReplies()` relationship. This ensures that Eloquent events fire and deletions are logged in the application layer.
2. Deletes the comment itself.
3. The database foreign key `parent_id` ON DELETE CASCADE then removes any deeper descendants automatically.

**Corrected code** (original used `replies()` which only includes approved children; must be `allReplies()`):

```php
use Illuminate\Support\Facades\DB;

public function destroy(Comment $comment)
{
    Gate::authorize('delete', $comment);

    DB::transaction(function () use ($comment) {
        // Delete direct children (unfiltered) to trigger Eloquent events
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

> **Note:** The `Comment` model’s `allReplies()` method returns a `HasMany` relationship **without any `where('approved', true)` filter**, unlike the public `replies()`. This is critical for complete deletion.

---

## 6. Form Request (Admin Side)

The `CommentRequest` form request is **not used** by the admin controller. Admin actions rely solely on route model binding and authorization through policies. No additional validation is performed on `POST`/`DELETE` requests beyond the authorization gates.

---

## 7. Events, Listeners & Notifications

The admin’s **approval** action is the only trigger for post‑author notifications from the moderation panel.

### 7.1 Flow

```
Admin clicks "Approve"
  → approve()
  → $comment->approve()   // sets approved = true
  → event(new CommentPosted($comment))
  → SendCommentNotification listener (queued)
  → NewCommentNotification (mail + database) sent to post author
```

### 7.2 Listener Conditions

The listener (`SendCommentNotification`) respects:

- `config('blog.comment_notifications')` must be `true`.
- Comment must be approved (it is, since we just approved it).
- The comment author must **not** be the post author (no self‑notification).
- The commentable model must have a valid `author`.

### 7.3 Notification Content

The mail notification includes a **direct link to manage comments** (`/admin/comments/pending`). This is defined in `NewCommentNotification`.

---

## 8. Routes

All admin comment routes are inside the `Admin`‑only group:

```php
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('comments', [CommentAdminController::class, 'index'])->name('comments.index');
        Route::get('comments/pending', [CommentAdminController::class, 'pending'])->name('comments.pending');
        Route::post('comments/{comment}/approve', [CommentAdminController::class, 'approve'])->name('comments.approve');
        Route::post('comments/{comment}/reject', [CommentAdminController::class, 'reject'])->name('comments.reject');
        Route::delete('comments/{comment}', [CommentAdminController::class, 'destroy'])->name('comments.destroy');
    });
```

**Route names** follow the pattern `admin.comments.*`.

---

## 9. Configuration Reference

The following keys in `config/blog.php` directly affect admin functionality:

| Key | Default | Description |
|-----|---------|-------------|
| `comments_per_page` | `20` | Number of comments per page in `index` and `pending` |
| `auto_approve_roles` | `['Admin','Editor']` | Roles whose comments are auto‑approved (not related to moderation, but affects the pending queue) |
| `comment_notifications` | `true` | Global toggle – if `false`, approving a comment will **not** send any notification (the listener returns early). |

---

## 10. Practical Scenarios

### 10.1 Approving a Pending Comment

1. Admin visits `/admin/comments/pending`.
2. Clicks “Approve” on a comment.
3. `POST /admin/comments/{id}/approve` is called.
4. Policy `approve` passes (Admin bypass or `approve comments` permission).
5. `$comment->approve()` sets `approved = 1`.
6. `CommentPosted` event is dispatched → post author receives email + database notification.
7. Admin is redirected back with a success message.

### 10.2 Rejecting an Approved Comment

1. Admin finds an approved comment (from `/admin/comments`).
2. Clicks “Reject”.
3. `POST /admin/comments/{id}/reject` is called.
4. `$comment->reject()` sets `approved = 0`.
5. No event fired; the comment is hidden from public view.

### 10.3 Deleting a Comment with Replies

Given a thread:

```
Comment A (to be deleted)
  └─ Reply B (approved)
       └─ Reply C (approved)
```

Action: Admin deletes Comment A.

1. `DELETE /admin/comments/A`.
2. `Gate::authorize('delete', A)` passes.
3. Inside transaction:
   - `$comment->allReplies()` returns Reply B (and any other direct children).
   - Reply B is deleted via Eloquent. C is **not** directly deleted by application code.
4. A is deleted.
5. Database cascade (`ON DELETE CASCADE` on `parent_id`) sees B is gone → C is automatically deleted.

Result: The entire sub‑thread is removed, all Eloquent events for A and B are triggered, and the log records the top‑level deletion.

---

## 11. Relationship with General Comments System

This document **complements** `comments-system.md`. To avoid duplication:

- The general document explains the **public comment workflows** (guest commenting, reply depth, auto‑approval, view policies).
- This document describes the **administrative interface** that sits on top of the same data.
- Cross‑references:
  - The `Comment` model and its relationships (`replies()`, `allReplies()`, `approve()`, etc.) are fully documented in the general doc.
  - The `CommentRequest` and public `CommentController` are **not** part of admin moderation.
  - The notification system is detailed in the general doc; here we only explain how admin approval triggers it.

**Rule of thumb:** If you need to understand how a comment’s data is stored, validated, or displayed publicly, see `comments-system.md`. If you need to know how an admin changes a comment’s status or deletes it, this document is the authority.

---

## 12. Common Pitfalls & Debugging

1. **`pending()` shows nothing despite unapproved comments existing**  
   → Check that `approved` column has index and the query uses `where('approved', false)`. Also verify pagination: page number may be beyond available records.

2. **Approval doesn’t send notification**  
   - Ensure `config('blog.comment_notifications')` is `true`.
   - Make sure the event `CommentPosted` is actually fired in the `approve()` method (the original code missed this).
   - The listener is queued; if queue is not running, notifications won’t be dispatched. Use `QUEUE_CONNECTION=sync` for testing.
   - Check logs for `Skipping notification` messages.

3. **Deleting a comment leaves orphan replies**  
   → The original `destroy()` used `$comment->replies()->delete()` which only deletes **approved** children. Unapproved children would be left behind. The fix uses `allReplies()->delete()`.

4. **Access denied (403) on admin routes**  
   - Confirm the user has the `Admin` role.
   - The role middleware uses `CheckRole` with `role:Admin`; ensure the role name case‑sensitivity matches.
   - The `AuthServiceProvider` must register `CommentPolicy` and the `Gate::before` bypass.

5. **Duplicate index error during migration**  
   - The migration `create_comments_table` may contain a duplicate `index()` after `morphs()`. Remove the redundant line (see general doc §14).

---

## 13. Required Fixes from Original Codebase

If the current admin controller and policy do not match the documented behavior, apply the following changes (extracted from `comments-system.md` §12 for admin‑specific parts):

| # | File | Change |
|---|------|--------|
| 1 | `CommentAdminController@pending` | Change `Gate::authorize('approve', Comment::class)` to `Gate::authorize('approveAny', Comment::class)` |
| 2 | `CommentAdminController@approve` | Add guard `if ($comment->approved) return ...` and fire `event(new CommentPosted($comment))` after approval |
| 3 | `CommentAdminController@reject` | Add guard `if (!$comment->approved) return ...` |
| 4 | `CommentAdminController@destroy` | Use `$comment->allReplies()->delete()` instead of `$comment->replies()->delete()` and wrap in `DB::transaction` |
| 5 | `CommentAdminController@index`, `@pending` | Replace hardcoded `paginate(20)` with `paginate(config('blog.comments_per_page', 20))` |
| 6 | `CommentPolicy` | Add `approveAny(User $user): bool` (if missing) |
| 7 | `AuthServiceProvider` | Register `Comment::class => CommentPolicy::class` in `$policies` |

---

## 14. Example Blade Variables

The admin views receive a `$comments` paginator instance. Each comment object includes:

- `$comment->id`, `->body`, `->approved`, `->created_at`
- `$comment->author` (User or null) → `$comment->display_name`
- `$comment->commentable` (Post, etc.) → `$comment->commentable->title`, `->url`
- `$comment->isReply()`, `$comment->parent`, etc. (if needed)

Use `$comments->links()` for pagination.

---

**Depends on:** [comments-system.md](../comments-system.md), [user-management.md](../user-management.md), `config/blog.php`