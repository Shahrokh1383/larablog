<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('call_us_phone')->nullable();
            $table->json('call_us_emails')->nullable();
            $table->text('visit_address')->nullable();
            $table->json('social_links')->nullable();
            $table->string('story_image')->nullable();
            $table->timestamps();
        });

        // Insert a single default row to act as the settings singleton
        DB::table('about_site_settings')->insert([
            'call_us_phone' => null,
            'call_us_emails' => '[]',
            'visit_address' => null,
            'social_links' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('about_site_settings');
    }
};