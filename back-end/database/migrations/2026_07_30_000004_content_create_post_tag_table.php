<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_post_tag', function (Blueprint $table) {
            $table->uuid('post_id');
            $table->uuid('tag_id');
            $table->primary(['post_id', 'tag_id']);
            $table->foreign('post_id')->references('id')->on('content_posts')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('content_tags')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_post_tag');
    }
};