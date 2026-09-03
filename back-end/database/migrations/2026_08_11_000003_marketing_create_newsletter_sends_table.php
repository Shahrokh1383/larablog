<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_newsletter_sends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscriber_id')->constrained('marketing_subscribers')->cascadeOnDelete();
            $table->uuid('run_id');
            $table->timestamp('sent_at');
            $table->unique(['subscriber_id', 'run_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_newsletter_sends');
    }
};