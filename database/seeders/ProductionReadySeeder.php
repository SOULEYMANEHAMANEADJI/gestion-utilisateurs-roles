<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProductionReadySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Créer les rôles système
            $this->createSystemRoles();

            // 2. Créer les utilisateurs système
            $this->createSystemUsers();

            // 3. Créer des utilisateurs de test (optionnel)
            if (app()->environment(['local', 'staging'])) {
                $this->createTestUsers();
            }

            DB::commit();

            $this->command->info('✅ Seeding terminé avec succès !');
            $this->command->info('🔑 Super Admin: superadmin@app.com / Super123!');
            $this->command->info('🛡️ Admin: admin@app.com / Admin123!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors du seeding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer les rôles système essentiels
     */
    private function createSystemRoles(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités du système',
                'color' => '#DC2626',
                'level' => 100,
                'is_default' => false,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.export', 'users.bulk_actions',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                    'analytics.view', 'logs.view', 'settings.manage'
                ]
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Gestion des utilisateurs et des rôles (sauf super-admin)',
                'color' => '#DC2626',
                'level' => 80,
                'is_default' => false,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete', 'users.export', 'users.bulk_actions',
                    'roles.view', 'roles.create', 'roles.edit',
                    'analytics.view'
                ]
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Gestion des utilisateurs avec permissions limitées',
                'color' => '#F59E0B',
                'level' => 60,
                'is_default' => false,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.export',
                    'analytics.view'
                ]
            ],
            [
                'name' => 'author',
                'display_name' => 'Auteur',
                'description' => 'Utilisateur avec permissions de création de contenu',
                'color' => '#3B82F6',
                'level' => 40,
                'is_default' => false,
                'permissions' => ['users.view']
            ],
            [
                'name' => 'user',
                'display_name' => 'Utilisateur',
                'description' => 'Utilisateur standard avec permissions basiques',
                'color' => '#10B981',
                'level' => 20,
                'is_default' => true,
                'permissions' => ['users.view']
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );

            $this->command->info("✅ Rôle créé/mis à jour : {$role->display_name}");
        }
    }

    /**
     * Créer les utilisateurs système essentiels
     */
    private function createSystemUsers(): void
    {
        // Super Administrateur
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@app.com'],
            [
                'name' => 'Super Administrateur',
                'email' => 'superadmin@app.com',
                'password' => Hash::make('Super123!'),
                'phone' => '+33 1 23 45 67 89',
                'address' => '123 Rue de la Technologie, 75001 Paris',
                'birth_date' => '1985-01-15',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );

        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
            $superAdmin->roles()->attach($superAdminRole->id, [
                'assigned_by' => $superAdmin->id,
                'assigned_at' => now()
            ]);
        }

        // Administrateur
        $admin = User::updateOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name' => 'Administrateur',
                'email' => 'admin@app.com',
                'password' => Hash::make('Admin123!'),
                'phone' => '+33 1 23 45 67 90',
                'address' => '456 Avenue de la Gestion, 75002 Paris',
                'birth_date' => '1988-03-22',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(1),
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id, [
                'assigned_by' => $superAdmin->id,
                'assigned_at' => now()
            ]);
        }

        $this->command->info("✅ Utilisateur système créé : {$superAdmin->name}");
        $this->command->info("✅ Utilisateur système créé : {$admin->name}");
    }

    /**
     * Créer des utilisateurs de test (uniquement en développement)
     */
    private function createTestUsers(): void
    {
        $testUsers = [
            [
                'name' => 'Jean Manager',
                'email' => 'manager@test.com',
                'password' => Hash::make('Manager123!'),
                'phone' => '+33 1 98 76 54 32',
                'role' => 'manager',
                'status' => 'active'
            ],
            [
                'name' => 'Marie Auteur',
                'email' => 'author@test.com',
                'password' => Hash::make('Author123!'),
                'phone' => '+33 1 87 65 43 21',
                'role' => 'author',
                'status' => 'active'
            ],
            [
                'name' => 'Pierre Utilisateur',
                'email' => 'user@test.com',
                'password' => Hash::make('User123!'),
                'phone' => '+33 1 76 54 32 10',
                'role' => 'user',
                'status' => 'active'
            ],
            [
                'name' => 'Sophie Inactive',
                'email' => 'inactive@test.com',
                'password' => Hash::make('Inactive123!'),
                'role' => 'user',
                'status' => 'inactive'
            ]
        ];

        $superAdmin = User::where('email', 'superadmin@app.com')->first();

        foreach ($testUsers as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'address' => 'Adresse de test',
                    'birth_date' => '1990-01-01',
                    'email_verified_at' => now(),
                    'last_login_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                ])
            );

            $role = Role::where('name', $roleName)->first();
            if ($role && !$user->hasRole($roleName)) {
                $user->roles()->attach($role->id, [
                    'assigned_by' => $superAdmin->id,
                    'assigned_at' => now()
                ]);
            }

            $this->command->info("✅ Utilisateur de test créé : {$user->name} ({$roleName})");
        }

        // Créer l'utilisateur archivé APRÈS avoir créé les autres utilisateurs
        $archivedUser = User::updateOrCreate(
            ['email' => 'archived@test.com'],
            [
                'name' => 'Michel Archivé',
                'email' => 'archived@test.com',
                'password' => Hash::make('Archived123!'),
                'address' => 'Adresse de test',
                'birth_date' => '1990-01-01',
                'status' => 'archived',
                'email_verified_at' => now(),
                'last_login_at' => null,
                'archived_at' => now()->subDays(30),
                'archived_by' => $superAdmin->id, // Maintenant le super admin existe
            ]
        );

        $userRole = Role::where('name', 'user')->first();
        if ($userRole && !$archivedUser->hasRole('user')) {
            $archivedUser->roles()->attach($userRole->id, [
                'assigned_by' => $superAdmin->id,
                'assigned_at' => now()->subDays(35)
            ]);
        }

        $this->command->info("✅ Utilisateur archivé créé : {$archivedUser->name} (user - archivé)");
    }
}
