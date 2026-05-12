```markdown
# Public Post Views – Frontend Module Documentation

## Overview

The public post views module encompasses all Blade templates responsible for rendering blog posts to end users as well as providing creation and editing interfaces for authorised authors and administrators. This module covers:

- **Post listing** – a responsive grid of post cards with search and pagination.
- **Single post view** – full article with hero image, metadata, author social links, related posts, and a threaded comment section.
- **Post creation** – a form restricted to users with the `create` ability for posts.
- **Post editing** – the same form repurposed, restricted to admins and editors.
- **Shared form partial** – DRY template reused for both creation and editing, handling all fields, validation, image upload preview, and state-dependent options.

Creation and editing interfaces use an admin-oriented layout (`layouts.admin`) and are gated by policy permissions. The public-facing listing and detail views use the main frontend layout (`layouts.app`).

---

## File List

| File | Role |
|------|------|
| `resources/views/posts/index.blade.php` | Displays a paginated, searchable grid of published posts. |
| `resources/views/posts/show.blade.php` | Renders a single post’s content, metadata, comments, and related posts. |
| `resources/views/posts/create.blade.php` | Entry point for creating a new post; extends `layouts.admin`. |
| `resources/views/posts/edit.blade.php` | Entry point for editing an existing post; extends `layouts.admin` and passes existing data. |
| `resources/views/posts/partials/form.blade.php` | Shared form partial used by `create` and `edit`, containing all input fields, image handling, and action buttons. |
| `resources/views/layouts/app.blade.php` | Master layout for the public-facing pages (index, show). Contains navbar, flash messages, and imports Bootstrap and custom assets. |
| `resources/css/post.css` | Dedicated stylesheet for all public post views (cards, single post, sidebar, search, responsiveness). |
| `resources/js/app.js` | Global JavaScript including slug generation, character counters, reading time estimate, image preview/validation, and form helpers. |

---

## View Hierarchy & Inheritance

```
layouts.app
├── posts.index
│   ├── includes partials._post_card (for each post)
│   └── includes partials._sidebar
└── posts.show
    ├── includes partials._comment_form
    ├── includes partials._comments
    └── includes partials._sidebar

layouts.admin (not provided, used for admin area)
├── posts.create
│   └── includes posts.partials.form
└── posts.edit
    └── includes posts.partials.form
```

- `posts.index` and `posts.show` extend `layouts.app`, populating:
  - `@section('title', ...)`
  - `@section('content')`
  - They push inline styles and scripts via `@push('styles')` and `@push('scripts')`.
- `layouts.app` yields `'content'` and stacks `'styles'` and `'scripts'`.
- `posts.create` and `posts.edit` extend `layouts.admin`, which is a different layout (not provided) intended for the content management area.

The form partial `posts.partials.form` receives variables like `$post`, `$method`, `$action`, `$buttonText`, and optionally `$categories` and `$tags` (expected to be injected from the controller or view composer).

---

## Detailed View Documentation

### 1. `resources/views/posts/index.blade.php`

**Path and Purpose**  
The public post listing page, accessible to all visitors. Displays a searchable grid of post cards.

**Data Dependencies**  
- `$posts` – an instance of `Illuminate\Pagination\LengthAwarePaginator` containing published `Post` models.  
Likely provided by `PostController@index`.

**Blade Directives Used**
- `@forelse($posts as $post)` … `@empty` – handles empty collection.
- `@if($posts->hasPages())` – conditionally shows Bootstrap 5 pagination.
- `@auth`, `@guest` – show “Create First Post” link only for authenticated users with admin/editor/author roles.
- `@include('partials._post_card', ['post' => $post])` – renders each card.

**Included Partials**
- `partials._post_card` – receives a single `$post` variable.
- `partials._sidebar` – a sidebar widget (content not provided).

**Conditional Rendering**
- The empty state shows a different message/button based on whether the user is logged in and has role `Admin`, `Editor`, or `Author` (via `auth()->user()->isAdmin()` etc.).
- Pagination links only appear when there are multiple pages.

**Links & Navigation**
- Search form submits to `route('posts.index')` via GET.
- Each post card presumably links to `route('posts.show', $post)` (inside `_post_card`).
- “Create First Post” button links to `route('posts.create')`.
- Navbar (from `app`) includes active link detection for `posts.*`.

**Styling Notes**
- Scaffolding relies on Bootstrap 5.3 classes (`container-fluid`, `row`, `col-lg-8`, etc.).
- Custom styles defined in `resources/css/post.css` govern the look of `.search-container`, `.posts-grid-container`, `.post-card`, `.empty-state-card`, sidebar toggle, and responsive breakpoints.

**Inline JavaScript**  
A `<script>` block in `posts.index` handles sidebar toggle on mobile (≤992px). It is not included in any stack but rendered directly within `@section('content')` (note: it would be better placed in `@push('scripts')` to respect layout structure, but the current implementation renders it inline). It listens for click on `#sidebarToggle` and uses `sidebar.classList.toggle('show')`. It also closes the sidebar when clicking outside.

