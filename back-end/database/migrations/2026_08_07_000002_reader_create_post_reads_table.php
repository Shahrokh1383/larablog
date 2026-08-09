<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_post_reads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('post_id');
            $table->timestamp('read_at')->useCurrent();

            // PERFORMANCE INDEX for Dashboard queries
            $table->index(['user_id', 'read_at'], 'idx_user_read_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('content_posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_post_reads');
    }
};