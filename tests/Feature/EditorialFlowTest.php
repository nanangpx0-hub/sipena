<?php

namespace Tests\Feature;

use App\Models\ChapterNarrative;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialFlowTest extends TestCase
{
    use RefreshDatabase;

    private Publication $publication;

    private ChapterNarrative $narrative;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->publication = Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Redaksi Dalam Angka 2026',
            'status' => 'DATA_INGESTED',
        ]);

        $this->narrative = $this->publication->narratives()->create([
            'chapter_number' => 1,
            'title_id' => 'Geografi dan Iklim',
            'title_en' => 'Geography and Climate',
            'narrative_id' => 'Narasi awal.',
            'narrative_en' => 'Initial narrative.',
            'highlight_label' => 'Luas Wilayah',
            'highlight_value' => '100 km2',
        ]);
    }

    private function userWith(string $role): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $role)->value('id'),
            'is_active' => true,
        ]);
    }

    public function test_editor_update_moves_publication_to_editorial(): void
    {
        $editor = $this->userWith('admin');

        $this->actingAs($editor)
            ->put("/editorial/{$this->narrative->id}", [
                'narrative_id' => 'Kencong terletak di barat.',
                'narrative_en' => 'Kencong lies in the west.',
                'highlight_label' => 'Luas Wilayah',
                'highlight_value' => '100 km2',
            ])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame('Kencong terletak di barat.', $this->narrative->fresh()->narrative_id);
        $this->assertSame('IN_EDITORIAL', $this->publication->fresh()->status);
    }

    public function test_editor_can_submit_to_approver_once(): void
    {
        $editor = $this->userWith('admin');
        $this->publication->update(['status' => 'IN_EDITORIAL']);

        $this->actingAs($editor)
            ->post("/editorial/{$this->narrative->id}/submit")
            ->assertSessionHas('success');

        $this->assertSame('PENDING_APPROVAL', $this->publication->fresh()->status);

        // Pengajuan kedua tidak diizinkan state machine
        $this->actingAs($editor)
            ->post("/editorial/{$this->narrative->id}/submit")
            ->assertSessionHas('error');

        $this->assertSame('PENDING_APPROVAL', $this->publication->fresh()->status);
    }

    public function test_locked_publication_cannot_be_submitted(): void
    {
        $editor = $this->userWith('admin');
        $this->publication->update(['status' => 'APPROVED_LOCKED']);

        $this->actingAs($editor)
            ->post("/editorial/{$this->narrative->id}/submit")
            ->assertSessionHas('warning');

        $this->assertSame('APPROVED_LOCKED', $this->publication->fresh()->status);
    }

    public function test_editorial_requires_editor_role(): void
    {
        $viewer = $this->userWith('viewer');

        $this->actingAs($viewer)->get('/editorial')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($viewer)->get("/editorial/{$this->narrative->id}/edit")->assertRedirect()->assertSessionHas('error');
    }
}