---

### 2. `resources/views/posts/show.blade.php`

**Path and Purpose**  
Displays a single blog post in full detail, including comments and related posts.

**Data Dependencies**
- `$post` – a `Post` model instance (must be published or accessible to the viewer).
- `$comments` – a collection of approved `Comment` models for the post, likely loaded with replies.
- Controller method: `PostController@show`.

**Blade Directives Used**
- `@can('approve', \App\Models\Comment::class)` – shows pending comment count only to users with the `approve` ability.
- `@if($post->allow_comments ?? true)` – conditionally shows comment form.
- `@foreach` for categories, tags, social links, and related posts.
- `@include('partials._comment_form', ['post' => $post])`
- `@include('partials._comments', ['comments' => $comments, 'depth' => 0, 'maxDepth' => 3])`
- Access control for edit button using `auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEditor())`.

**Included Partials**
- `partials._comment_form` – receives the `$post` instance.
- `partials._comments` – receives `$comments` collection, `$depth`, and `$maxDepth` for nested threading.
- `partials._sidebar` – same sidebar as on the index page.

**Forms**
- No direct form on this page (comment submission is handled inside `_comment_form` partial, not detailed here).

**Conditional Rendering**
- Edit button only visible to admins and editors (role-based, not policy-based; uses `isAdmin()` and `isEditor()`).
- Hero image displayed only if `$post->featured_image` is truthy.
- Author social links (GitHub, LinkedIn, Twitter, website) rendered only if the corresponding array keys exist and are non-empty.
- “Edited by” meta row shown only when `$post->updated_by` differs from `$post->user_id` and is not null.
- Excerpt displayed if present.
- Categories and tags sections only appear if they have items.
- Related posts section rendered only if there are related published posts (fetched via inline query).
- Comments section: comment form shown if `$post->allow_comments` is true; otherwise a “closed” message.
- Comments list uses `@if($comments->count())` to toggle between list and “No comments yet” placeholder.

**Links & Navigation**
- Search form (same as index) leads to `posts.index`.
- Edit button → `route('posts.edit', $post)`.
- “View” link in header of edit page (though not present here, it is in edit.blade) → `route('posts.show', $post)`.
- Social share buttons are static anchor tags (no dynamic URL generation; currently placeholders to x.com, facebook.com, linkedin.com).
- Related post titles link to `route('posts.show', $related)`.
- Comment reply toggles via `window.toggleReplyForm(commentId)`.

**Styling Notes**
- Extensive styles in `post.css` for `.post-header`, `.post-hero-image`, `.post-meta`, `.post-title`, `.post-excerpt`, `.category-badge`, `.tag-badge`, `.post-content`, `.social-share`, `.comments-section`, `.related-posts`, etc.
- Responsive adjustments at 768px.

**JavaScript (pushed to `scripts` stack)**
- Sidebar toggle logic (duplicated from index).
- Scroll to comment from URL hash (`#comment-{id}`) with a border highlight.
- Character counter for comment form (`#comment-char-count` updates on input, validates max 1000 characters).
- Global `window.toggleReplyForm` function to toggle inline reply form visibility.

---

### 3. `resources/views/posts/create.blade.php`

**Path and Purpose**  
Page for creating a new blog post. Only accessible to users authorised by the `create` Post policy.

**Data Dependencies**
- No existing post; the form receives `$post = null`.
- `$categories` and `$tags` (must be supplied to the form partial, presumably from `create` method of `PostController`).
- Controller: `PostController@create`.

