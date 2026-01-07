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
        Schema::create('creator_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('minutes_watched', 12, 2)->default(0);
            $table->decimal('total_platform_minutes', 12, 2)->default(0);
            $table->decimal('revenue_share_percentage', 5, 2)->default(0);
            $table->decimal('amount', 10, 2)->default(0)->comment('Amount in EUR');
            $table->enum('status', ['pending', 'processing', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('creator_id');
            $table->index('status');
            $table->unique(['creator_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_payouts');
    }
};
