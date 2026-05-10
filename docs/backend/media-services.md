```markdown
# Media‑Services Module Documentation

## 1. Overview

The **media‑services** module is the central image‑handling pipeline for the application. It is not a traditional CRUD module with its own database tables; instead, it is a cross‑cutting utility that manages image upload, storage, thumbnail generation, and deletion for multiple domain entities: **posts** (featured images) and **users** (avatars). Category images were planned but not yet implemented.

The module is built around the `ImageService` class, which uses the **Intervention Image** library (GD driver) and Laravel’s filesystem abstraction. It supports:

- **Multiple pre‑configured thumbnail sizes** (`social`, `card`, `thumbnail`) with cropping (`cover`) or proportional scaling (`scale`).
- **Smart URL generation** on models: they request a specific size and receive the appropriate URL, falling back gracefully to the original or to a placeholder.
- **Safe file handling** during create, update, soft‑delete, and force‑delete operations.
- **Centralised logging** via a custom Monolog formatter.
- An **Artisan command** to pre‑create storage directories.

All images are stored on the `public` disk (by default `storage/app/public`) and served via the `/storage` URL prefix.

---

## 2. Configuration

### 2.1 `config/image.php`

This file controls every aspect of image processing. Below is the **expected** configuration – note the keys that are referenced in code but are **currently missing from the provided file** (indicated with `⚠️`).

```php
// config/image.php

return [
    'disk'    => env('IMAGE_DISK', 'public'),

    'quality' => (int) env('IMAGE_QUALITY', 85),

    'sizes'   => [
        'social'    => [
            'width'   => 1200,
            'height'  => 630,
            'quality' => 90,
            'fit'     => true,    // true = cover (crop), false = scale
        ],
        'card'      => [
            'width'   => 400,
            'height'  => 300,
            'quality' => 80,
            'fit'     => true,
        ],
        'thumbnail' => [
            'width'   => 150,
            'height'  => 150,
            'quality' => 75,
            'fit'     => true,
        ],
    ],

    // ⚠️ MISSING from current config, but used by PostRequest validation
    'max_upload_size' => (int) env('IMAGE_MAX_UPLOAD_SIZE', 5120), // KB

    // ⚠️ MISSING from current config, used to build allowed MIME list
    'allowed_mimes'   => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    'optimize' => true, // reserved for future optimisation
];
```

**Important:**  
- The keys `max_upload_size` and `allowed_mimes` are **not present** in the current `config/image.php`, but they are referenced in `PostRequest`. If you want to override the defaults via environment variables, you must add them to the config file.  
- The `allowed_mimes` array contains full MIME types. The `PostRequest` strips the `image/` prefix to build the `mimes` validation rule.

### 2.2 `config/filesystems.php` (relevant parts)

The `public` disk is used for all media files. It is defined as:

```php
'public' => [
    'driver'     => 'local',
    'root'       => storage_path('app/public'),
    'url'        => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw'      => false,
    'report'     => false,
],
```

A symbolic link from `public/storage` to `storage/app/public` **must** exist. If not, run:

```bash
php artisan storage:link
```

---

## 3. Directory Structure & Setup

All image files follow this pattern:

```
storage/app/public/
├── posts/
│   ├── original/       ← full‑size uploads
│   ├── social/
│   ├── card/
│   └── thumbnail/
└── users/
    ├── original/
    └── thumb/           ← avatar thumbnails (custom size key)
