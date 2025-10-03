<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les rôles s'ils n'existent pas
        $this->createRoles();
        
        // Créer les utilisateurs avec différents rôles
        $this->createUsers();
    }

    private function createRoles()
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet à tous les utilisateurs et fonctionnalités',
                'level' => 100,
                'color' => '#dc2626',
                'permissions' => json_encode(['*']),
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Gestion des utilisateurs de niveau inférieur',
                'level' => 50,
                'color' => '#2563eb',
                'permissions' => json_encode(['users.create', 'users.read', 'users.update', 'users.delete']),
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Gestion limitée selon les permissions',
                'level' => 25,
                'color' => '#7c3aed',
                'permissions' => json_encode(['users.read', 'users.update']),
                'is_default' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user',
                'display_name' => 'Utilisateur',
                'description' => 'Accès de base à la plateforme',
                'level' => 10,
                'color' => '#059669',
                'permissions' => json_encode(['profile.read', 'profile.update']),
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }

    private function createUsers()
    {
        // Créer d'abord le super admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Alexandre Dubois',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 12 34 56 78',
                'address' => '123 Avenue des Champs-Élysées, 75008 Paris',
                'birth_date' => '1985-03-15',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(2),
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ]
        );

        // Assigner le rôle super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && !$superAdmin->hasRole('super_admin')) {
            $superAdmin->roles()->attach($superAdminRole->id, [
                'assigned_by' => null, // Premier super admin
                'assigned_at' => now()
            ]);
        }

        $users = [
            // Admin
            [
                'name' => 'Marie Martin',
                'email' => 'admin.user@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 23 45 67 89',
                'address' => '456 Rue de Rivoli, 75001 Paris',
                'birth_date' => '1990-07-22',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(1),
                'created_at' => now()->subMonths(4),
                'updated_at' => now(),
                'role' => 'admin'
            ],
            // Manager
            [
                'name' => 'Pierre Durand',
                'email' => 'manager@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 34 56 78 90',
                'address' => '789 Boulevard Saint-Germain, 75006 Paris',
                'birth_date' => '1992-11-08',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subMinutes(30),
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
                'role' => 'manager'
            ],
            // User
            [
                'name' => 'Sophie Bernard',
                'email' => 'user@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 45 67 89 01',
                'address' => '321 Rue de la Paix, 75002 Paris',
                'birth_date' => '1988-05-12',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(1),
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Thomas Moreau',
                'email' => 'thomas.moreau@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 56 78 90 12',
                'address' => '654 Avenue Montaigne, 75008 Paris',
                'birth_date' => '1995-09-30',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(2),
                'created_at' => now()->subMonths(1),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Julie Petit',
                'email' => 'julie.petit@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 67 89 01 23',
                'address' => '987 Rue du Faubourg Saint-Antoine, 75011 Paris',
                'birth_date' => '1993-12-03',
                'status' => 'inactive',
                'email_verified_at' => now(),
                'last_login_at' => now()->subWeeks(2),
                'created_at' => now()->subWeeks(3),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Marc Rousseau',
                'email' => 'marc.rousseau@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 78 90 12 34',
                'address' => '147 Rue de la République, 69002 Lyon',
                'birth_date' => '1987-01-18',
                'status' => 'suspended',
                'email_verified_at' => now(),
                'last_login_at' => now()->subWeeks(4),
                'created_at' => now()->subWeeks(5),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Claire Lefebvre',
                'email' => 'claire.lefebvre@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 89 01 23 45',
                'address' => '258 Cours Mirabeau, 13100 Aix-en-Provence',
                'birth_date' => '1991-08-25',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(6),
                'created_at' => now()->subWeeks(2),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Nicolas Blanc',
                'email' => 'nicolas.blanc@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 90 12 34 56',
                'address' => '369 Place Bellecour, 69002 Lyon',
                'birth_date' => '1989-04-14',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(3),
                'created_at' => now()->subWeeks(1),
                'updated_at' => now(),
                'role' => 'user'
            ],
            [
                'name' => 'Isabelle Garcia',
                'email' => 'isabelle.garcia@example.com',
                'password' => Hash::make('password123'),
                'phone' => '+33 6 01 23 45 67',
                'address' => '741 Rue de la Soif, 35000 Rennes',
                'birth_date' => '1994-06-07',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(12),
                'created_at' => now()->subDays(5),
                'updated_at' => now(),
                'role' => 'user'
            ]
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assigner le rôle
            $role = Role::where('name', $roleName)->first();
            if ($role && !$user->hasRole($roleName)) {
                $user->roles()->attach($role->id, [
                    'assigned_by' => $superAdmin->id, // Super admin
                    'assigned_at' => now()
                ]);
            }
        }
    }
}
