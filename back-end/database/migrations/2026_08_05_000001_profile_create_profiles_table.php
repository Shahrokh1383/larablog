<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('expertise')->nullable();
            $table->unsignedTinyInteger('years_of_experience')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};