```

The `storage:mkdirs` Artisan command (see section 9) pre‑creates the subdirectories.  
**Note:** The command does **not** create the `original` directories because they are not listed in `config('image.sizes')`. They are created automatically by the filesystem when the first file is stored.

---

## 4. Database Schema

The module relies on two existing tables to store the relative path to the original image.

### 4.1 `posts`

| Column            | Type         | Nullable | Default | Notes |
|-------------------|--------------|----------|---------|-------|
| `featured_image`  | `VARCHAR(255)` | YES      | `NULL`  | Relative path, e.g. `posts/original/abc123.jpg` |

**Indexes:** none on this column specifically.  
**Soft Deletes:** The table has `deleted_at` (via `SoftDeletes` trait).

### 4.2 `users`

| Column   | Type         | Nullable | Default | Notes |
|----------|--------------|----------|---------|-------|
| `avatar` | `VARCHAR(255)` | YES      | `NULL`  | Relative path, e.g. `users/original/xyz.jpg` |

**Soft Deletes:** The `users` table also uses `SoftDeletes`.

---

## 5. Models

### 5.1 `Post` (`App\Models\Post`)

**Table:** `posts`  
**Primary Key:** `id`  
**Fillable:** includes `featured_image`  
**Casts:** `featured_image` is a plain string, no special casting.

#### Image‑Related Methods

- **`getImageUrlAttribute(): string`**  
  Calls `$this->getImage('original')`. Used as a convenient accessor in views (`$post->image_url`).

- **`getImage(string $size = 'original'): string`**  
  The central image‑URL resolver with a three‑step fallback:
  1. If `featured_image` is `null`, returns a placeholder URL from Lorem Picsum:  
     `https://picsum.photos/seed/{$this->id}/800/600.jpg`  
     (This is an external service; no local fallback exists.)
  2. If `$size === 'original'`, returns `asset('storage/' . $this->featured_image)`.
  3. For any other size (e.g. `'card'`), it builds the expected path by replacing `'original'` in the stored path with the requested size name.  
     Example: `posts/original/photo.jpg` → `posts/card/photo.jpg`.  
     It then checks if that file exists on the `public` disk using `Storage::disk('public')->exists($path)`.  
     - If the file exists → `asset('storage/' . $path)`
     - If not → falls back to the **original** image URL (`asset('storage/' . $this->featured_image)`).

This design allows the system to serve thumbnails that were generated after the fact, while older images with only an original still display correctly.

### 5.2 `User` (`App\Models\User`)

**Table:** `users`  
**Fillable:** includes `avatar`  
**Casts:** `avatar` is a plain string.

#### Image‑Related Methods

- **`getAvatarUrlAttribute(): string`**  
  Fallback chain:
  1. If `avatar` is empty → return Gravatar URL based on email (150px, `mp` fallback).
  2. Build a **thumbnail** path by replacing `'original'` with `'thumb'` (case‑insensitive replace):  
     `str_ireplace('original', 'thumb', $this->avatar)`
  3. Check if the thumbnail file exists on the `public` disk → if yes, return `asset('storage/' . $thumbPath)`.
  4. If thumbnail doesn’t exist, check if the **original** file exists → if yes, return that URL.
  5. If neither exists → fall back to Gravatar (step 1).

The avatar system assumes a thumbnail size key of `'thumb'` (see controller details below).

---

## 6. Controllers

All controllers receive `ImageService` via method injection (not constructor injection). They handle image operations inside database transactions where appropriate.

### 6.1 `PostController` (frontend)

**Routes:**  
- `POST   /posts`          → `store`  
- `PUT    /posts/{post}`   → `update`  
- `DELETE /posts/{post}`   → `destroy`

**Authorization:** Uses `Gate::authorize` policies (`create`, `update`, `delete`).

#### `store(PostRequest $request, ImageService $imageService)`

1. Extracts validated data (without the `featured_image` file) via `$request->getPostData()`.
2. Inside a DB transaction:
   - Creates the `Post`.
   - Syncs categories and tags.
   - If `$request->hasFile('featured_image')`:
     ```php
     $path = $imageService->storeImage(
         $request->file('featured_image'),
         'posts',
         config('image.sizes')
     );
     $post->update(['featured_image' => $path]);
     ```
   - Logs creation.
3. Returns redirect to `posts.show`.

#### `update(PostRequest $request, Post $post, ImageService $imageService)`

1. Inside a transaction:
   - Updates post fields.
   - Syncs categories/tags.
   - **Delete old image** (if checkbox `delete_image` is checked and post has an image):
     ```php
     $imageService->deleteImage($post->featured_image);
     $post->update(['featured_image' => null]);
     ```
   - **Replace image** (if new file uploaded):
     - First deletes old image (even if `delete_image` wasn’t checked, because a replacement implies removal of the old one).
     - Then calls `storeImage` and updates the column.
   - Logs update.
2. Returns redirect to `posts.show`.

#### `destroy(Post $post, ImageService $imageService)`

- **Soft‑deletes only.** Images are intentionally **not** deleted to allow restoration.
- The method was refactored to remove image deletion; the original code is commented out.
- Inside a transaction, only `$post->delete()` is called.
- Logs that the image was kept.

