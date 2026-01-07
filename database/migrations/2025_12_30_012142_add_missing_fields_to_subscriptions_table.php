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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Renommer end_date en expires_at pour cohérence
            $table->renameColumn('end_date', 'expires_at');

            // Ajouter le champ plan
            $table->string('plan')->default('monthly')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('expires_at', 'end_date');
            $table->dropColumn('plan');
        });
    }
};
