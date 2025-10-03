<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class RoleHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_modify_super_admin()
    {
        // Créer les rôles
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Administrateur',
            'level' => 100,
            'color' => '#dc2626'
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'level' => 50,
            'color' => '#2563eb'
        ]);

        // Créer les utilisateurs
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
        $superAdmin->roles()->attach($superAdminRole);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
        $admin->roles()->attach($adminRole);

        // Tenter de modifier le super admin en tant qu'admin
        $this->actingAs($admin);

        $response = $this->put(route('admin.users.update', $superAdmin), [
            'name' => 'Super Admin Modifié',
            'email' => 'super@admin.com',
            'status' => 'active'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Vous n\'avez pas les permissions pour modifier cet utilisateur.');
    }

    public function test_super_admin_can_modify_admin()
    {
        // Créer les rôles
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Administrateur',
            'level' => 100,
            'color' => '#dc2626'
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'level' => 50,
            'color' => '#2563eb'
        ]);

        // Créer les utilisateurs
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
        $superAdmin->roles()->attach($superAdminRole);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
        $admin->roles()->attach($adminRole);

        // Modifier l'admin en tant que super admin
        $this->actingAs($superAdmin);

        $response = $this->put(route('admin.users.update', $admin), [
            'name' => 'Admin Modifié',
            'email' => 'admin@admin.com',
            'status' => 'active'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Utilisateur mis à jour avec succès.');
    }

    public function test_user_cannot_assign_higher_level_role()
    {
        // Créer les rôles
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Administrateur',
            'level' => 100,
            'color' => '#dc2626'
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrateur',
            'level' => 50,
            'color' => '#2563eb'
        ]);

        // Créer un admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);
        $admin->roles()->attach($adminRole);

        // Créer un utilisateur normal
        $user = User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'status' => 'active'
        ]);

        // Tenter d'assigner le rôle super_admin à l'utilisateur normal
        $this->actingAs($admin);

        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'User',
            'email' => 'user@user.com',
            'status' => 'active',
            'roles' => [$superAdminRole->id]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Vous ne pouvez pas assigner le rôle super_admin.');
    }
}
