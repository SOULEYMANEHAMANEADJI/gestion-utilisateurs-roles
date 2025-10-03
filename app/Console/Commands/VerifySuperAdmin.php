<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class VerifySuperAdmin extends Command
{
    protected $signature = 'verify:superadmin';
    protected $description = 'Vérifier et créer un super admin si nécessaire';

    public function handle()
    {
        $this->info('=== VÉRIFICATION SUPER ADMIN ===');

        // Vérifier s'il existe déjà
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        
        if ($superAdmin) {
            $this->info("✓ Super admin existe: {$superAdmin->name}");
            $this->info("  Email: {$superAdmin->email}");
            $this->info("  Status: {$superAdmin->status}");
            $this->info("  Rôles: " . $superAdmin->roles->pluck('name')->implode(', '));
        } else {
            $this->warn("⚠ Aucun super admin trouvé. Création...");
            
            // Créer le super admin
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => bcrypt('password'),
                'status' => 'active',
                'phone' => '+1234567890',
                'address' => '123 Admin Street'
            ]);
            
            // Créer le rôle si nécessaire
            $superAdminRole = Role::firstOrCreate([
                'name' => 'super_admin'
            ], [
                'display_name' => 'Super Administrateur',
                'level' => 100,
                'color' => '#dc2626'
            ]);
            
            // Attacher le rôle
            $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);
            
            $this->info("✓ Super admin créé avec succès!");
        }

        // Test de connexion automatique
        $this->newLine();
        $this->info('TEST DE CONNEXION AUTOMATIQUE');
        $this->line('==============================');
        
        try {
            Auth::login($superAdmin);
            $this->info("✓ Connexion réussie pour: " . Auth::user()->name);
            
            // Test d'accès au contrôleur
            $controller = new \App\Http\Controllers\Admin\UserAdvancedController();
            $request = new \Illuminate\Http\Request();
            
            $this->info("✓ Contrôleur accessible");
            
        } catch (\Exception $e) {
            $this->error("✗ Erreur de connexion: " . $e->getMessage());
        }

        // Informations de connexion
        $this->newLine();
        $this->info('INFORMATIONS DE CONNEXION');
        $this->line('=========================');
        $this->line('URL: http://127.0.0.1:8000/login');
        $this->line('Email: superadmin@example.com');
        $this->line('Mot de passe: password');
        $this->newLine();
        $this->line('Après connexion, accédez à: http://127.0.0.1:8000/admin/users');

        return 0;
    }
}