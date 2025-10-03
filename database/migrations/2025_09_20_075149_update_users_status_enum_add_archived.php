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
        // SQLite ne supporte pas MODIFY COLUMN pour les ENUM
        // Nous devons recréer la colonne avec les nouvelles valeurs
        if (config('database.default') === 'sqlite' || DB::connection()->getDriverName() === 'sqlite') {
            // Pour SQLite, nous devons ajouter la colonne archived_at qui sera utilisée à la place
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'archived_at')) {
                    $table->timestamp('archived_at')->nullable()->after('status');
                }
                if (!Schema::hasColumn('users', 'archived_by')) {
                    $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
                    $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        } else {
            // Pour MySQL/PostgreSQL, modifier l'enum
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'inactive', 'suspended', 'archived') DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'sqlite' || DB::connection()->getDriverName() === 'sqlite') {
            // Pour SQLite, supprimer les colonnes ajoutées
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['archived_by']);
                $table->dropColumn(['archived_at', 'archived_by']);
            });
        } else {
            // Pour MySQL/PostgreSQL, retirer 'archived' de l'enum
            DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'");
        }
    }
};
