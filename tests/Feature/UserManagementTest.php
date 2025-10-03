<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $superAdmin;
    protected $admin;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles de test
        $this->createTestRoles();

        // Créer les utilisateurs de test
        $this->createTestUsers();
    }

    #[Test]
    public function super_admin_can_access_admin_panel()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_cannot_access_admin_panel()
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/admin/users');

        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 'active',
            'roles' => [Role::where('name', 'user')->first()->id]
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post('/admin/users', $userData);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    #[Test]
    public function admin_cannot_modify_super_admin()
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$this->superAdmin->id}", [
                'name' => 'Modified Super Admin',
                'email' => $this->superAdmin->email
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_role_hierarchy_is_respected()
    {
        $managerRole = Role::where('name', 'manager')->first();
        $userRole = Role::where('name', 'user')->first();

        // L'admin peut assigner le rôle manager
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test Manager',
                'email' => 'manager@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'roles' => [$managerRole->id]
            ]);

        $response->assertRedirect('/admin/users');

        // Mais ne peut pas assigner le rôle super_admin
        $superAdminRole = Role::where('name', 'super_admin')->first();

        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'Test Super Admin',
                'email' => 'fake-super@test.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'roles' => [$superAdminRole->id]
            ]);

        $response->assertSessionHasErrors();
    }

    #[Test]
    public function cannot_delete_last_super_admin()
    {
        // Assurer qu'il n'y a qu'un seul super admin
        User::whereHas('roles', function($query) {
            $query->where('name', 'super_admin');
        })->where('id', '!=', $this->superAdmin->id)->delete();

        $response = $this->actingAs($this->superAdmin)
            ->delete("/admin/users/{$this->superAdmin->id}");

        $response->assertSessionHasErrors();
        $this->assertDatabaseHas('users', [
            'id' => $this->superAdmin->id,
            'status' => 'active'
        ]);
    }

    #[Test]
    public function user_can_be_archived_instead_of_deleted()
    {
        $testUser = User::factory()->create();
        $userRole = Role::where('name', 'user')->first();
        $testUser->roles()->attach($userRole->id);

        $response = $this->actingAs($this->superAdmin)
            ->delete("/admin/users/{$testUser->id}");

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'status' => 'archived',
            'archived_by' => $this->superAdmin->id
        ]);
    }

    #[Test]
    public function bulk_actions_work_correctly()
    {
        $users = User::factory()->count(3)->create();
        $userRole = Role::where('name', 'user')->first();

        foreach($users as $user) {
            $user->roles()->attach($userRole->id);
        }

        $userIds = $users->pluck('id')->toArray();

        $response = $this->actingAs($this->superAdmin)
            ->post('/admin/users/bulk-action', [
                'action' => 'deactivate',
                'user_ids' => $userIds
            ]);

        $response->assertStatus(200);

        foreach($users as $user) {
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'status' => 'inactive'
            ]);
        }
    }

    #[Test]
    public function user_search_returns_correct_results()
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/users/search?q=John');

        $response->assertStatus(200)
                ->assertJsonFragment(['name' => 'John Doe']);
    }

    #[Test]
    public function role_permissions_are_validated()
    {
        $roleData = [
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'A test role',
            'level' => 30,
            'permissions' => ['users.view', 'users.create']
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post('/admin/roles', $roleData);

        $response->assertRedirect('/admin/roles');
        $this->assertDatabaseHas('roles', [
            'name' => 'test_role',
            'level' => 30
        ]);
    }

    #[Test]
    public function export_functionality_works()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/users/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    private function createTestRoles(): void
    {
        Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Super Administrator',
            'level' => 100,
            'permissions' => ['*'],
            'color' => '#DC2626'
        ]);

        Role::create([
            'name' => 'admin',
            'display_name' => 'Admin',
            'description' => 'Administrator',
            'level' => 80,
            'permissions' => ['users.*', 'roles.*'],
            'color' => '#F59E0B'
        ]);

        Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager',
            'level' => 60,
            'permissions' => ['users.view', 'users.create'],
            'color' => '#3B82F6'
        ]);

        Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Regular User',
            'level' => 20,
            'permissions' => ['users.view'],
            'color' => '#10B981'
        ]);
    }

    private function createTestUsers(): void
    {
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'status' => 'active'
        ]);
        $this->superAdmin->roles()->attach(Role::where('name', 'super_admin')->first()->id);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => 'active'
        ]);
        $this->admin->roles()->attach(Role::where('name', 'admin')->first()->id);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'status' => 'active'
        ]);
        $this->regularUser->roles()->attach(Role::where('name', 'user')->first()->id);
    }
}
