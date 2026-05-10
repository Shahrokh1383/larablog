# Admin Dashboard Widgets — Documentation

> **Coverage:** Admin dashboard page (`/admin`), including statistics overview, recent activity lists, and chart data.  
> **Excluded:** User‑facing dashboard (`/dashboard`), profile editing, and front‑end template details.

---

## 1. Overview & Purpose

The admin dashboard provides a centralised overview of the blog platform’s health and activity. It is intended exclusively for users with the **Admin** role. The dashboard is not available to Editors or any other role—both middleware and a dedicated Spatie permission gate double‑lock the route.

**Key information displayed:**
- **Summary counts:** total/authorship‑broken‑down posts, comments (approved/pending), users, categories, tags.
- **Recent activity:** five most recent posts (with author), ten most recent comments (with parent content and author), top five authors by published post count.
- **Charts:** monthly posts and comments for the last six months, suitable for line or bar chart rendering.

The data is computed **live on each request**—there is no caching layer. All queries run inside a single controller method and are handed to a Blade view (not covered here).

---

## 2. File Manifest

| File | Role |
|------|------|
| `app/Http/Controllers/Admin/DashboardController.php` | Builds statistics and chart data for the admin dashboard view. |
| `routes/web.php` (admin dashboard route) | Defines `GET /admin` guarded by `auth` and `role:Admin`. |
| `app/Http/Middleware/CheckRole.php` | Role‑based middleware that rejects requests if the authenticated user lacks the `Admin` role. |
| `config/auth.php` | Default guard and provider; no custom dashboard‑specific settings. |
| `app/Models/Post.php` | Provides `published()`, `draft()`, and relationship to `User` (for `author`). |
| `app/Models/Comment.php` | `approved` column (boolean) used for pending count; relationships to `commentable` and `author`. |
| `app/Models/User.php` | Provides relationship `posts()`; used for `top_authors` via `withCount`. |
| `app/Models/Category.php` | Simple count. |
| `app/Models/Tag.php` | Simple count. |
| `database/migrations/..._create_posts_table.php` | `posts` table (status, published_at, etc.). |
| `database/migrations/..._create_comments_table.php` | `comments` table (`approved` column). |

No custom request classes, policies, or services are specific to the dashboard. The `DashboardProfileRequest` belongs to the **user** dashboard and is **not** used in the admin panel.

---

## 3. Route & Middleware

The route responsible for the admin dashboard is defined inside the **strictly Admin** route group (group 1 in `routes/web.php`).

```php
// GROUP 1: Strictly Admin Only Routes
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        // ... other admin-only routes
    });
```

**Route details:**
- **HTTP method:** `GET`
- **URI:** `/admin`
- **Name:** `admin.dashboard`
- **Middleware:**
  - `auth` → ensures user is logged in.
  - `role:Admin` → only users possessing the `Admin` role pass; all others receive `403 Forbidden` (see `CheckRole` middleware).

**`CheckRole` behaviour:**
- If the user is not authenticated, redirect to login with an error flash.
- If the user is authenticated but lacks the required role (`Admin`), the attempt is logged (`Log::warning`) with user ID, required and actual roles, URL, and IP, and the request is aborted with `403`.
- Priority set to `10`, so it runs after the `auth` middleware guarantees a user object.

**Effect:** Even if an Editor has the `view admin panel` permission (see below), they **cannot** access this route because `role:Admin` purely checks Spatie roles, not permissions.

---

## 4. Authorization (Gate Check)

Inside `DashboardController@index`, a second layer protects the logic:

```php
Gate::authorize('view-admin-panel');
```

This uses Laravel’s Gate and matches the Spatie permission **`view admin panel`**. In the seeded roles (see `user-management.md`), this permission is assigned to:
- **Admin** – inherently, because Admin has *all* permissions.
- **Editor** – also explicitly given `view admin panel`.

However, the middleware already prevents Editors from reaching the controller. The `Gate::authorize` call is therefore redundant but serves as an additional safety net and explicit documentation of intent. If the route middleware were ever relaxed to include Editors, they could then view the dashboard because the gate would succeed.

**Important:** The `AuthServiceProvider`’s global `Gate::before` callback grants any `Admin` role immediate `true` for all abilities; thus for Admins the policy/permission check is effectively bypassed.

---

## 5. Dashboard Statistics (`$stats`)

The controller builds an associative array `$stats` with the following keys. All queries are executed **synchronously** on every dashboard load.