**Blade Directives Used**
- `@include('posts.partials.form', [...] )` with parameters:
  - `'post' => null`
  - `'method' => 'POST'`
  - `'action' => route('posts.store')`
  - `'buttonText' => 'Create Post'`

**Included Partials**
- `posts.partials.form` – reused for creation.

**Forms**  
The form partial itself contains the `<form>` element; details covered in the form partial section.

**Conditional Rendering**
- The create view is very simple and passes all state to the partial.

**Links & Navigation**
- None on this page itself (outside the form’s cancel button).

**Styling Notes**
- Uses Bootstrap card layout with `col-lg-10 col-xl-8` for centered form.

---

### 4. `resources/views/posts/edit.blade.php`

**Path and Purpose**  
Page for editing an existing post. Access restricted to admins and editors (inferred from `isAdmin()`, `isEditor()` check on show page; but the policy is not explicitly enforced in Blade yet route probably uses `PostPolicy@update`).

**Data Dependencies**
- `$post` – the `Post` model being edited.
- `$categories`, `$tags` – for the form partial.
- Controller: `PostController@edit`.

**Blade Directives Used**
- `@include('posts.partials.form', [...] )` with:
  - `'post' => $post`
  - `'method' => 'PUT'`
  - `'action' => route('posts.update', $post)`
  - `'buttonText' => 'Update Post'`
- Directives in the partial handle conditional display based on `$post` existence.

**Included Partials**
- `posts.partials.form`

**Forms**  
Covered in form partial.

**Conditional Rendering**
- Header shows a “View” link to `route('posts.show', $post)`.
- The partial uses `$post` to pre‑fill values and show current image.

**Links & Navigation**
- “Cancel” button in the form links to `route('posts.index')` (via the form partial’s cancel link).
- “View” button in the header → `route('posts.show', $post)`.

**Styling Notes**
- Same centered card layout as create.

---

### 5. `resources/views/posts/partials/form.blade.php`

**Path and Purpose**  
A reusable form partial for creating and editing posts. It handles all input fields, validation errors, image management, and status options.

**Data Dependencies**  
All passed via `@include` parameters:
- `$post` – can be `null` (create) or a `Post` model (edit).
- `$method` – `'POST'` or `'PUT'`.
- `$action` – route URL (`route('posts.store')` or `route('posts.update', $post)`).
- `$buttonText` – label for the main submit button.
- `$categories` – collection of all categories (expected from controller).
- `$tags` – collection of all tags.

**Blade Directives Used**
- `@csrf` and `@method($method)`.
- `@error('field')` for validation feedback.
- `old('field', $post?->field)` – pre‑filling.
- `@can('publish posts')` – shows the “Published” status option and the “Publish Now” button only to users with permission to publish posts.
- `@if($post?->featured_image)` – displays current image preview and delete option.
- `@if($post && !$isPublished)` – shows the separate “Publish Now” button (sets status to `published` via name attribute on submit) only when editing a non‑published draft, and user can publish.
- Checkboxes for categories and tags use `in_array` with `old` or existing plucked IDs to determine `checked` state.

**Forms**
- `<form method="POST" action="{{ $action }}" enctype="multipart/form-data">`
- Fields:
  - `title` (text, required)
  - `slug` (text, optional; shows live preview span `#slug-preview`)
  - `featured_image` (file input with `onchange="previewImage(event, 'image-preview-new')"`; existing image can be deleted via checkbox `delete_image`)
  - `excerpt` (textarea, max 500 chars; character count via JavaScript in `app.js`)
  - `body` (textarea, required; reading time estimator)
  - `categories[]` (checkboxes)
  - `tags[]` (checkboxes)
  - `status` (select: draft/published, `published` option shown only with `@can('publish posts')`)
  - `allow_comments` (toggle checkbox, default on)
- Action buttons:
  - Cancel link → `route('posts.index')`
  - Optional “Publish Now” submit button (name="status" value="published") for drafts when user can publish.
  - Main submit button with `$buttonText`.

**Validation Error Display**
- Each input/select uses `@error('field') is-invalid @enderror` and includes `<div class="invalid-feedback">{{ $message }}</div>`.
- General category/tag errors displayed as small text below the groups.

