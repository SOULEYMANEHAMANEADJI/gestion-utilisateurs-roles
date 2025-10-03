<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class CheckPermissions extends Command
{
    protected $signature = 'check:permissions';
    protected $description = 'Vérifier les permissions et diagnostiquer les problèmes d\'accès';

    public function handle()
    {
        $this->info('=== DIAGNOSTIC PERMISSIONS ET ACCÈS ===');

        // 1. Test des utilisateurs
        $this->newLine();
        $this->info('1. UTILISATEURS');
        $this->line('==============');
        
        $userCount = User::count();
        $this->info("✓ Total utilisateurs: {$userCount}");
        
        $superAdmins = User::whereHas('roles', function($q) {
            $q->where('name', 'super_admin');
        })->get();
        
        $this->info("✓ Super admins: " . $superAdmins->count());
        
        foreach ($superAdmins as $admin) {
            $this->line("  - {$admin->name} ({$admin->email}) - Status: {$admin->status}");
        }

        // 2. Test des rôles
        $this->newLine();
        $this->info('2. RÔLES');
        $this->line('=========');
        
        $roles = Role::all();
        $this->info("✓ Total rôles: " . $roles->count());
        
        foreach ($roles as $role) {
            $userCount = $role->users()->count();
            $this->line("  - {$role->display_name} ({$role->name}): {$userCount} utilisateurs");
        }

        // 3. Test des middlewares
        $this->newLine();
        $this->info('3. MIDDLEWARES');
        $this->line('==============');
        
        $middlewareFile = app_path('Http/Middleware/RoleMiddleware.php');
        if (file_exists($middlewareFile)) {
            $this->info("✓ RoleMiddleware existe");
        } else {
            $this->error("✗ RoleMiddleware manquant");
        }

        // 4. Test des contrôleurs
        $this->newLine();
        $this->info('4. CONTRÔLEURS');
        $this->line('==============');
        
        $controllerFile = app_path('Http/Controllers/Admin/UserAdvancedController.php');
        if (file_exists($controllerFile)) {
            $this->info("✓ UserAdvancedController existe");
        } else {
            $this->error("✗ UserAdvancedController manquant");
        }

        // 5. Test des vues
        $this->newLine();
        $this->info('5. VUES');
        $this->line('=======');
        
        $viewFile = resource_path('views/admin/users/advanced-index.blade.php');
        if (file_exists($viewFile)) {
            $this->info("✓ Vue advanced-index existe");
        } else {
            $this->error("✗ Vue advanced-index manquante");
        }
        
        $layoutFile = resource_path('views/layouts/app.blade.php');
        if (file_exists($layoutFile)) {
            $this->info("✓ Layout app existe");
        } else {
            $this->error("✗ Layout app manquant");
        }

        // 6. Test de simulation de contrôleur
        $this->newLine();
        $this->info('6. SIMULATION CONTRÔLEUR');
        $this->line('========================');
        
        try {
            $query = User::with('roles');
            $users = $query->paginate(15);
            
            $this->info("✓ Requête utilisateurs: " . $users->count() . " résultats");
            $this->info("✓ Total pagination: " . $users->total());
            
            if ($users->count() > 0) {
                $firstUser = $users->first();
                $this->line("✓ Premier utilisateur: {$firstUser->name}");
                $this->line("✓ Rôles du premier: " . $firstUser->roles->count());
            }
            
        } catch (\Exception $e) {
            $this->error("✗ Erreur simulation: " . $e->getMessage());
        }

        // 7. Recommandations
        $this->newLine();
        $this->info('7. RECOMMANDATIONS');
        $this->line('==================');
        
        if ($userCount == 0) {
            $this->warn("⚠ Aucun utilisateur en base - Exécutez: php artisan db:seed");
        }
        
        if ($superAdmins->count() == 0) {
            $this->warn("⚠ Aucun super admin - Créez un compte admin");
        }
        
        $this->newLine();
        $this->info('URLS DE TEST:');
        $this->line('http://127.0.0.1:8000/test-direct - Test sans authentification');
        $this->line('http://127.0.0.1:8000/test-auth-users - Connexion automatique');
        $this->line('http://127.0.0.1:8000/login - Page de connexion');

        return 0;
    }
}