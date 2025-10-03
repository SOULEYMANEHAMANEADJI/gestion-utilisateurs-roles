<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:performance
                           {--save : Sauvegarder le rapport dans un fichier}
                           {--email= : Envoyer le rapport par email}';

    /**
     * The console command description.
     */
    protected $description = 'Monitorer les performances du système de gestion des utilisateurs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📊 MONITORING PERFORMANCE - Système de Gestion');
        $this->line('════════════════════════════════════════════════');

        $startTime = microtime(true);

        // Collecter les métriques
        $metrics = $this->collectMetrics();

        // Afficher le rapport
        $this->displayReport($metrics);

        // Analyser les performances
        $this->analyzePerformance($metrics);

        // Sauvegarder si demandé
        if ($this->option('save')) {
            $this->saveReport($metrics);
        }

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $this->info("⏱️  Temps d'exécution: {$executionTime}ms");

        return self::SUCCESS;
    }

    private function collectMetrics(): array
    {
        $this->line('🔍 Collecte des métriques...');

        return [
            'database' => $this->getDatabaseMetrics(),
            'users' => $this->getUserMetrics(),
            'roles' => $this->getRoleMetrics(),
            'cache' => $this->getCacheMetrics(),
            'performance' => $this->getPerformanceMetrics(),
            'security' => $this->getSecurityMetrics(),
            'system' => $this->getSystemMetrics(),
            'timestamp' => now()
        ];
    }

    private function getDatabaseMetrics(): array
    {
        $dbStart = microtime(true);

        $metrics = [
            'connection_time' => 0,
            'total_queries' => 0,
            'slow_queries' => 0,
            'table_sizes' => [],
            'indexes' => []
        ];

        try {
            // Test de connexion
            DB::connection()->getPdo();
            $metrics['connection_time'] = round((microtime(true) - $dbStart) * 1000, 2);

            // Tailles des tables
            $tables = ['users', 'roles', 'role_user'];
            foreach ($tables as $table) {
                $size = DB::table($table)->count();
                $metrics['table_sizes'][$table] = $size;
            }

            // Vérifier les index
            $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name != 'PRIMARY'");
            $metrics['indexes']['users'] = count($indexes);

        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }

        return $metrics;
    }

    private function getUserMetrics(): array
    {
        $queryStart = microtime(true);

        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'inactive_users' => User::where('status', 'inactive')->count(),
            'archived_users' => User::where('status', 'archived')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'avg_roles_per_user' => 0,
            'query_time' => 0
        ];

        // Calculer la moyenne de rôles par utilisateur
        $usersWithRoles = User::withCount('roles')->get();
        if ($usersWithRoles->count() > 0) {
            $metrics['avg_roles_per_user'] = round($usersWithRoles->avg('roles_count'), 2);
        }

        $metrics['query_time'] = round((microtime(true) - $queryStart) * 1000, 2);

        return $metrics;
    }

    private function getRoleMetrics(): array
    {
        $queryStart = microtime(true);

        $metrics = [
            'total_roles' => Role::count(),
            'roles_with_users' => Role::has('users')->count(),
            'default_roles' => Role::where('is_default', true)->count(),
            'roles_with_permissions' => Role::whereNotNull('permissions')
                ->where('permissions', '!=', '[]')->count(),
            'avg_users_per_role' => 0,
            'query_time' => 0
        ];

        // Calculer la moyenne d'utilisateurs par rôle
        $rolesWithUsers = Role::withCount('users')->get();
        if ($rolesWithUsers->count() > 0) {
            $metrics['avg_users_per_role'] = round($rolesWithUsers->avg('users_count'), 2);
        }

        $metrics['query_time'] = round((microtime(true) - $queryStart) * 1000, 2);

        return $metrics;
    }

    private function getCacheMetrics(): array
    {
        $metrics = [
            'cache_driver' => config('cache.default'),
            'cache_hits' => 0,
            'cache_misses' => 0,
            'cache_size' => 0
        ];

        try {
            // Test du cache
            $testKey = 'performance_test_' . time();
            Cache::put($testKey, 'test_value', 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $metrics['cache_working'] = ($retrieved === 'test_value');
        } catch (\Exception $e) {
            $metrics['cache_error'] = $e->getMessage();
        }

        return $metrics;
    }

    private function getPerformanceMetrics(): array
    {
        $metrics = [
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'load_average' => null
        ];

        // Load average (Linux/Mac uniquement)
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $metrics['load_average'] = $load ? round($load[0], 2) : null;
        }

        return $metrics;
    }

    private function getSecurityMetrics(): array
    {
        return [
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
            'users_with_weak_passwords' => $this->countWeakPasswords(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'super_admins_count' => User::whereHas('roles', function($query) {
                $query->where('name', 'super_admin');
            })->count(),
            'inactive_admin_sessions' => 0 // Placeholder
        ];
    }

    private function getSystemMetrics(): array
    {
        $diskUsage = disk_free_space('/') !== false ?
            round((disk_total_space('/') - disk_free_space('/')) / disk_total_space('/') * 100, 2) :
            null;

        return [
            'disk_usage_percent' => $diskUsage,
            'storage_logs_size' => $this->getDirectorySize(storage_path('logs')),
            'uptime' => $this->getSystemUptime(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale')
        ];
    }

    private function displayReport(array $metrics): void
    {
        $this->line('');
        $this->info('📈 RAPPORT DE PERFORMANCE');
        $this->line('═══════════════════════════');

        // Base de données
        $this->line('🗄️  BASE DE DONNÉES');
        $db = $metrics['database'];
        $this->line("   Connexion: {$db['connection_time']}ms");
        foreach ($db['table_sizes'] as $table => $size) {
            $this->line("   Table {$table}: {$size} enregistrements");
        }

        // Utilisateurs
        $this->line('');
        $this->line('👥 UTILISATEURS');
        $users = $metrics['users'];
        $this->line("   Total: {$users['total_users']}");
        $this->line("   Actifs: {$users['active_users']}");
        $this->line("   Inactifs: {$users['inactive_users']}");
        $this->line("   Archivés: {$users['archived_users']}");
        $this->line("   Sans rôles: {$users['users_without_roles']}");
        $this->line("   Rôles/utilisateur (moy.): {$users['avg_roles_per_user']}");
        $this->line("   Temps de requête: {$users['query_time']}ms");

        // Rôles
        $this->line('');
        $this->line('🎭 RÔLES');
        $roles = $metrics['roles'];
        $this->line("   Total: {$roles['total_roles']}");
        $this->line("   Avec utilisateurs: {$roles['roles_with_users']}");
        $this->line("   Avec permissions: {$roles['roles_with_permissions']}");
        $this->line("   Utilisateurs/rôle (moy.): {$roles['avg_users_per_role']}");

        // Performance
        $this->line('');
        $this->line('⚡ PERFORMANCE');
        $perf = $metrics['performance'];
        $this->line("   Mémoire utilisée: {$perf['memory_usage']} MB");
        $this->line("   Pic mémoire: {$perf['memory_peak']} MB");
        $this->line("   PHP: {$perf['php_version']}");
        $this->line("   Laravel: {$perf['laravel_version']}");

        // Sécurité
        $this->line('');
        $this->line('🔒 SÉCURITÉ');
        $security = $metrics['security'];
        $debugStatus = $security['app_debug'] ? '⚠️  ACTIVÉ' : '✅ DÉSACTIVÉ';
        $this->line("   Mode DEBUG: {$debugStatus}");
        $this->line("   Environnement: {$security['app_env']}");
        $this->line("   Super admins: {$security['super_admins_count']}");
        $this->line("   Emails non vérifiés: {$security['unverified_users']}");
    }

    private function analyzePerformance(array $metrics): void
    {
        $this->line('');
        $this->info('🎯 ANALYSE ET RECOMMANDATIONS');
        $this->line('════════════════════════════════');

        $issues = [];
        $recommendations = [];

        // Analyse de la base de données
        if ($metrics['database']['connection_time'] > 100) {
            $issues[] = "Connexion DB lente ({$metrics['database']['connection_time']}ms)";
            $recommendations[] = "Optimiser la configuration DB ou vérifier la latence réseau";
        }

        // Analyse des utilisateurs
        if ($metrics['users']['users_without_roles'] > 0) {
            $issues[] = "{$metrics['users']['users_without_roles']} utilisateur(s) sans rôle";
            $recommendations[] = "Assigner des rôles par défaut aux nouveaux utilisateurs";
        }

        // Analyse de la sécurité
        if ($metrics['security']['app_debug'] && $metrics['security']['app_env'] === 'production') {
            $issues[] = "Mode DEBUG activé en production - CRITIQUE";
            $recommendations[] = "Désactiver immédiatement APP_DEBUG=false";
        }

        if ($metrics['security']['super_admins_count'] < 2) {
            $issues[] = "Un seul Super Admin - Risque de verrouillage";
            $recommendations[] = "Créer au moins 2 comptes Super Admin";
        }

        // Analyse de la performance
        if ($metrics['performance']['memory_peak'] > 128) {
            $issues[] = "Consommation mémoire élevée ({$metrics['performance']['memory_peak']} MB)";
            $recommendations[] = "Optimiser les requêtes et utiliser la pagination";
        }

        // Afficher les résultats
        if (!empty($issues)) {
            $this->error('❌ PROBLÈMES DÉTECTÉS :');
            foreach ($issues as $issue) {
                $this->error("  • {$issue}");
            }
        }

        if (!empty($recommendations)) {
            $this->warn('💡 RECOMMANDATIONS :');
            foreach ($recommendations as $recommendation) {
                $this->warn("  • {$recommendation}");
            }
        }

        if (empty($issues) && empty($recommendations)) {
            $this->info('🎉 Performances optimales ! Aucun problème détecté.');
        }
    }

    private function saveReport(array $metrics): void
    {
        $filename = 'performance_report_' . now()->format('Y-m-d_H-i-s') . '.json';
        $filepath = storage_path("logs/{$filename}");

        file_put_contents($filepath, json_encode($metrics, JSON_PRETTY_PRINT));

        $this->info("💾 Rapport sauvegardé : {$filepath}");
    }

    private function countWeakPasswords(): int
    {
        // Compter les mots de passe potentiellement faibles (< 60 chars = non bcrypt)
        return User::whereRaw('LENGTH(password) < 60')->count();
    }

    private function getDirectorySize(string $directory): string
    {
        if (!is_dir($directory)) {
            return '0 B';
        }

        $size = 0;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $this->formatBytes($size);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function getSystemUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime !== false) {
                $seconds = (int) explode(' ', $uptime)[0];
                return gmdate('H:i:s', $seconds);
            }
        }
        return null;
    }
}
