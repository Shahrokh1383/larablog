```markdown
# Services & Logging – Admin Panel Documentation

> **Related documents:**  
> - [Media Services](../media-services.md) – full image handling pipeline and configuration.  
> - [Comments System](../comments-system.md) – public & admin comment logic, policies, and events.  
> - [Authorization & Middleware](authorization-and-middleware.md) – role/permission system that guards these services.  
> - Module‑specific admin docs: [User Management](user-management.md), Content Management, Taxonomy Management, Comment Moderation, Dashboard Widgets.

---

## Table of Contents

1. [Overview](#1-overview)  
2. [ImageService in the Admin Context](#2-imageservice-in-the-admin-context)  
3. [Custom Log Formatting & the Image Processing Channel](#3-custom-log-formatting--the-image-processing-channel)  
4. [Comment Notification Pipeline (Admin Involvement)](#4-comment-notification-pipeline-admin-involvement)  
5. [Logging Practices Across Admin Controllers](#5-logging-practices-across-admin-controllers)  
6. [Configuration Summary](#6-configuration-summary)  
7. [Development & Troubleshooting Guide](#7-development--troubleshooting-guide)  

---

## 1. Overview

The admin panel relies on several shared services and logging mechanisms that are not tied to a single module:

- **ImageService** – handles upload, thumbnail generation, and deletion of images; used heavily in admin force‑delete operations.  
- **ImageProcessorFormatter** – a custom Monolog formatter that enriches log output for image‑related actions.  
- **Comment Notification System** – the `CommentPosted` event, `SendCommentNotification` listener, and `NewCommentNotification` mail/database notification. Even though notifications are sent to **post authors**, the admin panel is the trigger for manual comment approvals, which in turn fire the event.  

This document explains each service from the admin perspective, shows how they are configured, and gives developers enough detail to debug, extend, or modify the behaviour without reading the source code.

---

## 2. ImageService in the Admin Context

The `ImageService` (`app/Services/ImageService.php`) is the central image pipeline. Its full API and configuration are documented in **[Media Services](../media-services.md)**. Here we focus on **where and how the admin panel uses the service**.

### 2.1 Admin Routes That Use ImageService

| Route | Controller | Method | ImageService usage |
|-------|------------|--------|-------------------|
| `DELETE /admin/posts/{id}/force-delete` | `PostAdminController@forceDelete` | `deleteImage()` | Permanently removes all image files of a soft‑deleted post. |
| `PUT /admin/posts/{post}` | `PostAdminController@update` | (delegates to `PostController@update`) | Replace or delete featured image on post update. |
| `DELETE /admin/posts/{post}` | `PostAdminController@destroy` | (delegates to `PostController@destroy`) | Soft‑delete – **images are intentionally kept**. |

The admin panel does **not** currently upload or modify images directly via its own controllers; it re‑uses the public `PostController` logic for updates. The only admin‑exclusive image operation is **permanent deletion** during force‑delete.

#### Force‑Delete flow (`PostAdminController@forceDelete`)

```php
// Pseudocode from the actual controller
$post = Post::onlyTrashed()->findOrFail($id);
DB::transaction(function () use ($post, $imageService) {
    $post->categories()->detach();
    $post->tags()->detach();
    if ($post->featured_image) {
        $imageService->deleteImage($post->featured_image);
    }
    $post->forceDelete();
    Log::warning('Post permanently deleted', ['post_id' => $id, 'deleted_by' => auth()->id()]);
});
```

**Key point:** Before calling `forceDelete()`, the code detaches all relationships and removes the original image **plus all generated thumbnails** (social, card, thumbnail) by calling `$imageService->deleteImage(...)`. This is the only place in the admin panel that performs a **complete file cleanup**.

### 2.2 `deleteImage()` Behaviour

```php
public function deleteImage(?string $path): void
{
    if (!$path) return;
    // Parse path: expects "folder/original/filename.ext"
    $parts = explode('/', $path);
    $filename = array_pop($parts);
    $originalDir = array_pop($parts); // 'original'
    $folder = implode('/', $parts);   // e.g. 'posts'

    // Delete original
    Storage::disk(config('image.disk'))->delete($path);

    // Delete all configured thumbnails
    foreach (array_keys(config('image.sizes')) as $size) {
        Storage::disk(config('image.disk'))->delete("{$folder}/{$size}/{$filename}");
    }

    $this->logger->info('Image deleted', ['path' => $path]);
}
```

This method guarantees that all derivative files are removed, preventing orphaned thumbnails. It relies on the fact that the stored path always contains the `original` segment.

### 2.3 Known Issues (from media‑services.md)

Developers working on admin‑related image features must be aware of these bugs:

| Issue | Impact on Admin | Fix |
|-------|-----------------|-----|
| **Avatar‑size array bug** (not directly in admin, but affects dashboard/profile) | If admin modifies user avatars via profile edit, uploading a new avatar will throw an `ErrorException` because the size array is `[150,150]` instead of `['width'=>150,'height'=>150]`. | Change `storeImage` call to use associative array. |
| **Orphaned avatar thumbnails** | When an avatar is replaced, old thumbnails are not deleted. Over time, disk space grows. | Use `$imageService->deleteImage()` for the old path before storing the new one. |
| **Missing config keys** (`max_upload_size`, `allowed_mimes`) | The `PostRequest` validation references these keys, but they are not present in the default `config/image.php`. | Add the keys manually or rely on the hardcoded defaults (5120 KB, jpeg/png/gif/webp). |
| **Thumbnails always saved as JPEG** | Transparency and animations are lost. | Extend the service if PNG/GIF support is required. |

For full details and the exact code fixes, see the **[Known Issues section of Media Services](../media-services.md#13-known-issues--important-notes)**.

### 2.4 Configuration Reference

All image‑related configuration, including the `disk`, quality, sizes, and thumbnail generation settings, is described in **[media‑services.md §2](../media-services.md#2-configuration)**. The admin panel does not override any of these settings; it uses the same `ImageService` instance resolved from the service container.

---

## 3. Custom Log Formatting & the Image Processing Channel

The application includes a custom Monolog formatter designed to give image‑processing log entries a consistent, machine‑readable structure. This is especially useful when debugging image uploads, deletions, and thumbnail generation from the admin panel.

### 3.1 `ImageProcessorFormatter` Class

**File:** `app/Logging/ImageProcessorFormatter.php`

```php
namespace App\Logging;

