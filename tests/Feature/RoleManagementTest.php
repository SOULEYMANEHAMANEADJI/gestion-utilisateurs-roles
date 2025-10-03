<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les rôles de base
        $adminRole = Role::create([
            'name' => 'admin', 
            'display_name' => 'Administrateur',
            'description' => 'Administrateur',
            'level' => 80,
            'color' => '#DC2626'
        ]);
        
        // Créer un utilisateur admin pour les tests
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Assigner le rôle admin
        $this->admin->roles()->attach($adminRole->id, [
            'assigned_by' => null,
            'assigned_at' => now(),
        ]);
    }

    #[Test]
    public function admin_can_view_roles_list()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get(route('admin.roles.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.roles.index');
    }

    #[Test]
    public function admin_can_create_new_role()
    {
        $this->actingAs($this->admin);
        
        $roleData = [
            'name' => 'moderator',
            'display_name' => 'Modérateur',
            'description' => 'Modérateur du système',
            'level' => 60,
            'color' => '#F59E0B'
        ];
        
        $response = $this->post(route('admin.roles.store'), $roleData);
        
        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', [
            'name' => 'moderator',
            'description' => 'Modérateur du système'
        ]);
    }

    #[Test]
    public function admin_can_update_role()
    {
        $this->actingAs($this->admin);
        
        $role = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'Original description',
            'level' => 50,
            'color' => '#10B981'
        ]);
        
        $updateData = [
            'name' => 'updated_role',
            'display_name' => 'Updated Role',
            'description' => 'Updated description',
            'level' => 55,
            'color' => '#10B981'
        ];
        
        $response = $this->put(route('admin.roles.update', $role), $updateData);
        
        $response->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated_role',
            'description' => 'Updated description'
        ]);
    }

    #[Test]
    public function admin_can_delete_custom_role()
    {
        $this->actingAs($this->admin);
        
        // Créer un rôle sans utilisateurs assignés
        $role = Role::create([
            'name' => 'custom_role',
            'display_name' => 'Custom Role',
            'description' => 'Custom role',
            'level' => 70, // Niveau inférieur à admin (80)
            'color' => '#6B7280'
        ]);
        
        // Vérifier que le rôle n'a pas d'utilisateurs
        $this->assertFalse($role->hasUsers());
        
        $response = $this->delete(route('admin.roles.destroy', $role));
        
        $response->assertRedirect(route('admin.roles.index'));
        
        // Vérifier que le rôle est supprimé
        $this->assertNull(Role::find($role->id));
    }

    #[Test]
    public function admin_cannot_delete_system_role()
    {
        $this->actingAs($this->admin);
        
        $systemRole = Role::create([
            'name' => 'system_admin',
            'display_name' => 'System Admin',
            'description' => 'System admin role',
            'level' => 90,
            'color' => '#DC2626'
        ]);
        
        $response = $this->delete(route('admin.roles.destroy', $systemRole));
        
        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['id' => $systemRole->id]);
    }

    #[Test]
    public function role_validation_works()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post(route('admin.roles.store'), [
            'name' => '',
            'description' => 'Test description'
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function role_name_must_be_unique()
    {
        $this->actingAs($this->admin);
        
        Role::create([
            'name' => 'existing_role',
            'display_name' => 'Existing Role',
            'description' => 'Existing role',
            'level' => 40,
            'color' => '#10B981'
        ]);
        
        $response = $this->post(route('admin.roles.store'), [
            'name' => 'existing_role',
            'description' => 'Duplicate role'
        ]);
        
        $response->assertSessionHasErrors(['name']);
    }
}