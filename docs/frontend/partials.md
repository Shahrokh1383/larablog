---

# Partial Components Documentation

## Overview

The `resources/views/partials/` directory houses four reusable Blade partials that are the backbone of the public-facing blog’s user interface. They encapsulate:

- **Post rendering** (`_post_card.blade.php`) – the card that displays a summary of a blog post in listings, search results, or related posts.
- **Sidebar** (`_sidebar.blade.php`) – a contextual sidebar widget aggregating blog stats, navigation for administrators, category/tag filtering, and moderation cues.
- **Comment submission** (`_comment_form.blade.php`) – the form to leave a new comment on a post, supporting both authenticated and guest users.
- **Comment threading** (`_comments.blade.php`) – the flat‑reply comment display with nested reply forms, moderation actions, and dynamic toggle behaviour.

These partials are designed to be included in various layouts (e.g., `posts/index`, `posts/show`, `dashboard`) and share a consistent design system powered by Bootstrap 5 and custom CSS (`post.css`, `sidebar.css`). They are tightly integrated with the backend Laravel routes, controllers, and policies, and they expect specific data from the views that host them.

---

## File List

| File | Responsibility |
|------|----------------|
| `resources/views/partials/_post_card.blade.php` | Renders a single post card with image, meta, excerpt, tags, and admin actions; includes a delete modal. |
| `resources/views/partials/_sidebar.blade.php` | Sidebar aggregator: blog stats, category/tag lists, admin quick links, and pending comment alert. |
| `resources/views/partials/_comment_form.blade.php` | New comment form (main post level) with guest name/email fields and char counter placeholder. |
| `resources/views/partials/_comments.blade.php` | Displays a flat‑threaded comment list with reply forms, approve/delete buttons, and toggle scripts. |

---

## `_post_card.blade.php`

### Path and Purpose
**File:** `resources/views/partials/_post_card.blade.php`

Renders a single blog post as a Bootstrap card. Used primarily in post listing pages (e.g., `posts/index`, author profile pages, search results) and possibly in “related posts” sections. The card is self-contained and includes a delete modal for users with appropriate roles.

### Data Dependencies
The partial expects a single Laravel component prop: `$post` (an instance of `App\Models\Post`). An optional `$compact` boolean prop (default `false`) is accepted to shrink the card (smaller image, shorter excerpt, smaller badges).

The `Post` model provides:
- `featured_image` (accessor for image path)
- `getImage('card')` – returns the ‘card’ sized image URL
- `author` relationship (User) with `avatar_url` and `name`
- `updater` relationship (User|null) for “edited by” info
- `published_at` (Carbon|null)
- `title`, `excerpt`, `body` (HTML)
- `categories` and `tags` relationships with counts
- `views`, `reading_time`, `comments_count`
- `id` for route model binding

Controllers that typically pass this partial include:
- `PostController@index` (list of posts)
- `PostController@show` (related posts)
- `AuthorController@show` (author’s posts)

### Blade Directives & Logic

- **`@props(['post', 'compact' => false])`** – Defines the two expected props.
- **`@if($post->featured_image)`** – Only shows the image if one exists.
- **Author avatar** – Uses `$post->author->avatar_url` directly; no fallback image is defined here (assumes `avatar_url` always returns a valid path).
- **Edited indicator** – `@if($post->updater && $post->updater->id !== $post->user_id)` shows “Edited by” only when an updater exists and differs from the original author.
- **`$post->published_at?->format('M d, Y')`** – Null-safe formatting; if `published_at` is null, nothing is printed.
- **Excerpt/body fallback** – `@if($post->excerpt)` shows excerpt, otherwise strips tags from `$post->body` and limits via `Str::limit`. The `$compact` flag adjusts the limit (80 vs 150).
- **Categories & Tags** – `@if($post->categories->count())` and `@if($post->tags->count())` loops. `take(2)` for categories, `take(3)` for tags.
- **Footer actions** – `@if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEditor()))` – Shows a delete button only to Admin and Editor roles. This button triggers a modal (`#deletePostModal{{ $post->id }}`).
- **Delete Modal** – Also wrapped in the same auth check. The modal contains a form that POSTs to `posts.destroy` with `@method('DELETE')`.

