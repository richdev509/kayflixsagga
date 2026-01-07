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
        Schema::create('view_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('video_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('series_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('episode_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_watched')->default(0)->comment('Duration in seconds');
            $table->boolean('completed')->default(false)->comment('90%+ watched');
            $table->string('device_type')->nullable()->comment('mobile, tablet, desktop');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Indexes pour performance
            $table->index('user_id');
            $table->index('video_id');
            $table->index('series_id');
            $table->index('episode_id');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_analytics');
    }
};
