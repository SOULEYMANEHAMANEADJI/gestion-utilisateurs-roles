<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NewUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'phone' => '+33 1 23 45 67 89',
                'address' => '123 Rue de la Paix, 75001 Paris',
                'birth_date' => '1980-01-15',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(1),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'John Admin',
                'password' => Hash::make('password123'),
                'phone' => '+33 1 23 45 67 90',
                'address' => '456 Avenue des Champs, 75008 Paris',
                'birth_date' => '1985-05-20',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(3),
            ]
        );
        $admin->assignRole('admin');

        // Moderator
        $moderator = User::updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Jane Moderator',
                'password' => Hash::make('password123'),
                'phone' => '+33 1 23 45 67 91',
                'address' => '789 Boulevard Saint-Germain, 75006 Paris',
                'birth_date' => '1990-08-10',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subHours(6),
            ]
        );
        $moderator->assignRole('moderator');

        // Editor
        $editor = User::updateOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Bob Editor',
                'password' => Hash::make('password123'),
                'phone' => '+33 1 23 45 67 92',
                'address' => '321 Rue de Rivoli, 75004 Paris',
                'birth_date' => '1992-12-03',
                'status' => 'active',
                'email_verified_at' => now(),
                'last_login_at' => now()->subDays(2),
            ]
        );
        $editor->assignRole('editor');

        // Users normaux
        $users = [
            [
                'name' => 'Alice Dupont',
                'email' => 'alice@example.com',
                'phone' => '+33 1 23 45 67 93',
                'birth_date' => '1995-03-15',
            ],
            [
                'name' => 'Pierre Martin',
                'email' => 'pierre@example.com',
                'phone' => '+33 1 23 45 67 94',
                'birth_date' => '1988-07-22',
            ],
            [
                'name' => 'Sophie Durand',
                'email' => 'sophie@example.com',
                'phone' => '+33 1 23 45 67 95',
                'birth_date' => '1993-11-08',
            ],
            [
                'name' => 'Marc Rousseau',
                'email' => 'marc@example.com',
                'phone' => '+33 1 23 45 67 96',
                'birth_date' => '1987-04-12',
                'status' => 'inactive',
            ],
            [
                'name' => 'Emma Leroy',
                'email' => 'emma@example.com',
                'phone' => '+33 1 23 45 67 97',
                'birth_date' => '1996-09-18',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, [
                    'password' => Hash::make('password123'),
                    'address' => '123 Rue Example, 75000 Paris',
                    'status' => $userData['status'] ?? 'active',
                    'email_verified_at' => now(),
                    'last_login_at' => now()->subDays(rand(1, 30)),
                ])
            );
            $user->assignRole('user');
        }

        // Invités
        $guests = [
            [
                'name' => 'Guest User 1',
                'email' => 'guest1@example.com',
            ],
            [
                'name' => 'Guest User 2',
                'email' => 'guest2@example.com',
            ],
        ];

        foreach ($guests as $guestData) {
            $guest = User::updateOrCreate(
                ['email' => $guestData['email']],
                array_merge($guestData, [
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ])
            );
            $guest->assignRole('guest');
        }
    }
}