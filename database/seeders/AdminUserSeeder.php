<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les rôles s'ils n'existent pas
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet au système',
                'color' => '#DC2626',
                'level' => 100,
                'permissions' => ['*'],
                'is_default' => false
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrateur',
                'description' => 'Gestion des utilisateurs et rôles',
                'color' => '#2563EB',
                'level' => 80,
                'permissions' => ['users.*', 'roles.*'],
                'is_default' => false
            ]
        );

        // Créer l'utilisateur super admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'phone' => '+33123456789',
                'address' => '123 Admin Street, Paris, France'
            ]
        );

        // Assigner le rôle super admin
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // Créer un utilisateur admin de test
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
                'phone' => '+33987654321',
                'address' => '456 Test Avenue, Lyon, France'
            ]
        );

        // Assigner le rôle admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $this->command->info('Utilisateurs admin créés avec succès !');
        $this->command->info('Super Admin: superadmin@example.com / password');
        $this->command->info('Admin Test: admin@test.com / password');
    }
}
