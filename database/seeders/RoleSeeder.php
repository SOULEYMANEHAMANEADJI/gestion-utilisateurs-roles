<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet à toutes les fonctionnalités du système',
                'color' => '#DC2626',
                'level' => 100,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit', 'users.delete',
                    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
                    'system.settings', 'system.backup', 'system.logs'
                ],
                'is_default' => false,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Gestion des utilisateurs et rôles',
                'color' => '#DC2626',
                'level' => 80,
                'permissions' => [
                    'users.view', 'users.create', 'users.edit',
                    'roles.view', 'roles.create', 'roles.edit'
                ],
                'is_default' => false,
            ],
            [
                'name' => 'moderator',
                'display_name' => 'Modérateur',
                'description' => 'Modération du contenu et support utilisateur',
                'color' => '#F59E0B',
                'level' => 60,
                'permissions' => [
                    'users.view', 'users.edit',
                    'content.moderate', 'support.tickets'
                ],
                'is_default' => false,
            ],
            [
                'name' => 'editor',
                'display_name' => 'Éditeur',
                'description' => 'Création et édition de contenu',
                'color' => '#10B981',
                'level' => 40,
                'permissions' => [
                    'content.view', 'content.create', 'content.edit',
                    'media.upload'
                ],
                'is_default' => false,
            ],
            [
                'name' => 'user',
                'display_name' => 'Utilisateur',
                'description' => 'Utilisateur standard avec accès limité',
                'color' => '#6B7280',
                'level' => 20,
                'permissions' => [
                    'profile.view', 'profile.edit',
                    'content.view'
                ],
                'is_default' => true,
            ],
            [
                'name' => 'guest',
                'display_name' => 'Invité',
                'description' => 'Accès en lecture seule',
                'color' => '#9CA3AF',
                'level' => 10,
                'permissions' => [
                    'content.view'
                ],
                'is_default' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
