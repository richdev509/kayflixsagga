<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            // Remplacer vimeo_video_id par bunny_video_id
            $table->dropColumn('vimeo_video_id');
            $table->string('bunny_video_id')->nullable()->after('creator_id');

            // Remplacer thumbnail par thumbnail_url
            $table->dropColumn('thumbnail');
            $table->string('thumbnail_url')->nullable()->after('description');

            // Ajouter is_published
            $table->boolean('is_published')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['bunny_video_id', 'thumbnail_url', 'is_published']);
            $table->string('vimeo_video_id')->unique()->after('creator_id');
            $table->string('thumbnail')->nullable()->after('description');
        });
    }
};