**Old Input Handling**
- All fields use `old('field', $post?->field)` to preserve submitted values.

**Image Handling**
- Current image preview (edit mode) shows a thumbnail and file size (if file exists on disk).
- Delete checkbox adds class `opacity-25` and disables new upload via `app.js` event listener.
- New upload triggers `previewImage(event, 'image-preview-new')` global function defined in `app.js`, which validates size and type against `window.LaravelConfig`.

**Conditional Rendering**
- Status dropdown: “published” option only if user can `publish posts`.
- “Publish Now” button only when editing a draft (`$post && !$isPublished`) and user can publish.
- Delete image section only if `$post` has a featured image.
- Categories and tags loop: if none available, display a placeholder.

**Links & Navigation**
- Cancel → `posts.index`.

**Styling Notes**
- Uses Bootstrap form classes (`form-label`, `form-control`, `form-select`, `form-check`, `form-switch`, etc.).

**JavaScript Interactions**
- `app.js` hooks: slug auto‑generation from title, character counter for excerpt, reading time for body, image preview and validation, and the delete-image checkbox disabling new upload.
- Inline `onchange` handler on file input calls global `previewImage`.

---

## CSS/JS Details

### CSS – `resources/css/post.css`

This stylesheet is loaded via `@vite` in `layouts.app` (together with `app.css`, `sidebar.css`, `dashboard.css`, `author.css`). It defines CSS custom properties (design tokens) and styles for:

- **Search bar**: `.search-container`, `.search-input`, `.search-button` – clean, elevated search component.
- **Posts grid**: `.posts-grid-container` uses CSS Grid with responsive columns (auto-fill, minmax 350px).
- **Post cards**: `.post-card`, `.post-card-image`, `.post-card-body`, `.post-card-title`, `.post-card-content`, `.post-card-tag`, hover effects (translate, shadow).
- **Empty state**: `.empty-state-card` dashed border style.
- **Pagination**: Bootstrap 5 pagination overrides with rounded buttons and primary color.
- **Single post page**: `.post-header`, `.post-hero-image`, `.post-meta`, `.post-title`, `.post-excerpt` (left red border), `.category-badge`, `.tag-badge`, `.post-content` (images, pre, code), `.social-share`, `.comments-section`, `.related-posts`, `.read-more-btn`.
- **Sidebar toggle**: `.sidebar-toggle-button` (fixed, initially hidden on desktop) and `.sidebar-wrapper` (fixed off-canvas on mobile, static on desktop).
- **Responsive breakpoints**: 576px, 768px, 992px – adjust grid columns, card padding, font sizes, sidebar behaviour.
- **Animations**: `fadeIn` keyframes.

### JavaScript – `resources/js/app.js`

Loaded with `@vite` on `layouts.app`. Contains:

- `window.LaravelConfig` fallback for image upload limits.
- Slug auto‑generation: listens to `#title` input and updates `#slug` (unless manually edited, tracked via `dataset.manuallyEdited`).
- Slug preview update for categories/tags (selectors `#name` → `#slug`).
- Character counter for `#excerpt` (live count, adds `is-invalid` if >500).
- Reading time estimation for `#body` (word count / 200, minimum 1).
- Image preview: `window.previewImage(event, previewId)` validates size/mime using config, shows preview in target `<img>`, hides current image section when new image is selected.
- Delete-image checkbox: disables the new image upload input.
- Generic form submit prevention for items with `data-posts-count` (used elsewhere, not directly in post form).

The `show.blade.php` page also pushes inline scripts that handle sidebar toggle (duplicated from index, but using stack) and comment-related interactions (hash scroll, character count, reply toggle).

---

## Integration with Backend

### Routes & Controllers

| User Action | Route Name | HTTP Verb | Controller Method | Notes |
|-------------|------------|-----------|-------------------|-------|
| View all posts | `posts.index` | GET | `PostController@index` | Accepts `search` query parameter. |
| View single post | `posts.show` | GET | `PostController@show` | Requires `$post` route model binding. |
| Create post form | `posts.create` | GET | `PostController@create` | Gated by `create` Post policy. Provides `$categories`, `$tags` to view. |
| Store new post | `posts.store` | POST | `PostController@store` | Receives form data; validated by `PostRequest`. |
| Edit post form | `posts.edit` | GET | `PostController@edit` | Gated by `update` policy. Provides post, categories, tags. |
| Update post | `posts.update` | PUT | `PostController@update` | Receives form data; validated by `PostRequest`. |
| Cancel action | `posts.index` | GET | – | Return to listing. |

