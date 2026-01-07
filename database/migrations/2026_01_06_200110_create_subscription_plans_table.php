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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: Basic, Premium, VIP
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Prix mensuel
            $table->integer('duration_days')->default(30); // Durée en jours
            $table->json('features')->nullable(); // Fonctionnalités du plan
            $table->boolean('is_active')->default(true);
            $table->integer('max_devices')->default(1); // Nombre d'appareils simultanés
            $table->boolean('has_offline_download')->default(false);
            $table->string('video_quality')->default('HD'); // SD, HD, 4K
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->timestamps();
        });

        // Ajouter plan_id à la table subscriptions si elle existe
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreignId('plan_id')->nullable()->after('user_id')->constrained('subscription_plans')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'plan_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
                $table->dropColumn('plan_id');
            });
        }

        Schema::dropIfExists('subscription_plans');
    }
};
