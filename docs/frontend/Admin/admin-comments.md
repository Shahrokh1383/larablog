Admin Comments
markdown

# Comments Management – Frontend Documentation

## Overview
The Comments module enables `Admin` users to review, approve, and reject comments. Two views are provided: an “All Comments” list (including approved and pending) and a “Pending Comments” list. Both use AJAX-driven moderation (approve/reject) via modals. The sidebar shows a pending count badge and links to the pending list.

## File List
| File | Purpose |
|------|---------|
| `resources/views/admin/comments/index.blade.php` | All comments with approve/delete actions |
| `resources/views/admin/comments/pending.blade.php` | Pending comments with shared approve/reject modals |

## View Hierarchy & Inheritance

layouts/admin.blade.php
├── admin/comments/index.blade.php
└── admin/comments/pending.blade.php
text

Both extend `layouts.admin`.

## Data Flow (Controllers)
`CommentAdminController`:
- `index` → returns `$comments` (paginated, all comments)
- `pending` → returns `$comments` (paginated, only pending/not approved)

Each comment is a `Comment` model with:
- `id`, `body`, `parent_id`, `approved`, `created_at`  
- Relationships: `commentable` (polymorphic, e.g., `Post`) → provides `title` and `url`  
- `display_name`, `author` (nullable), `guest_email`

## Detailed Views

### 1. `admin/comments/index.blade.php`

#### Path and Purpose
Displays a table of all comments (approved and pending) with filtering by status (but not shown in this view; navigation to pending is via a separate button). Each row shows comment excerpt, linked post title, submitter info, time, status badge, and action buttons.

#### Data Dependencies
- `$comments` – paginated collection of `Comment` models.

#### Blade Directives
- `@if(session('success'))` – shows a standard `alert-success` (note: the layout also shows a floating flash, so this may duplicate)
- `@foreach($comments as $comment)`
- `@if($comment->parent_id)` – displays “Reply” indicator
- `@if($comment->approved)` – status badge (green/yellow)
- `@if(!$comment->approved)` – show “Approve” button

#### Included Partials
None.

#### Forms / Modals
- **Approve Modal** (inline per row, only if not approved)  
  - Form: `route('admin.comments.approve', $comment)`, `POST` with `@csrf`. Modal header `bg-success text-white`.  
- **Reject Modal** (inline per row, triggered by button with `onclick="submitRejectForm(...)"`)  
  - The modal itself is a standard Bootstrap modal, but the “Reject” button calls a JavaScript function that sends a `fetch` request to `/admin/comments/{id}/reject` (POST) with CSRF token. The form is not used inside the modal for reject; it’s AJAX‑driven.

#### JavaScript (`@push('scripts')`)
```javascript
function submitRejectForm(commentId) {
    fetch(`/admin/comments/${commentId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) window.location.reload();
        else alert('Failed to reject comment.');
    })
    .catch(error => { ... });
}

Conditional Rendering

    “Approve” button visible only if comment is not approved.

    Reply indicator (parent_id) shown as small text.

    Empty state shows bi-chat-square-x icon and message.

Links

    “Pending” button at top → route('admin.comments.pending')

    Post title link opens post in new tab (target="_blank").

Styling

    Card height 60vh; overflow-y: scroll; pushed via @push('styles').

    Table uses table-responsive.

2. admin/comments/pending.blade.php
Path and Purpose

Shows only pending comments with shared modals (not inline per row) to approve/reject using a dynamic action URL.
Data Dependencies

    $comments – paginated collection of pending comments only.

Blade Directives

    @foreach($comments as $comment)

    Approve/Reject buttons call setupAction(url) before triggering the modal.

Forms / Modals

    Approve Modal (shared single modal)

        Modal ID: approveModal. The button inside calls executeAction() which uses the stored currentActionUrl to send a POST request. No form submission; pure JS.

    Reject Modal (shared single modal)

        Similar structure; button calls executeAction().

JavaScript (@push('scripts'))
javascript

let currentActionUrl = '';
function setupAction(url) { currentActionUrl = url; }
function executeAction() {
    fetch(currentActionUrl, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => { if(response.ok) window.location.reload(); })
    .catch(...);
}

Conditional Rendering

    If no pending comments, a green check icon with “No pending comments! All caught up.” is shown.

Links

    “All Comments” button → route('admin.comments.index').

CSS & JavaScript Specific to Comments

    Inline styles: Table container card height and scroll behaviour, identical to categories/tags.

    JavaScript: Two variants:

        index.blade.php: submitRejectForm(commentId) – called from reject button inside an inline modal.

        pending.blade.php: setupAction(url) and executeAction() – generic modal‑based AJAX for both approve and reject.

    The scripts rely on the CSRF meta tag and fetch API. No additional libraries required.

Global admin CSS from admin.css styles the sidebar, flash messages, and general layout.
Integration with Backend
UI Action	HTTP Method	Route	Controller Method	Policy / Gate
List all comments	GET	admin.comments.index	CommentAdminController@index	Admin role (sidebar)
List pending comments	GET	admin.comments.pending	CommentAdminController@pending	Admin role
Approve comment	POST	admin.comments.approve {comment}	CommentAdminController@approve	Implicitly admin role (likely in controller)
Reject comment (delete)	POST	admin.comments.reject {comment}	CommentAdminController@reject	Implicitly admin role

Flash messages: Success messages (e.g., “Comment approved”) are flashed and displayed by the layout’s floating cards, plus a local alert-success in the index view.
Edge Cases & UI States

    Empty pending queue: Shows a positive green message.

    No comments at all: Shows a muted empty state.

    AJAX failure: Alerts the user and logs to console.

    Multiple modals: In the index view, each row has its own unique modal IDs (approveModal{{ $comment->id }}, rejectModal{{ $comment->id }}), avoiding ID conflicts.

    CSRF protection: All POST requests include the CSRF token from the meta tag.

    Page reload: After a successful AJAX action, the page reloads to reflect the updated status.

    Soft deletes: Comments are not soft‑deleted; rejection permanently deletes them (as per the warning in the UI).