### Policy Integration

- The `create` and `edit` views are behind controller policies; the Blade partial uses `@can('publish posts')` to hide the “Published” status option and the “Publish Now” button from users without that ability.
- The `show` view checks roles with `auth()->user()->isAdmin()` / `isEditor()` for edit button visibility (not policy-based, but works with the current role system).
- Comment approval: `@can('approve', \App\Models\Comment::class)` shows pending count.

### Session Flash Messages

`layouts.app` includes a flash messages block that displays:

- `session('success')` → green success card
- `session('error')` → red error card
- `session('status')` → blue info card

Each is dismissible with a close button. These are rendered at the top of the content area.

### Form Submission & Validation

- All POST/PUT requests are validated by `PostRequest` (likely an `App\Http\Requests\PostRequest` form request class). Errors are flashed to the session and displayed in the form via `$errors` variable.
- The form partial uses `@error` directives for each field.
- File uploads: the `featured_image` is validated by `PostRequest` and likely processed by `ImageService` on the server side.

### Comment Integration

- The comment form (partial `_comment_form`) and list (`_comments`) are not fully documented here, but they are included in `show.blade.php`. They rely on the `$post` instance and the `comments` relationship.

---

## Edge Cases & UI States

### Loading / Empty States
- **Empty post list**: When `$posts` collection is empty, the `@empty` block shows an icon, “No posts found”, and (if authenticated as Admin/Editor/Author) a button to create the first post.
- **No comments**: If approved comments count is zero, shows a big icon and “No comments yet” message.
- **No related posts**: The entire related posts section is hidden if the query returns empty.
- **No categories/tags available**: In the form partial, the checkbox loops display “No categories available.” or “No tags available.”.

### Validation Errors
- Each form input gets `.is-invalid` and a feedback message on error.
- Categories and tags validation errors appear as small red text beneath the groups.
- JavaScript client‑side validation: excerpt character limit (500) and comment body (1000) add `.is-invalid` on overflow.

### Permission-Denied Handling
- The “Create Post” button in the index empty state only appears for authenticated users with roles Admin/Editor/Author.
- The edit button on `show.blade.php` is only rendered for admins and editors (not for authors, so authors cannot edit from the public view even if they have policy ability? This might be inconsistent; but it’s what the code does).
- The “Publish” option in the form and the “Publish Now” button are controlled by `@can('publish posts')`.
- Users without the `create` ability cannot reach the create page (guarded by controller and route middleware).
- The `layouts.app` navbar hides the “Create Post” link behind `@can('create', App\Models\Post::class)`.

### Draft vs Published Handling
- The form partial shows the `status` dropdown. “Published” option is present only for users with `publish posts` permission.
- When editing a draft (`$post->status !== 'published'`), an additional “Publish Now” button is rendered that submits the form with `status=published`.
- Index page typically shows only published posts (controller responsibility); this is not reflected in Blade beyond assuming `$posts` are already filtered.

### Image Upload Previews
- On edit form, current featured image is shown with a thumbnail (using `thumb` variant). If the file does not exist on disk, “(File not found on disk)” is displayed.
- Uploading a new image hides the current image preview via JavaScript.
- Checkbox “Delete this image” adds a visual opacity and disables the file input when checked.
- Preview fails gracefully if file size or type validation fails (alert and clears input).

### Mobile Sidebar Behaviour
- Sidebar (`#sidebarWrapper`) is hidden on mobile and toggled by a floating button. Clicking outside closes it.
- The toggle button shows chevron‑right/left icons appropriately.
- The inline script (or stacked script) ensures identical functionality on both index and show pages.

### Search Interaction
- Search term persists in the input via `value="{{ request('search') }}"`.
- Empty search results are handled by the same empty state as no posts.

### Flash Messages Dismissal
- Each flash card has a close button that removes the card from the DOM on click (no auto-dismiss timer).

---

```