use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class ImageProcessorFormatter
{
    public function __invoke(Logger $logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof StreamHandler) {
                $handler->setFormatter(new LineFormatter(
                    "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                    "Y-m-d H:i:s",
                    true,   // allowInlineLineBreaks
                    true    // ignoreEmptyContextAndExtra
                ));
            }
        }
    }
}
```

When this class is used as a **tap** on a log channel, it iterates over all `StreamHandler` instances and sets a `LineFormatter` with the format:  

```
[2026-05-10 14:30:00] image_processing.INFO: Image deleted {"path":"posts/original/abc.jpg"} []
```

This makes it easy to filter log entries by time, channel, or severity.

### 3.2 Channel Configuration

In `config/logging.php`, the following channel is defined:

```php
'image_processing' => [
    'driver' => 'daily',
    'path'   => storage_path('logs/image-processing.log'),
    'level'  => 'debug',
    'days'   => 14,
    'tap'    => [App\Logging\ImageProcessorFormatter::class],
],
```

**Key details:**

| Option | Value | Meaning |
|--------|-------|---------|
| `driver` | `daily` | Rotates log files daily; old logs are retained for 14 days. |
| `path` | `storage/logs/image-processing.log` | Base file name; daily rotation appends date suffix. |
| `level` | `debug` | Captures all log levels from DEBUG upwards. |
| `tap` | `[ImageProcessorFormatter::class]` | Applies the custom formatter to all stream handlers of this channel. |

**Important:** This channel is **not used by default**. The `ImageService` receives a `LoggerInterface` via dependency injection, which by default resolves to the application’s default log stack (likely the `single` channel writing to `storage/logs/laravel.log`). Therefore, image‑processing log messages appear in the main log file, **not** in `image-processing.log`, unless you explicitly tell Laravel to use the `image_processing` channel.

### 3.3 Directing ImageService Logs to the Dedicated Channel

To capture all image‑related logs in `image-processing.log` with the custom format, add a contextual binding in `App\Providers\AppServiceProvider`:

```php
// In the register() or boot() method:
$this->app->when(ImageService::class)
          ->needs(\Psr\Log\LoggerInterface::class)
          ->give(function () {
              return \Illuminate\Support\Facades\Log::channel('image_processing');
          });