---

### 6.2 `Admin\PostAdminController`

**Routes:**  
- `PUT    /admin/posts/{post}`          → `update`  
- `DELETE /admin/posts/{post}`          → `destroy` (soft)  
- `DELETE /admin/posts/{id}/force-delete` → `forceDelete`  
- `POST   /admin/posts/{id}/restore`     → `restore`

**Middleware:** `auth`, `role:Admin|Editor` for all except `forceDelete` and `restore` which are under `role:Admin`.

#### `update(PostRequest $request, Post $post, ImageService $imageService)`
Delegates completely to `PostController@update` via `app()->make()`.

#### `destroy(Post $post, ImageService $imageService)`
Delegates to `PostController@destroy` (soft delete, images kept).

#### `forceDelete($id, ImageService $imageService)`
1. Retrieves the post (only from trashed).
2. Inside a transaction:
   - Detaches categories & tags.
   - **Deletes all image files**: `$imageService->deleteImage($post->featured_image)` if not null.
   - Calls `$post->forceDelete()`.
   - Logs with severity `warning`.
3. Returns redirect back.

#### `restore($id)`
Restores a soft‑deleted post. No image logic needed – the path is still in the DB and files were never removed.

---

### 6.3 `DashboardController`

**Routes:**  
- `PUT /dashboard/profile` → `updateProfile`

**Middleware:** `auth`

#### `updateProfile(DashboardProfileRequest $request, ImageService $imageService)`
1. In a transaction:
   - Updates name, email, bio.
   - If `$request->hasFile('avatar')` → calls private `handleAvatarUpload()`.
   - If `$request->boolean('delete_avatar')` and user has avatar → calls private `handleAvatarDeletion()`.

**Private helper `handleAvatarUpload` (BUG – see known issues):**
```php
// Current code
$avatarPath = $imageService->storeImage(
    $request->file('avatar'),
    'users',
    ['thumb' => [150, 150]]    // ❌ WRONG! Must be ['width' => 150, 'height' => 150]
);
```
This method also manually deletes the **old original** file via `Storage::disk('public')->delete($user->avatar)` **before** calling `storeImage`. However, it does **not** delete the old thumbnail, leaving an orphan file. The correct approach should use `$imageService->deleteImage()` for a full cleanup.

**Private helper `handleAvatarDeletion`:**
```php
$imageService->deleteImage($user->avatar);
$user->update(['avatar' => null]);
```
This correctly removes all derived files.

---

### 6.4 `AuthorController`

**Routes:**  
- `PUT /profile` → `update`

**Middleware:** `auth`, `role:Admin|Editor|Author`

#### `update(ProfileRequest $request, ImageService $imageService)`
1. Extracts validated data (excluding avatar fields).
2. Inside a transaction:
   - Updates user fields.
   - Avatar upload: same logic as `DashboardController` (with the same bug) – deletes old original manually, then calls `storeImage` with `['thumb' => [150, 150]]`.
   - Avatar deletion: uses `$imageService->deleteImage()` correctly.

---

## 7. Form Requests & Validation

### 7.1 `PostRequest`

**Authorization:** uses `Gate::allows` (`create posts` for POST, `update`/`delete` for post instance).

**Relevant validation rules:**
```php
'featured_image' => [
    'nullable', 'file', 'image',
    'mimes:' . implode(',', $this->getAllowedMimeTypes()),
    'max:' . config('image.max_upload_size', 5120),
],
'delete_image'   => ['nullable', 'boolean'],
```

`getAllowedMimeTypes()` strips the `image/` prefix from each entry in `config('image.allowed_mimes', [...default...])`.

The method `getPostData()` removes `featured_image` from the returned array so that it is not passed directly to the model.

### 7.2 `DashboardProfileRequest`

**Rules:**
```php
'avatar'        => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
'delete_avatar' => ['nullable', 'boolean'],
```

### 7.3 `ProfileRequest`

**Rules:** identical to `DashboardProfileRequest` for image fields.  
The method `getProfileData()` unsets `avatar` and `delete_avatar` for separate handling.

---

## 8. ImageService (`App\Services\ImageService`)

The core engine. Constructed with an `ImageManager` (GD driver) and a PSR‑14 logger.

