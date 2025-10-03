<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class BasicTestVerification extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function database_migrations_work()
    {
        // Créer un rôle de base
        $role = Role::create([
            'name' => 'test_role',
            'display_name' => 'Test Role',
            'description' => 'Test role for verification',
            'level' => 50,
            'permissions' => ['test.permission'],
            'color' => '#000000'
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'test_role'
        ]);

        // Créer un utilisateur de base
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);

        // Attacher le rôle
        $user->roles()->attach($role->id);

        $this->assertTrue($user->hasRole('test_role'));
    }

    /** @test */
    public function dashboard_route_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }
}