// Or, if you prefer a broader binding for any logger request in the service:
$this->app->bind(ImageService::class, function ($app) {
    return new \App\Services\ImageService(
        $app->make(\Intervention\Image\ImageManager::class),
        \Illuminate\Support\Facades\Log::channel('image_processing')
    );
});
```

After this change, all calls to `$this->logger->info(...)` inside `ImageService` will end up in the daily‑rotated `image-processing.log` and will be formatted by `ImageProcessorFormatter`.

**Without this binding**, the admin force‑delete log entries (like “Image deleted”) are still written, but they are mixed with the rest of the application logs and lack the custom format.

### 3.4 Log Messages from ImageService

The service logs the following events (levels as per source code):

| Event | Log Level | Example Message |
|-------|-----------|-----------------|
| Image stored successfully | `info` | `Image stored: posts/original/abc.jpg` |
| Thumbnail generation failure for a size | `error` | `Thumbnail generation failed for size social` |
| All thumbnails generated | `info` | `Thumbnails generated for posts/original/abc.jpg` |
| Image deleted | `info` | `Image deleted: posts/original/abc.jpg` |

These messages help trace exactly what happens during an admin force‑delete or when an editor replaces a post’s featured image.

---

## 4. Comment Notification Pipeline (Admin Involvement)

The comment notification system is fully documented in **[Comments System](../comments-system.md#9-events-listeners--notifications)**. Here we explain the flow **from the admin panel’s perspective**, because the admin’s approve action is one of the two triggers that dispatch notifications to post authors.

### 4.1 When Notifications Are Triggered

| Trigger | Event Fired? | Notification Sent? |
|---------|-------------|-------------------|
| Automatic approval (user has `Admin`/`Editor` role) | ✅ `CommentPosted` is fired immediately after comment creation. | ✅ Listener sends notification to post author. |
| **Manual approval by admin** (via `CommentAdminController@approve`) | ✅ `CommentPosted` is fired **only when** the comment was previously pending and becomes approved. | ✅ Listener sends notification. |
| Double‑approval attempt (comment already approved) | ❌ The controller guards against this and does not fire the event. | ❌ No notification. |

### 4.2 Admin Approval Code (excerpt)

```php
public function approve(Comment $comment)
{
    Gate::authorize('approve', $comment);

    if ($comment->approved) {
        return redirect()->back()->with('info', 'Comment is already approved.');
    }

    $comment->approve();                        // set approved = true
    event(new \App\Events\CommentPosted($comment));  // fire the event

    Log::info('Comment approved', [
        'comment_id'   => $comment->id,
        'approved_by'  => auth()->id(),
    ]);

    return redirect()->back()->with('success', 'Comment approved!');
}
```

The `event(new CommentPosted($comment))` line is the critical bridge to the notification system. It triggers the `SendCommentNotification` listener.

### 4.3 Event, Listener & Notification Chain

**`CommentPosted` Event** (`app/Events/CommentPosted.php`)  
- Accepts a `Comment` model.  
- Immediately eager‑loads the `author` and `commentable.author` relationships to avoid N+1 queries inside the listener.

**`SendCommentNotification` Listener** (`app/Listeners/SendCommentNotification.php`)  
- Implements `ShouldQueue` → runs asynchronously when a queue worker is active.  
- Respects the master switch `config('blog.comment_notifications')`. If set to `false`, the listener exits without doing anything.  
- Skips notification if the comment author is the same as the post author (no need to notify yourself).  
- Sends a `NewCommentNotification` to the **post author** via `$post->author->notify(...)`.  
- Logs every step, making it easy to debug.

**`NewCommentNotification`** (`app/Notifications/NewCommentNotification.php`)  
- Delivers via **mail** and **database** channels.  
- The mail includes:  
  - Subject: “New Comment on Your Post: {post title}”  
  - A blockquote of the comment’s first 200 characters.  
  - Two action buttons: “View Comment” (links to the post page with `#comment-{id}`) and “Manage Comments” (links to `/admin/comments/pending`).  
- The database notification stores: `comment_id`, `post_id`, `post_title`, `commenter_name`, a truncated body, and the URL.

### 4.4 Configuration Keys Involved

All relevant settings live in `config/blog.php`:

```php
return [
    'allow_guest_comments'      => true,
    'auto_approve_roles'        => ['Admin', 'Editor'],
    'max_comment_nesting_depth' => 3,
    'comments_per_page'         => 20,
    'comment_notifications'     => true,   // Master kill‑switch
];
```

- **`comment_notifications`** – set to `false` to disable all comment‑related emails and database notifications. Useful during bulk imports or testing.  
- **`auto_approve_roles`** – determines which roles get instant approval; consequently, their comments also trigger immediate notification.

### 4.5 Logging and Debugging Notifications

The listener and controller produce the following log entries (default channel, unless overridden):

| Log | Level | Meaning |
|-----|-------|---------|
| `"Comment approved"` (with comment_id, approved_by) | `info` | Admin manually approved a comment. |
| `"Skipping notification - comment author is post author"` | `info` | Self‑comment; no notification sent. |
| `"Comment notification sent"` (with comment_id, post_id, notified_user_id) | `info` | Notification successfully dispatched. |

If notifications are not being received, check:

1. `config('blog.comment_notifications')` is `true`.  
2. The queue worker is running (`php artisan queue:work`) or you are in `sync` mode locally.  
3. The post author’s email is valid and your mail driver is correctly configured.  
4. The `SendCommentNotification` listener is registered in `EventServiceProvider` (see [Comments System §14](../comments-system.md#14-migration--provider-corrections)).

---

## 5. Logging Practices Across Admin Controllers

Beyond the dedicated image and comment logs, the admin controllers follow a consistent logging pattern that aids in debugging. Here is a summary of the most important admin‑specific log messages:

| Controller | Action | Log Level | Message & Context |
|------------|--------|-----------|-------------------|
| `PostAdminController` | `forceDelete` | `warning` | `"Post permanently deleted"` { `post_id`, `deleted_by` } |
| `CommentAdminController` | `approve` | `info` | `"Comment approved"` { `comment_id`, `approved_by` } |
| `CommentAdminController` | `reject` | `info` | `"Comment rejected"` { `comment_id`, `rejected_by` } |
| `CommentAdminController` | `destroy` | `info` | `"Comment deleted (with all descendants)"` { `comment_id`, `deleted_by` } |
| `UserAdminController` | `ban`, `unban`, `delete` | not explicitly logged in provided code? (Check actual implementation; if absent, it’s a gap) | Developers should verify logging in user management and add it if missing. |

**Note:** The `UserAdminController` is expected to log ban/unban/delete actions for auditability. If your version does not, it is recommended to add `Log::info()` entries similar to those above.

All log entries appear in the default log channel (`storage/logs/laravel.log`) unless a custom channel is bound to those controllers’ dependencies.

---

## 6. Configuration Summary

| Configuration File | Key(s) | Admin Relevance |
|--------------------|--------|-----------------|
| `config/image.php` | `disk`, `sizes`, `quality`, `max_upload_size`, `allowed_mimes` | Controls image storage and validation for force‑delete and post updates. |
| `config/logging.php` | `image_processing` channel | Defines a daily‑rotated log with custom formatting for image operations. |
| `config/blog.php` | `comment_notifications`, `auto_approve_roles`, `comments_per_page` | Master switch for notifications; determines which roles bypass moderation. |
| `.env` | `QUEUE_CONNECTION`, `LOG_CHANNEL`, `IMAGE_DISK` | Set queue driver (must be `sync` or a real queue for notifications), default log channel, and storage disk. |

---

## 7. Development & Troubleshooting Guide

### 7.1 Viewing Image‑Processing Logs

By default, image logs go to the main log. To quickly filter them:

```bash
# If logs are in laravel.log
grep "Image" storage/logs/laravel.log

# After enabling the dedicated channel (see §3.3)
tail -f storage/logs/image-processing-$(date +%Y-%m-%d).log
```

### 7.2 Testing Comment Notifications Locally

1. Ensure `QUEUE_CONNECTION=sync` in `.env` (no need for a separate worker).  
2. Verify `config('blog.comment_notifications')` is `true`.  
3. Log in as an `Admin` and approve a pending comment.  
4. Check the mail log (if using `log` mail driver) in `storage/logs/laravel.log` for the “New Comment on Your Post” email.  
5. Check the database `notifications` table; the post author should have a new record.

### 7.3 Debugging Missing Image Deletion

When an admin force‑deletes a post and the images remain on disk:

- Confirm that the post actually had a `featured_image` before deletion (the field is nullable).  
- Check the log for `"Image deleted"` entry; its presence indicates that `deleteImage()` was called.  
- If the log is missing, the `featured_image` column was likely `null` or the post was not in the trash.  
- If the log is present but files remain, verify that the filesystem disk is correctly configured and the path is relative to the disk root.

### 7.4 Common Pitfalls

| Symptom | Probable Cause | Solution |
|---------|---------------|----------|
| Thumbnails are not generated after replacing an image | `config/image.php` missing size keys, or GD driver not installed. | Verify `config/image.php` (see media‑services.md) and ensure PHP GD extension is enabled. |
| “Undefined array key 'width'” when uploading avatars | Bug in avatar upload code (hardcoded `[150,150]`). | Apply the fix described in [media‑services.md §13.1](../media-services.md#131-critical-bug-avatar-thumbnail-generation-fails). |
| `Gate::authorize('approve', $comment)` fails for Admin | `CommentPolicy` is not registered in `AuthServiceProvider`. | Add `Comment::class => CommentPolicy::class` to the `$policies` array (see [Authorization §11](authorization-and-middleware.md#11-known-issues--critical-gaps)). |
| Admin cannot force‑delete a post (403) | The `force-delete` route requires `role:Admin` middleware; user might lack the `Admin` role. | Check the user’s roles in the database; assign `Admin` via `$user->assignRole('Admin')`. |

---

> **Next steps:** After mastering the services and logging layer, refer to the individual admin module documents for specific workflows:  
> – [Comment Moderation](comment-moderation.md)  
> – [Content Management](content-management.md)  
> – [Taxonomy Management](taxonomy-management.md)  
> – [User Management](user-management.md)  
> – [Dashboard Widgets](dashboard-widgets.md)
```