| Key | Query / Calculation | Description |
|-----|---------------------|-------------|
| `total_posts` | `Post::count()` | Total rows in `posts` table (including soft‑deleted posts? No – `Post` uses `SoftDeletes`, so `count()` excludes trashed posts. Only non‑trashed posts are counted.) |
| `published_posts` | `Post::published()->count()` | Posts matching the `published` scope: `status = 'published'` AND `published_at` not null AND `published_at <= now()`. Schedule‑future posts are excluded. |
| `draft_posts` | `Post::draft()->count()` | Posts with `status = 'draft'` (non‑trashed). |
| `pending_comments` | `Comment::where('approved', false)->count()` | Comments where `approved` is `false` (i.e., awaiting moderation). |
| `total_comments` | `Comment::count()` | All comments regardless of approval status (no soft deletes on comments). |
| `total_users` | `User::count()` | All non‑soft‑deleted users. |
| `active_users` | `User::whereHas('posts')->count()` | Users who have at least one post (trashed posts are excluded because `posts()` relationship respects soft deletes by default). |
| `total_categories` | `Category::count()` | Total categories (no soft deletes). |
| `total_tags` | `Tag::count()` | Total tags (no soft deletes). |
| `recent_posts` | `Post::with(['author'])->latest()->take(5)->get()` | Last five posts (by `created_at` descending) with their author eager‑loaded. |
| `recent_comments` | `Comment::with(['commentable', 'author'])->latest()->take(10)->get()` | Last ten comments with the parent model (the post) and the comment author. |
| `top_authors` | `User::withCount(['posts' => fn($q) => $q->published()])->orderByDesc('posts_count')->take(5)->get()` | Five users with the highest number of published posts. The `posts_count` attribute is added to each model. |

**Eager loading notes:**
- `recent_posts` eager‑loads `author` but does **not** load categories or tags. The view is expected to show only simple post info.
- `recent_comments` eager‑loads `commentable` (the post) and `author`; `commentable` is a morphTo relationship that will load the appropriate model (always `App\Models\Post` in this system).

**Soft‑deletion awareness:**
- `Post::count()` excludes soft‑deleted posts. If you need trashed posts displayed, a scope adjustment is necessary.
- `User::count()` excludes soft‑deleted users; they are not counted as `total_users` or `active_users`.

---

## 6. Chart Data (`$chartData`)

The method `getChartData()` returns an array designed for front‑end chart libraries (e.g., Chart.js). It contains three keys:

```php
[
    'months'   => [...],  // labels, e.g., ['Jan 2026', 'Feb 2026', ...]
    'posts'    => [...],  // post counts per month
    'comments' => [...],  // comment counts per month
]
```

### 6.1 Time Window

The charts always cover the **last 6 calendar months**, including the current month. The calculation uses `now()` (server time) as the reference point.

Example: if today is 10 May 2026, the months will be `['2025-12', '2026-01', '2026-02', '2026-03', '2026-04', '2026-05']`.

### 6.2 Posts by Month Query

```php
Post::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupBy('month')
    ->orderBy('month')
    ->pluck('count', 'month')
    ->toArray();
```

- Filters posts created on or after a date 6 months ago.
- Uses MySQL `DATE_FORMAT` to extract year‑month as a string `'YYYY-MM'`.
- Groups by that formatted month and counts rows.
- The result is an associative array `['2026-01' => 42, '2026-02' => 18, ...]`.

**Critical:** This query is **MySQL‑specific**. If the application is moved to PostgreSQL, SQLite, or SQL Server, it will fail. See §9.

### 6.3 Comments by Month Query

Exactly the same structure but over the `Comment` model:

```php
Comment::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
    ->where('created_at', '>=', now()->subMonths(6))
    ->groupBy('month')
    ->orderBy('month')
    ->pluck('count', 'month')
    ->toArray();
```

### 6.4 Filling Missing Months

The system ensures every month in the 6‑month window appears, even if there were zero posts/comments. It does this by:

1. Generating the correct sequence of month keys using PHP’s `now()->subMonths($i)->format('Y-m')` in reverse order.
2. Merging with default `0` using `$postsByMonth[$m] ?? 0`.

```php
$months = collect(range(0, 5))->map(function ($i) {
    return now()->subMonths($i)->format('Y-m');
})->reverse()->values()->toArray();
```

### 6.5 Label Formatting

The `months` key in the final array is converted to a human‑readable label like `'Jan 2026'` using Carbon:

```php
'months' => array_map(function ($m) {
    return \Carbon\Carbon::createFromFormat('Y-m', $m)->format('M Y');
}, $months)
```

The resulting `months` array is a list of strings, e.g., `['Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'May 2026']`.

---

## 7. Data Passed to View

The controller returns:

```php
return view('admin.dashboard', compact('stats', 'chartData'));
```

So the view receives two variables:

- **`$stats`** – the full associative array described in §5.
- **`$chartData`** – the array described in §6.

The view is expected to render widgets, lists, and charts using this data. No additional processing is done on the server side.

---

## 8. Authorization & Access Control Summary

