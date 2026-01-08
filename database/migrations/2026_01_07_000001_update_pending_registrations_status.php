<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier la colonne status pour ajouter les nouveaux statuts
        // Les statuts possibles: pending, completed, failed, expired, cancelled
        DB::statement("ALTER TABLE pending_registrations MODIFY COLUMN status VARCHAR(255) DEFAULT 'pending' COMMENT 'pending, completed, failed, expired, cancelled'");

        // Ajouter un index sur status pour les requêtes de nettoyage
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
    }
};