### Conditional Rendering & State Handling
- **No featured image** – The `<img>` tag is omitted entirely, so the card starts directly with the body.
- **No categories/tags** – The respective loops simply render nothing.
- **No excerpt and empty body** – `strip_tags($post->body)` may produce an empty string, which `Str::limit` will handle gracefully.
- **`$compact` mode** – Chooses CSS class `card-compact` vs `h-100 post-card`, sets inline height for image (120px vs 200px), adjusts excerpt length, badge sizing.
- **Delete modal** – Only rendered if the user is Admin or Editor; for guests or regular users, no modal markup is emitted, eliminating unnecessary DOM.

### Links & Navigation
- **Post detail** – `route('posts.show', $post)` appears twice: once on the title and once on the “Read More” button.
- **Delete form** – `route('posts.destroy', $post)` with method DELETE.

### Styling Notes
The card uses classes from **`resources/css/post.css`** and Bootstrap. Key CSS classes:
- `.post-card`, `.post-card-image`, `.post-card-body`, `.post-card-title`, `.post-card-content`, `.post-card-tag`, `.post-card-date`, `.post-card-author-img`, `.post-card-button`.
- The hover effects, transitions, and responsive tweaks are defined in `post.css` (lines 50‑250 roughly). The grid layout `.posts-grid-container` (not in this partial) controls the overall card placement.

### JavaScript Behavior
No inline JavaScript is present in this partial. The delete modal relies on Bootstrap’s modal JS (included globally via `resources/js/app.js` which imports Bootstrap). The partial simply uses `data-bs-toggle` and `data-bs-target` attributes, so no custom JS is needed.

---

## `_sidebar.blade.php`

### Path and Purpose
**File:** `resources/views/partials/_sidebar.blade.php`

Renders a multi‑widget sidebar for the blog. It is typically included in layouts like `resources/views/layouts/app.blade.php` (or directly in pages like `posts/index` and `posts/show`). It provides:
- “About” blurb
- Admin quick‑access links (role‑based)
- Pending comment alert (only for Admins with approval permission)
- Category and tag listings with post counts
- Blog statistics

### Data Dependencies
This partial does **not** receive any variables via `@props`. It **directly queries the database** using Eloquent:
- `\App\Models\Category::count()` and `::withCount('posts')->get()`
- `\App\Models\Tag::count()` and `::withCount('posts')->get()`
- `\App\Models\Comment::where('approved', false)->count()` (only if user is Admin)
- `\App\Models\Post::published()->count()`

Because it performs own data fetching, it is completely self‑contained. This is a design choice—no controller needs to supply sidebar data. However, note the potential N+1 issues if many category/tag queries are repeated on every page (mitigated by `withCount`).

### Blade Directives & Logic

- **`@can('view-admin-panel')`** – Wraps the entire “Admin Quick Access” widget. The gate `view-admin-panel` is defined in a policy (likely `App\Policies\PostPolicy` or similar) and typically allows Admin and Editor roles.
- **`@role('Admin')`** – Inside the admin widget, certain links (Dashboard, Categories, Tags) are shown only to users with the `Admin` role. Editors see only “Manage Posts”.
- **`@role('Admin')` and `@can('approve', \App\Models\Comment::class)`** – Combined check for the “Pending Comments Alert”. Only Admins who can approve comments see this. It fetches the pending count directly.
- **`@if($pendingComments > 0)`** – Displays the alert only if there are pending comments.
- **`@if($sidebarCategories->count())` / `@if($sidebarTags->count())`** – Ensures the widgets appear only when there is data.
- **`@foreach` loops** – Iterate categories/tags and display names, post counts, and links.
- **`{{ \App\Models\Post::published()->count() }}`** – Scope `published` likely filters posts where `published_at` is not null and <= now.

### Included Sub‑Partials/Components
None. It is a standalone partial.

### Forms
No forms in this partial. All links are navigation links.

### Conditional Rendering & State Handling
- **Unauthenticated / Guest** – Only the “About”, “Categories”, “Tags”, and “Stats” widgets are shown. Admin widgets are hidden entirely because `@can('view-admin-panel')` returns false.
- **Editor role** – Sees the Admin Quick Access box but only the “Manage Posts” link. Dashboard, Categories, Tags are hidden.
- **Admin role** – Sees all admin links.
- **No pending comments** – The alert is omitted, keeping the sidebar clean.
- **No categories/tags** – The respective widgets are not rendered.
- **No posts** – Stats widget shows zeros, which is truthful.

