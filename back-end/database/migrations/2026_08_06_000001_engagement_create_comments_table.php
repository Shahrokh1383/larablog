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
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index('post_id');
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_comments');
    }
};