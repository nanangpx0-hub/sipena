<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('SI-PENA')
            ->assertDontSee('cdn.tailwindcss.com');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->seedRoles();
        $user = User::factory()->create([
            'email' => 'qa@bps3509.go.id',
            'password' => bcrypt('rahasia123'),
            'role_id' => Role::where('name', 'admin')->value('id'),
            'is_active' => true,
        ]);

        $this->post('/login', ['email' => 'qa@bps3509.go.id', 'password' => 'rahasia123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->seedRoles();
        User::factory()->create([
            'email' => 'qa@bps3509.go.id',
            'password' => bcrypt('rahasia123'),
            'role_id' => Role::where('name', 'operator')->value('id'),
            'is_active' => true,
        ]);

        $this->from('/login')
            ->post('/login', ['email' => 'qa@bps3509.go.id', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->seedRoles();
        User::factory()->create([
            'email' => 'nonaktif@bps3509.go.id',
            'password' => bcrypt('rahasia123'),
            'role_id' => Role::where('name', 'operator')->value('id'),
            'is_active' => false,
        ]);

        $this->from('/login')
            ->post('/login', ['email' => 'nonaktif@bps3509.go.id', 'password' => 'rahasia123'])
            ->assertSessionHasErrors();

        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $this->seedRoles();
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'viewer')->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