### Links & Navigation
- **Admin routes**:
  - `route('admin.dashboard')` – Admin dashboard.
  - `route('admin.categories.index')` – Category management.
  - `route('admin.tags.index')` – Tag management.
  - `route('admin.posts.index')` – Post management (available to both Admin and Editor).
  - `route('admin.comments.pending')` – Pending comments moderation page.
- **Public routes**:
  - `route('categories.show', $category->slug)` – Category archive.
  - `route('tags.show', $tag->slug)` – Tag archive.

### Styling Notes
All custom styling is in **`resources/css/sidebar.css`** (provided in full). Classes used include:
- `.sidebar-card`, `.sidebar-card-header`, `.sidebar-card-body` – universal widget styling.
- `.about-widget`, `.admin-quick-access`, `.pending-comments-alert`, `.categories-widget`, `.tags-widget`, `.stats-widget` – widget‑specific variations with distinct gradient headers.
- `.animated-list-item`, `.enhanced-badge`, `.sidebar-container` etc.

The CSS defines sticky positioning, hover animations, responsive breakpoints (992px and below), scrollbar styles, and pulse/glowing effects.

### JavaScript Behavior
No JavaScript is embedded; however, the partial relies on Bootstrap’s JS for any interactive components (none present). The sidebar itself is purely static in terms of JS. The “sidebar toggle” button (described in `post.css` for mobile) is **not** in this partial; it likely lives in the parent layout.

---

## `_comment_form.blade.php`

### Path and Purpose
**File:** `resources/views/partials/_comment_form.blade.php`

Renders the comment submission form at the bottom of a blog post (before the comment list). It adapts to guest vs. authenticated users and respects a config setting `blog.allow_guest_comments`.

### Data Dependencies
- **`$post`** – The `App\Models\Post` instance on which the comment is being made. Passed via `@props(['post'])`.

The form expects the `CommentRequest` validation rules (defined in `app/Http/Requests/CommentRequest.php`), which likely require `body` and, for guests, `guest_name` and `guest_email`.

### Blade Directives & Logic

- **`@guest` / `@endguest`** – Wraps the guest name/email fields.
  - **`@if(config('blog.allow_guest_comments', true))`** – If guest comments are allowed (default `true`), it shows name/email inputs. Otherwise, an alert with a login link is displayed.
- **`@error('guest_name')`** / `@error('guest_email')` / `@error('body')` – Standard Blade error directives, showing `invalid-feedback` Bootstrap styling.
- **`{{ old('guest_name') }}`** – Repopulates the guest fields after validation failure.
- **Character counter placeholder** – `<span id="comment-char-count">0</span>/1000 characters` is present but the JS to update it is **not** in this partial. The counter likely requires an external script (see JavaScript Behaviour below).

### Forms
- **Action**: `route('posts.comments.store', $post)` – This POST route maps to `CommentController@store` (for top-level comments on a post).
- **Method**: `POST` only; no `@method` spoofing needed.
- **CSRF**: `@csrf` included.
- **Fields**:
  - `guest_name` (required for guests)
  - `guest_email` (required for guests)
  - `body` textarea (required, maxlength not enforced in HTML but validated server-side)
- **Submit button**: “Post Comment”.
- **Old input**: `value="{{ old('guest_name') }}"` etc. for name/email; `{{ old('body') }}` inside the textarea.

### Conditional Rendering & State Handling
- **Authenticated user**: The guest fields are completely omitted; only the comment body and the submit button appear.
- **Guest, but guest comments disabled**: Only a message with a login link appears; the form is hidden entirely.
- **Validation errors**: Each field displays its error message using `@error` directive, and `is-invalid` class is toggled.
- **Character counter**: The counter element is always present, but without JS it stays at “0”. The server side likely limits to 1000 characters.

### Links & Navigation
- `route('login')` – Only shown when guest comments are disabled, linking to the login page.

### Styling Notes
The form uses Bootstrap card classes (`card`, `card-body`, `shadow-sm`, etc.) without custom CSS classes from the provided stylesheets. The design is simple and clean, relying on Bootstrap’s default form styles.

### JavaScript Behavior
No JavaScript is present in this partial. However, there is a **character counter element** (`#comment-char-count`) that is clearly intended to be updated dynamically. The actual JS for this counter is **not provided** in the partial nor in any of the CSS files; it most likely resides in `resources/js/app.js` or a dedicated comment script. As a senior developer, you must ensure that a script binding to the `#body` textarea `input` event exists to update the counter up to 1000 characters. Without it, the counter remains static, which is a known gap.

