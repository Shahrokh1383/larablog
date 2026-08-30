<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('engagement_comments')->nullOnDelete();

            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('body');

            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            /*
             * Index strategy — 4 composites replace the previous 6 indexes.
             *
             * Each composite's leftmost column is the foreign-key column, which
             * satisfies InnoDB's FK index requirement — standalone post_id /
             * parent_id / user_id indexes would be pure write amplification,
             * since the composite prefixes already serve every lookup.
             *
             * - (post_id, is_approved, created_at): public post feed (cursor
             *   pagination via ordered index scan — no filesort), admin
             *   per-post listing, per-post approved counts (covering).
             * - (parent_id, is_approved, created_at): reply-window preload
             *   and replies pagination (ordered scan — no filesort).
             * - (user_id, is_approved, created_at): dashboard pagination
             *   (ordered scan), weekly + lifetime user counts (prefix/range).
             * - (created_at, is_approved, user_id): covering range scan for
             *   the weekly top-commenters aggregation.
             */
            $table->index(['post_id', 'is_approved', 'created_at'], 'idx_post_approved_created_at');
            $table->index(['parent_id', 'is_approved', 'created_at'], 'idx_parent_approved_created_at');
            $table->index(['user_id', 'is_approved', 'created_at'], 'idx_user_approved_created_at');
            $table->index(['created_at', 'is_approved', 'user_id'], 'idx_created_approved_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_comments');
    }
};