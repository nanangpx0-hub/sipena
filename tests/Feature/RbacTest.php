<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string $role): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create([
            'role_id' => Role::where('name', $role)->value('id'),
            'is_active' => true,
        ]);
    }

    public function test_guest_cannot_access_any_module(): void
    {
        foreach (['/dashboard', '/ingestion', '/editorial', '/approval', '/compilation', '/skd'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_operator_can_open_ingestion_but_not_approval(): void
    {
        $operator = $this->userWith('operator');

        $this->actingAs($operator)->get('/ingestion')->assertOk();
        $this->actingAs($operator)->get('/approval')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->get('/editorial')->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_can_open_editorial_ingestion_and_approval(): void
    {
        $admin = $this->userWith('admin');

        $this->actingAs($admin)->get('/editorial')->assertOk();
        $this->actingAs($admin)->get('/ingestion')->assertOk();
        $this->actingAs($admin)->get('/approval')->assertOk();
    }

    public function test_viewer_is_read_only(): void
    {
        $viewer = $this->userWith('viewer');

        $this->actingAs($viewer)->get('/dashboard')->assertOk();
        $this->actingAs($viewer)->get('/skd')->assertOk();
        $this->actingAs($viewer)->get('/ingestion')->assertRedirect()->assertSessionHas('error');

        $pub = Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Uji Dalam Angka 2026',
            'status' => 'APPROVED_LOCKED',
        ]);

        $this->actingAs($viewer)
            ->post("/compilation/{$pub->id}/compile")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->actingAs($viewer)
            ->post('/covers/upload')
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_admin_can_open_approval_queue(): void
    {
        $admin = $this->userWith('admin');

        $this->actingAs($admin)->get('/approval')->assertOk();
    }
}
