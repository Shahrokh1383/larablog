<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_editors_pick')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('reading_time')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('content_categories')->nullOnDelete();
            $table->timestamps();

            // PERFORMANCE INDEX for Dashboard & Public feeds (Top Posts, Recent, Popular)
            $table->index(['is_published', 'published_at', 'views'], 'idx_published_date_views');
            // Index for author post listing pagination
            $table->index(['user_id', 'is_published', 'published_at'], 'idx_author_posts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_posts');
    }
};