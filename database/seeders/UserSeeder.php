<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'administrateur principal
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        // Créer des utilisateurs de test
        $users = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@example.com',
                'password' => 'password123',
                'roles' => ['author', 'editor']
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@example.com',
                'password' => 'password123',
                'roles' => ['moderator']
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@example.com',
                'password' => 'password123',
                'roles' => ['user']
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.bernard@example.com',
                'password' => 'password123',
                'roles' => ['author']
            ],
            [
                'name' => 'Lucas Moreau',
                'email' => 'lucas.moreau@example.com',
                'password' => 'password123',
                'roles' => ['editor']
            ],
            [
                'name' => 'Emma Petit',
                'email' => 'emma.petit@example.com',
                'password' => 'password123',
                'roles' => ['user']
            ],
            [
                'name' => 'Thomas Roux',
                'email' => 'thomas.roux@example.com',
                'password' => 'password123',
                'roles' => ['moderator', 'author']
            ],
            [
                'name' => 'Léa Simon',
                'email' => 'lea.simon@example.com',
                'password' => 'password123',
                'roles' => ['user']
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );
            
            if ($user->wasRecentlyCreated) {
                $user->syncRoles($userData['roles']);
            }
        }

        // Créer quelques utilisateurs non vérifiés
        $unverifiedUsers = [
            [
                'name' => 'Test User 1',
                'email' => 'test1@example.com',
                'password' => 'password123',
                'roles' => ['user']
            ],
            [
                'name' => 'Test User 2',
                'email' => 'test2@example.com',
                'password' => 'password123',
                'roles' => ['user']
            ]
        ];

        foreach ($unverifiedUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => null, // Non vérifié
                ]
            );
            
            if ($user->wasRecentlyCreated) {
                $user->syncRoles($userData['roles']);
            }
        }
    }
}
