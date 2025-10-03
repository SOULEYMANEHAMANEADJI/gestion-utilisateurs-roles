<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemDiagnosticCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:diagnostic
                           {--detailed : Afficher des informations détaillées}
                           {--fix : Corriger automatiquement les problèmes détectés}';

    /**
     * The console command description.
     */
    protected $description = 'Diagnostiquer l\'état du système de gestion des utilisateurs et rôles';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 DIAGNOSTIC SYSTÈME - Gestion des Utilisateurs et Rôles');
        $this->line('═══════════════════════════════════════════════════════════');

        $issues = [];
        $warnings = [];

        // 1. Vérification de la base de données
        $this->checkDatabase($issues, $warnings);

        // 2. Vérification des rôles
        $this->checkRoles($issues, $warnings);

        // 3. Vérification des utilisateurs
        $this->checkUsers($issues, $warnings);

        // 4. Vérification des permissions
        $this->checkPermissions($issues, $warnings);

        // 5. Vérification de la sécurité
        $this->checkSecurity($issues, $warnings);

        // Affichage du résumé
        $this->displaySummary($issues, $warnings);

        // Correction automatique si demandée
        if ($this->option('fix') && !empty($issues)) {
            $this->attemptAutoFix($issues);
        }

        return empty($issues) ? self::SUCCESS : self::FAILURE;
    }

    private function checkDatabase(array &$issues, array &$warnings): void
    {
        $this->line('📊 Vérification de la base de données...');

        // Vérifier les tables principales
        $requiredTables = ['users', 'roles', 'role_user'];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $issues[] = "Table manquante : {$table}";
            } else {
                $this->info("  ✅ Table {$table} existe");
            }
        }

        // Vérifier la connexion DB
        try {
            DB::connection()->getPdo();
            $this->info("  ✅ Connexion à la base de données OK");
        } catch (\Exception $e) {
            $issues[] = "Erreur de connexion DB : " . $e->getMessage();
        }
    }

    private function checkRoles(array &$issues, array &$warnings): void
    {
        $this->line('🎭 Vérification des rôles...');

        $totalRoles = Role::count();
        $this->info("  📈 {$totalRoles} rôle(s) trouvé(s)");

        // Vérifier les rôles système essentiels
        $systemRoles = ['super_admin', 'admin', 'user'];
        foreach ($systemRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                $issues[] = "Rôle système manquant : {$roleName}";
            } else {
                $this->info("  ✅ Rôle système {$roleName} présent (niveau {$role->level})");
            }
        }

        // Vérifier les rôles sans utilisateurs
        $emptyRoles = Role::doesntHave('users')->count();
        if ($emptyRoles > 0) {
            $warnings[] = "{$emptyRoles} rôle(s) sans utilisateur assigné";
        }

        // Vérifier les niveaux de rôles
        $rolesWithoutLevel = Role::whereNull('level')->orWhere('level', 0)->count();
        if ($rolesWithoutLevel > 0) {
            $warnings[] = "{$rolesWithoutLevel} rôle(s) sans niveau défini";
        }
    }

    private function checkUsers(array &$issues, array &$warnings): void
    {
        $this->line('👥 Vérification des utilisateurs...');

        $totalUsers = User::count();
        $this->info("  📈 {$totalUsers} utilisateur(s) trouvé(s)");

        // Vérifier la présence d'un super admin
        $superAdmins = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->count();

        if ($superAdmins === 0) {
            $issues[] = "Aucun Super Administrateur trouvé dans le système";
        } else {
            $this->info("  ✅ {$superAdmins} Super Administrateur(s) présent(s)");
        }

        // Vérifier les utilisateurs sans rôles
        $usersWithoutRoles = User::doesntHave('roles')->count();
        if ($usersWithoutRoles > 0) {
            $warnings[] = "{$usersWithoutRoles} utilisateur(s) sans rôle assigné";
        }

        // Statistiques par statut
        $stats = [
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'archived' => User::where('status', 'archived')->count(),
        ];

        foreach ($stats as $status => $count) {
            if ($count > 0) {
                $this->info("  📊 {$count} utilisateur(s) {$status}");
            }
        }

        // Vérifier les emails en doublon
        $duplicateEmails = User::select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($duplicateEmails > 0) {
            $issues[] = "{$duplicateEmails} adresse(s) email en doublon détectée(s)";
        }
    }

    private function checkPermissions(array &$issues, array &$warnings): void
    {
        $this->line('🔐 Vérification des permissions...');

        // Vérifier les rôles avec permissions
        $rolesWithPermissions = Role::whereNotNull('permissions')
            ->where('permissions', '!=', '[]')
            ->count();

        $this->info("  📋 {$rolesWithPermissions} rôle(s) avec permissions définies");

        // Vérifier la hiérarchie des niveaux
        $hierarchyIssues = Role::where('level', '>=', 100)
            ->where('name', '!=', 'super_admin')
            ->count();

        if ($hierarchyIssues > 0) {
            $warnings[] = "{$hierarchyIssues} rôle(s) avec niveau >= 100 (réservé au super_admin)";
        }
    }

    private function checkSecurity(array &$issues, array &$warnings): void
    {
        $this->line('🛡️ Vérification de la sécurité...');

        // Vérifier les utilisateurs avec mots de passe faibles (longueur)
        $weakPasswords = User::whereRaw('LENGTH(password) < 60')->count(); // bcrypt fait ~60 chars
        if ($weakPasswords > 0) {
            $warnings[] = "{$weakPasswords} utilisateur(s) avec mot de passe potentiellement faible";
        }

        // Vérifier les utilisateurs non vérifiés
        $unverifiedUsers = User::whereNull('email_verified_at')->count();
        if ($unverifiedUsers > 0) {
            $warnings[] = "{$unverifiedUsers} utilisateur(s) avec email non vérifié";
        }

        // Vérifier la configuration de sécurité
        if (config('app.debug') && app()->environment('production')) {
            $issues[] = "Mode DEBUG activé en production - RISQUE DE SÉCURITÉ";
        }
    }

    private function displaySummary(array $issues, array $warnings): void
    {
        $this->line('');
        $this->line('📋 RÉSUMÉ DU DIAGNOSTIC');
        $this->line('═══════════════════════');

        if (empty($issues) && empty($warnings)) {
            $this->info('🎉 Aucun problème détecté ! Votre système est en parfait état.');
            return;
        }

        if (!empty($issues)) {
            $this->error('❌ PROBLÈMES CRITIQUES :');
            foreach ($issues as $issue) {
                $this->error("  • {$issue}");
            }
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  AVERTISSEMENTS :');
            foreach ($warnings as $warning) {
                $this->warn("  • {$warning}");
            }
        }

        $this->line('');
        $this->info('💡 Utilisez --fix pour tenter une correction automatique des problèmes.');
    }

    private function attemptAutoFix(array $issues): void
    {
        $this->line('');
        $this->info('🔧 Tentative de correction automatique...');

        foreach ($issues as $issue) {
            if (str_contains($issue, 'Aucun Super Administrateur')) {
                $this->warn('  ⚠️  Correction manuelle requise : Créez un super admin avec php artisan user:create-super-admin');
            } elseif (str_contains($issue, 'Rôle système manquant')) {
                $this->warn('  ⚠️  Correction manuelle requise : Exécutez php artisan db:seed --class=ProductionReadySeeder');
            } else {
                $this->warn("  ⚠️  Correction manuelle requise pour : {$issue}");
            }
        }
    }
}