| Layer | Logic | Effect |
|-------|-------|--------|
| Route middleware `role:Admin` | User must have role `Admin` | Editors are blocked completely at HTTP level (403). |
| `Gate::authorize('view-admin-panel')` | Checks Spatie permission `view admin panel` | Redundant for Admins because of `Gate::before` bypass; would allow Editors if middleware were removed. |
| `Gate::before` in `AuthServiceProvider` | If user has role `Admin`, all abilities return `true` | Ensures Admins never fail any gate check. |

**Net result:** Only users with the `Admin` role can load the dashboard. Editors, Authors, and Users receive a 403 error if they attempt any `/admin` URI because the middleware rejects them before the controller is invoked.

---

## 9. Performance Considerations & Limitations

- **No caching:** Every dashboard load executes all 10+ queries (5 counts, 3 collections, 2 chart aggregates). For a site with tens of thousands of posts/comments, this may become slow. Consider caching the counts for a short period (e.g., 5 minutes) and using queues for heavy chart queries if needed.
- **Full table scans on counts:** `Post::count()`, `Comment::count()`, etc. will scan the respective tables. Indexing is not utilised for simple counts unless a `where` clause is present. This is acceptable for small to medium datasets but should be monitored.
- **Chart query portability:** The use of `DATE_FORMAT` is MySQL‑specific. If the database driver changes, this query will break. Alternative: use Laravel’s `->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))` with raw column aliases, or use `->whereBetween` with a date range and group by `created_at->format('Y-m')` after fetching (with the cost of fetching more rows). This is a documented technical debt.
- **`latest()` default column:** `Post::latest()` orders by `created_at`. For posts, `created_at` is always set, so order is reliable.
- **Eager loading in recent lists:** `recent_posts` loads only `author` (not categories/tags). If the view needs category names, modify the query or ensure the Blade uses attributes already available from the model. `recent_comments` loads `commentable`; make sure every `commentable` model has an `url` attribute accessor (as required by the comments system).
- **`active_users` calculation:** `whereHas('posts')` checks for any post, including drafts. If the definition of “active” should only count users with published posts, the sub‑query would need to use `->whereHas('posts', fn($q) => $q->published())`. Currently it counts all users with at least one non‑trashed post.

---

## 10. Integration with Other Modules

The dashboard pulls data from multiple domains. Refer to these documents for deeper understanding of the underlying models and permissions:

- **User management:** `user-management.md` – roles, permissions, `Admin` role definition.
- **Post system:** `post-system.md` – `published()`, `draft()` scopes, soft deletes, author relationship.
- **Comments system:** `comments-system.md` – `approved` column logic, moderation flow, relationships.
- **Authentication:** `authentication.md` – login flow and Spatie integration.
- **Authorization & Policies:** The global `Gate::before` bypass is described in the `AuthServiceProvider`; see also the `authorization-and-middleware.md` document if it exists (it would cover `CheckRole` and Spatie permission registration). For this dashboard, no custom policy is used; the only gate is `view-admin-panel`.

Any change to model scopes (e.g., adding a new post status) or new content types will need corresponding updates to the dashboard statistics.

---

## 11. Future Development & Extension Points

- **Add caching:** Wrap the stat queries in `Cache::remember()` with a configurable TTL. The chart data could be scheduled separately via a queued job.
- **Database‑agnostic chart queries:** Replace `DATE_FORMAT` with portable query constructs (e.g., using `GROUP BY YEAR(created_at), MONTH(created_at)` and then formatting the label in PHP).
- **Add Editors Dashboard:** To give Editors a limited dashboard, a new middleware group `role:Admin,Editor` could be created for a different route, and the `Gate::authorize` check would already pass. The controller could detect the role and conditionally hide certain widgets.
- **Real‑time updates:** Not currently supported; could be implemented with Laravel Echo and event broadcasting.
- **Custom date range:** Allow admin to select a custom period for chart data via query parameters.

---

## 12. Troubleshooting Quick Reference

| Symptom | Possible Cause | Solution |
|---------|----------------|---------|
| 403 on `/admin` despite being logged in as Editor | Middleware `role:Admin` blocks Editors | Editors are not meant to access the admin dashboard; only Admins can. To allow Editors, change middleware to `role:Admin,Editor` (but ensure they have the `view-admin-panel` permission). |
| `View [admin.dashboard] not found.` | Missing Blade file | Ensure `resources/views/admin/dashboard.blade.php` exists. |
| `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'approved'` | `comments` table migration not run or column missing | Run `php artisan migrate` and check the comments migration. |
| Dashboard shows 0 for everything | No seeded data or `users` table empty | Run database seeders (`php artisan db:seed`). |
| Charts not rendering on front end | `chartData` passed but JS library not configured | Verify the front‑end code that consumes `chartData.months`, `chartData.posts`, `chartData.comments`. The backend only provides the data. |
| `DATE_FORMAT` errors on non‑MySQL DB | DBMS doesn’t support `DATE_FORMAT` | Switch to portable query (see §11). |

---