---

## `_comments.blade.php`

### Path and Purpose
**File:** `resources/views/partials/_comments.blade.php`

Renders a flat‑threaded comment tree. For each top‑level comment, it displays the comment body, author info, moderation actions, a reply form, and a toggle button to show/hide all nested replies. Replies are shown as a flat list with indentation, including their own reply forms.

### Data Dependencies
- **`$comments`** – A collection of `App\Models\Comment` instances (typically the top‑level comments for a post, loaded with their replies).
- **`$depth`** (default 0) and **`$maxDepth`** (default 3) – Not actually used in the logic; they exist in the props but have no visible impact. This suggests the partial might have been designed for threaded recursion but currently implements a flat reply model.

The Comment model provides:
- `id`, `body`, `display_name` (accessor for author name/guest name), `avatar_url`, `user_id` (null for guests), `approved` (boolean), `created_at`, `getAllReplies()` relationship, `parent_id`, `parent` relationship, etc.

The partial is typically included with `@include('partials._comments', ['comments' => $post->comments()->whereNull('parent_id')->with('replies')->get()])` inside `posts/show`.

### Blade Directives & Logic

- **`@foreach($comments as $comment)`** – Iterates top‑level comments.
- **Visibility gate**: `@if($comment->approved || auth()->user()?->can('approve', $comment))` – A comment is shown if it is approved, OR if the current user can approve it (admin/moderator). This allows admins to see unapproved comments in the public view.
- **Unapproved styling**: If `!$comment->approved`, the card gets `opacity-75 border-start border-warning` and a “Pending” badge.
- **Guest badge**: `@if(!$comment->user_id)` shows a “Guest” badge next to the name.
- **Approval actions** – `@can('approve', $comment)`:
  - If not approved: form POST to `route('admin.comments.approve', $comment)` with CSRF.
  - If already approved: form POST to `route('admin.comments.reject', $comment)`.
- **Delete action** – `@can('delete', $comment)`: form DELETE to `route('admin.comments.destroy', $comment)` with confirmation dialog.
- **Reply toggle button** – `<button onclick="toggleReplyForm({{ $comment->id }})">` shows/hides the reply form for that specific comment.
- **Reply form** (inside `#reply-form-{{ $comment->id }}`, initially `display: none`):
  - Action: `route('comments.reply', $comment)` – POST route that stores a reply to `$comment`.
  - Contains guest fields if `@guest`, else just the body.
- **Flat replies section**:
  - `@if($comment->getAllReplies()->count())`
  - “Show/Hide replies” toggle button calling `toggleFlatReplies(commentId)`.
  - `#flat-replies-{{ $comment->id }}` container, initially hidden (`style="display: none;"`).
  - Inside, loops `$comment->getAllReplies()` and displays each reply with:
    - If `$reply->parent_id !== $comment->id && $reply->parent`, shows “to [parent name]” to indicate the direct parent.
    - Reply form for the reply itself (same `toggleReplyForm(id)` mechanism).
    - Delete button for replies with permission check.

### Conditional Rendering & State Handling
- **No comments**: The `@foreach` doesn’t render anything; the parent view (e.g., `posts/show`) usually shows a “No comments yet.” message. That message is **not** part of this partial—it is the responsibility of the including view.
- **Empty replies**: The “Show replies” button and container are omitted entirely if `getAllReplies()->count()` is zero.
- **Unapproved comments** are visible to moderators; regular users never see them (the `@if` condition fails).
- **Guest reply forms** respect `@guest` and ask for name/email; authenticated users skip those fields.

### Links & Navigation
All actions are form submissions (no plain links for navigation):
- `admin.comments.approve` → POST (moderation)
- `admin.comments.reject` → POST (moderation)
- `admin.comments.destroy` → DELETE (moderation)
- `comments.reply` → POST (stores a reply to a specific comment)

### Styling Notes
Custom classes from `post.css`:
- `.comment-item` – transition and hover effect (move up slightly).
- `.replies` – left border with post primary color (but this class is **not applied** in this partial; the flat replies use inline classes `border-start border-2 border-light ps-3`). The CSS defines `.replies` but it’s unused—likely intended for a nested version.
- No other custom classes; relies heavily on Bootstrap’s utility classes and card components.

### JavaScript Behavior
The partial contains **inline `<script>`** block at the end with two functions:

1. **`toggleReplyForm(commentId)`**
   - Toggles the `display` property of the reply form element.
   - If showing, focuses the textarea inside the form.

2. **`toggleFlatReplies(commentId)`**
   - Toggles the flat replies container and updates the button text/icon between “Show … replies” and “Hide replies”.
   - Uses the `data-count` attribute to restore the original count text.

These functions are defined globally (no `defer` or module encapsulation), so they must be loaded after the DOM. The script is placed at the end of the partial, but if the partial is included multiple times on a page (e.g., multiple post comment sections), the functions would be redefined, which is harmless but not ideal. A better practice would be to place this script once in the parent layout, but as it stands, the partial is self‑contained.

---

## Integration with Backend

### Routes & Controllers
- **Post card**:
  - `posts.show` → `PostController@show` (displays full post).
  - `posts.destroy` → `PostController@destroy` (soft‑delete; accessible only by Admin/Editor via policy `PostPolicy@delete`).
- **Sidebar**:
  - Categories: `categories.show` → Likely `CategoryController@show` (or route model binding).
  - Tags: `tags.show` → similar.
  - Admin routes: All under `admin.*` namespace, managed by `Admin\*Controller` classes as per the project’s backend docs.
- **Comment form**:
  - `posts.comments.store` → `CommentController@store` (handles both authenticated and guest comments; validates with `CommentRequest`; fires `CommentPosted` event, which triggers `SendCommentNotification` listener).
- **Comments**:
  - `admin.comments.approve`, `admin.comments.reject`, `admin.comments.destroy` → managed by `Admin\CommentAdminController` (as per backend docs).
  - `comments.reply` → `CommentController@reply` (stores nested reply).

### Policies & Authorization
- **`PostPolicy`**: `delete` – likely allows Admin and Editor roles (matches `auth()->user()->isAdmin() || auth()->user()->isEditor()`).
- **`CommentPolicy`**:
  - `approve` – resolves permissions for comment approval; expected to allow Admin and possibly Editor.
  - `delete` – resolves who can delete a comment.
- **`view-admin-panel` gate**: Defined in `AuthServiceProvider` (or via a policy) to allow Admin and Editor roles (as used in sidebar).

### Session Flash Messages
These partials do **not** display flash messages directly. The parent layouts (e.g., `layouts/app.blade.php`) are expected to render `session('success')`, `session('error')`, etc. When a comment is submitted, the controller likely redirects back with a success message, which the layout will show. The partials themselves are flash‑agnostic.

---

## Edge Cases & UI States

- **Post Card**:
  - **No image**: Gracefully omitted.
  - **Null `published_at`**: No date displayed.
  - **Empty excerpt & body**: `Str::limit` on empty string returns empty string; the card still shows the title and meta, albeit without a teaser.
  - **Missing author avatar**: `avatar_url` may return a default placeholder (assumed by the accessor), so no broken images under normal setup.
- **Sidebar**:
  - **No categories/tags exist**: The corresponding widgets are not rendered, avoiding empty boxes.
  - **No published posts**: Stats show zero; still informative.
  - **Guest users**: Only public widgets visible.
  - **Editors**: Limited admin quick access, no comment approval alerts.
  - **Admins without `approve` permission**: The pending alert is hidden due to `@can('approve')`.
- **Comment Form**:
  - **Guest comments disabled**: A clean info box with a login link.
  - **Validation errors**: Old input is repopulated, errors are displayed next to fields.
  - **Character counter without JS**: Shows “0/1000” statically—could mislead users if they type more than 1000 chars. The server‑side validation catches this, but the UX is incomplete. A developer must ensure the counter script is included.
- **Comments**:
  - **No comments**: Partial renders nothing; the parent view should show “No comments yet.” (not provided here—must be handled in the includer).
  - **Nested deep replies**: The flat list shows all replies regardless of depth; `$maxDepth` is unused, so there’s no truncation. This could become unwieldy with very deep threads.
  - **Unapproved comments**: Only visible to admins; for regular users they are entirely absent.
  - **Multiple instances of the inline script**: If the partial is included more than once (e.g., multiple post comment sections on the same page), the functions `toggleReplyForm` and `toggleFlatReplies` are redefined. This is acceptable but could be improved by moving JS to a dedicated file.
  - **Reply form UX**: Toggling a reply form closes any other open reply forms? No—the function only toggles the targeted form; multiple forms can be open simultaneously, which may clutter the UI.

---