### 8.1 Public Methods

#### `storeImage(UploadedFile $file, string $folder, array $sizes = []): string`

- **$folder**: base folder name (`'posts'`, `'users'`).
- **$sizes**: custom size definitions. If empty, falls back to `config('image.sizes')`.
- **Returns**: relative path to the original image (e.g. `posts/original/abc.jpg`).

**Processing steps:**
1. Generates a unique filename (8 random chars, SHA256 hash of original name + microtime + uniqid, original extension).
2. Determines disk from `config('image.disk')`.
3. Stores the original via `storeOriginal()` – uses `Storage::putFileAs()` for streaming.
4. Calls `generateThumbnails()` for each size definition.
5. Logs success.
6. Returns the original path.

#### `deleteImage(?string $path): void`

- **$path**: the full original image path. Returns early if null.
- Parses the path to extract `{$folder}` and `{$filename}`:
  - Expects format `{folder}/original/{filename}`. It pops the last segment (filename), then pops the next (`original`), and joins the remainder as folder.
- Deletes the original.
- Iterates over all `config('image.sizes')` keys and deletes the corresponding `{$folder}/{$sizeName}/{$filename}`.
- Logs the deletion.

#### `updateImage(UploadedFile $newFile, ?string $oldPath, string $folder, array $sizes = []): string`

- Combines `deleteImage($oldPath)` and `storeImage(...)`.
- **Currently not used** by any controller, but available for future code consolidation.

### 8.2 Protected Methods

#### `generateUniqueFilename(UploadedFile $file): string`
Creates names like `aB3xYz_<sha256>.jpg`.

#### `storeOriginal(...): string`
Streams the file to `{$folder}/original/{$filename}` using `putFileAs`.

#### `generateThumbnails(...)`
For each size in `$sizes`:
1. Opens the image with `ImageManager::read($file->getRealPath())`.
2. Applies transformation:
   - If `$config['fit']` is true → `cover(width, height)` (crop).
   - Else → `scale(width, height)` (keep aspect ratio, fit within bounds).
3. Sets JPEG quality from `$config['quality']` or global default.
4. Encodes to JPEG (`toJpeg(quality: $quality)`).
5. Saves to `{$folder}/{$sizeName}/{$filename}` via `Storage::put()`.
6. Catches exceptions per size and logs an error, allowing remaining sizes to be generated.

**Important:** All thumbnails are converted to JPEG regardless of source format. Transparency/animations from PNG/GIF are lost.

---

## 9. Logging

### 9.1 `ImageProcessorFormatter` (`App\Logging\ImageProcessorFormatter`)

This invokable class customises the log line format for Monolog stream handlers:

```php
"[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
```

It needs to be **registered** in the logging configuration. The typical place is in `config/logging.php` under the desired channel (e.g. `stack` or a custom `image` channel) using the `tap` option:

```php
'stack' => [
    'driver'   => 'stack',
    'channels' => ['single', 'image'],
    'tap'      => [App\Logging\ImageProcessorFormatter::class],
],
```

**Without this tap registration, the formatter has no effect.** The service itself logs using the injected `LoggerInterface`, so entries will appear in the default log file but without the custom format.

---

## 10. Artisan Commands

### `storage:mkdirs` (`CreateStorageDirectories`)

**Signature:** `storage:mkdirs`  
**Description:** Creates required storage directories for image sizes.

**Behaviour:**
- Reads the size keys from `config('image.sizes')` (e.g. `social`, `card`, `thumbnail`).
- Iterates over hardcoded base folders: `['posts', 'users', 'categories', 'tags']`.
- For each combination, creates `storage/app/public/{folder}/{sizeName}` using `File::makeDirectory()` with `0755` and `true` for recursive creation.
- Outputs `info` messages for every directory created.

**Limitation:** This command does **not** create the `original` subdirectories because that size is not part of the config array. The `original` folder will be created automatically when the first file is stored.

---

## 11. Routes Summary

