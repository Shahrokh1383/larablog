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
            
            // Base relational indexes
            $table->index('post_id');
            $table->index('parent_id');
            
            // PERFORMANCE INDEXES optimized for Dashboard aggregations & pagination
            $table->index(['user_id', 'created_at'], 'idx_user_created_at'); // For paginated user comments
            $table->index(['user_id', 'is_approved'], 'idx_user_approved'); // For total/wkly comment counts
            $table->index(['post_id', 'is_approved'], 'idx_post_approved'); // For post comment counts
            $table->index(['created_at', 'is_approved', 'user_id'], 'idx_created_approved_user'); // For Weekly Top Commenters
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_comments');
    }
};