| Method | URI | Controller Action | Image Operation |
|--------|-----|-------------------|-----------------|
| POST   | `/posts` | `PostController@store` | Upload featured image |
| PUT    | `/posts/{post}` | `PostController@update` | Replace/delete featured image |
| DELETE | `/posts/{post}` | `PostController@destroy` | Soft‑delete – **images kept** |
| PUT    | `/admin/posts/{post}` | `PostAdminController@update` | (delegates to PostController) |
| DELETE | `/admin/posts/{post}` | `PostAdminController@destroy` | Soft‑delete – images kept |
| DELETE | `/admin/posts/{id}/force-delete` | `PostAdminController@forceDelete` | **Permanently deletes all image files** |
| PUT    | `/dashboard/profile` | `DashboardController@updateProfile` | Upload/delete avatar |
| PUT    | `/profile` | `AuthorController@update` | Upload/delete avatar |

All routes require authentication. The admin post force‑delete route also requires the `Admin` role.

---

## 12. Integration & Usage Guide

### Displaying a Post Image
```blade
<img src="{{ $post->image_url }}" alt="...">
```
Or to request a specific size:
```blade
<img src="{{ $post->getImage('card') }}" alt="...">
```

### Displaying User Avatar
```blade
<img src="{{ $user->avatar_url }}" alt="...">
```

### Uploading a new image (in a controller)
```php
$path = $imageService->storeImage($request->file('image'), 'posts');
$post->update(['featured_image' => $path]);
```

### Deleting an image completely
```php
$imageService->deleteImage($post->featured_image);
```

### Custom thumbnail sizes
You can pass a custom sizes array to `storeImage`, but the **required format** is:
```php
[
    'size_key' => [
        'width'   => 300,
        'height'  => 300,
        'quality' => 80,
        'fit'     => true,
    ],
]
```

---

## 13. Known Issues & Important Notes

### 13.1 Critical Bug: Avatar thumbnail generation fails

**Location:** `DashboardController::handleAvatarUpload()` and `AuthorController::update()`  
**Problem:** The `$sizes` parameter is passed as `['thumb' => [150, 150]]`, which is an indexed array, not an associative array with `width` and `height` keys. The `generateThumbnails` method expects `$config['width']` and `$config['height']`.

**Result:** Uploading an avatar will throw an `ErrorException` (“Undefined array key 'width'”).

**Fix:** Change to:
```php
['thumb' => ['width' => 150, 'height' => 150, 'fit' => true]]
```

### 13.2 Orphaned avatar thumbnails on replacement

When a user uploads a new avatar, the old original is deleted manually via `Storage::delete($user->avatar)`, but the old `users/thumb/...` file is **not** cleaned up. Over time, this leaves orphaned thumbnail files on disk. Use `$imageService->deleteImage($oldPath)` instead to remove all derivatives.

### 13.3 Missing config keys `max_upload_size` and `allowed_mimes`

The `PostRequest` references `config('image.max_upload_size')` and `config('image.allowed_mimes')`. These keys are **not defined** in the current `config/image.php`. The system relies on defaults (5120 KB and a hardcoded list). To customise them via `.env`, you must add those keys to the config file.

### 13.4 `ImageService::updateImage` is unused

While the service provides a convenient `updateImage` method, controllers currently perform separate delete+store operations. This is not a bug, but developers may want to refactor to use the single method for consistency.

### 13.5 External placeholder for posts

When a post has no featured image, `getImage()` returns a URL to `picsum.photos`. This is an **external service** and requires an internet connection. There is no local fallback placeholder; a broken image will appear if the service is unreachable.

### 13.6 Thumbnail format is always JPEG

Regardless of source (PNG, WebP, GIF), all thumbnails are saved as JPEG. Transparency is lost, and animated GIFs become a single frame. If you need to preserve format, the service must be extended.

### 13.7 Logging formatter must be activated

The `ImageProcessorFormatter` will not affect log output unless it is registered in your logging channel configuration (see section 9).

---

## 14. Development & Troubleshooting Checklist

- [ ] Ensure `config/image.php` contains **all** keys referenced by validation rules.
- [ ] Run `php artisan storage:link` once per environment.
- [ ] Run `php artisan storage:mkdirs` on fresh deployments.
- [ ] Fix the avatar upload size array (see 13.1) before testing avatar uploads.
- [ ] Register `ImageProcessorFormatter` in `config/logging.php` if structured logging is desired.
- [ ] Consider adding a local fallback image for posts without a featured image.
- [ ] Review orphaned file cleanup strategy for avatar replacements.

---

```