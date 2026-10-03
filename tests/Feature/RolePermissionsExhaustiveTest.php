<?php

namespace Tests\Feature;

use App\Models\ChapterNarrative;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use App\Support\ActiveYear;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Matriks izin per tiga peran SI-PENA: operator, admin, viewer.
 *
 * `admin` adalah gabungan peran lama editor + approver (hak penuh).
 */
class RolePermissionsExhaustiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeUser(string $role, bool $active = true): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $role)->value('id'),
            'is_active' => $active,
        ]);
    }

    private function makePublication(string $status = 'PENDING_DATA', int $year = 2026): Publication
    {
        return Publication::create([
            'type' => 'KDA',
            'year' => $year,
            'title' => "Kecamatan Uji {$year}",
            'status' => $status,
        ]);
    }

    private function makeNarrative(Publication $pub): ChapterNarrative
    {
        return $pub->narratives()->create([
            'chapter_number' => 1,
            'title_id' => 'Geografi dan Iklim',
            'title_en' => 'Geography and Climate',
            'narrative_id' => 'Teks awal',
            'narrative_en' => 'Initial text',
            'highlight_label' => 'Luas',
            'highlight_value' => '50 km2',
        ]);
    }

    public function test_role_master_contains_only_operator_admin_viewer(): void
    {
        $this->assertSame(
            ['admin', 'operator', 'viewer'],
            Role::orderBy('name')->pluck('name')->values()->all()
        );
    }

    public function test_viewer_role_allow_and_deny_matrix(): void
    {
        $viewer = $this->makeUser('viewer');
        $pub = $this->makePublication();
        $nar = $this->makeNarrative($pub);

        // Allowed GET routes (read-only)
        $this->actingAs($viewer)->get('/')->assertOk();
        $this->actingAs($viewer)->get('/dashboard')->assertOk();
        $this->actingAs($viewer)->get('/compilation')->assertOk();
        $this->actingAs($viewer)->get('/covers')->assertOk();
        $this->actingAs($viewer)->get('/skd')->assertOk();

        // Denied GET routes
        $this->actingAs($viewer)->get('/ingestion')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($viewer)->get('/editorial')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($viewer)->get("/editorial/{$nar->id}/edit")->assertRedirect()->assertSessionHas('error');
        $this->actingAs($viewer)->get('/approval')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($viewer)->get("/approval/{$pub->id}")->assertRedirect()->assertSessionHas('error');

        // Denied POST/PUT actions
        $this->actingAs($viewer)->post('/covers/upload', [
            'publication_id' => $pub->id,
            'cover_image' => UploadedFile::fake()->image('cover.png'),
        ])->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post('/ingestion/upload', [
            'publication_id' => $pub->id,
            'opd_source_name' => 'Dinas A',
            'excel_file' => UploadedFile::fake()->create('test.xlsx'),
            'chapter_number' => 1,
            'data_mode' => 'DIRECT',
        ])->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->put("/editorial/{$nar->id}", [
            'narrative_id' => 'Baru',
            'narrative_en' => 'New',
        ])->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post("/approval/{$pub->id}/approve")
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post("/approval/{$pub->id}/reject", ['remarks' => 'Revisi'])
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post("/compilation/{$pub->id}/compile")
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post('/compilation/batch-all-kda')
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($viewer)->post('/skd/upload', [
            'vkd_file' => UploadedFile::fake()->create('vkd.xlsx'),
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_operator_role_allow_and_deny_matrix(): void
    {
        $operator = $this->makeUser('operator');
        $pub = $this->makePublication();
        $nar = $this->makeNarrative($pub);

        // Allowed
        $this->actingAs($operator)->get('/dashboard')->assertOk();
        $this->actingAs($operator)->get('/ingestion')->assertOk();
        $this->actingAs($operator)->get('/covers')->assertOk();
        $this->actingAs($operator)->get('/compilation')->assertOk();
        $this->actingAs($operator)->get('/skd')->assertOk();

        // Denied from editorial and approval
        $this->actingAs($operator)->get('/editorial')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->get("/editorial/{$nar->id}/edit")->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->put("/editorial/{$nar->id}", ['narrative_id' => 'X', 'narrative_en' => 'Y'])
            ->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->post("/editorial/{$nar->id}/submit")
            ->assertRedirect()->assertSessionHas('error');

        $this->actingAs($operator)->get('/approval')->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->get("/approval/{$pub->id}")->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->post("/approval/{$pub->id}/approve")
            ->assertRedirect()->assertSessionHas('error');

        // Denied from compilation triggers
        $this->actingAs($operator)->post("/compilation/{$pub->id}/compile")
            ->assertRedirect()->assertSessionHas('error');
        $this->actingAs($operator)->post('/compilation/batch-all-kda')
            ->assertRedirect()->assertSessionHas('error');

        // Denied from covers upload
        $this->actingAs($operator)->post('/covers/upload', [
            'publication_id' => $pub->id,
            'cover_image' => UploadedFile::fake()->image('cover.png'),
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_role_allow_matrix_covers_all_modules(): void
    {
        $admin = $this->makeUser('admin');
        $pub = $this->makePublication('PENDING_APPROVAL');
        $nar = $this->makeNarrative($pub);

        // All GETs accessible (redaksi, persetujuan, ingesti, kompilasi, aset, SKD)
        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/ingestion')->assertOk();
        $this->actingAs($admin)->get('/editorial')->assertOk();
        $this->actingAs($admin)->get("/editorial/{$nar->id}/edit")->assertOk();
        $this->actingAs($admin)->get('/approval')->assertOk();
        $this->actingAs($admin)->get("/approval/{$pub->id}")->assertOk();
        $this->actingAs($admin)->get('/compilation')->assertOk();
        $this->actingAs($admin)->get('/covers')->assertOk();
        $this->actingAs($admin)->get('/skd')->assertOk();

        // Aset visual: admin boleh mengunggah cover
        $this->actingAs($admin)->post('/covers/upload', [
            'publication_id' => $pub->id,
            'cover_image' => UploadedFile::fake()->image('cover.png'),
        ])->assertRedirect()->assertSessionHas('success');

        // Approve & reject
        $this->actingAs($admin)->post("/approval/{$pub->id}/approve")
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('APPROVED_LOCKED', $pub->fresh()->status);

        $this->actingAs($admin)->post("/approval/{$pub->id}/reject", ['remarks' => 'Perbaiki tabel'])
            ->assertRedirect()->assertSessionHas('warning');
        $this->assertSame('IN_EDITORIAL', $pub->fresh()->status);

        // Retrasisi ke PENDING_APPROVAL agar pengujian kompilasi berikutnya valid
        $pub->fresh();
        $pub->update(['status' => 'PENDING_APPROVAL']);

        // Kompilasi
        $this->actingAs($admin)->post("/compilation/{$pub->id}/compile")
            ->assertRedirect()->assertSessionHas('success');

        // Audit note (admin + viewer)
        $this->actingAs($admin)->get("/approval/{$pub->id}/audit-note")->assertOk();
    }

    public function test_admin_submit_for_approval_redirects_to_approval_show(): void
    {
        $admin = $this->makeUser('admin');
        $pub = $this->makePublication('DATA_INGESTED');
        $nar = $this->makeNarrative($pub);

        $response = $this->actingAs($admin)->post("/editorial/{$nar->id}/submit");

        $response->assertRedirect(route('approval.show', $pub->id));
        $response->assertSessionHas('success');
        $this->assertSame('PENDING_APPROVAL', $pub->fresh()->status);
    }

    public function test_active_year_controller_stores_valid_year_and_rejects_invalid(): void
    {
        $user = $this->makeUser('viewer');
        $this->makePublication('PENDING_DATA', 2026);
        $this->makePublication('PENDING_DATA', 2027);

        $response = $this->actingAs($user)->post('/set-active-year', ['year' => 2026]);
        $response->assertRedirect()->assertSessionHas('success');
        $this->assertSame(2026, ActiveYear::get());

        // Invalid year
        $this->actingAs($user)->post('/set-active-year', ['year' => 1999])
            ->assertRedirect()->assertSessionHasErrors(['year']);
    }

    public function test_check_deadline_middleware_soft_deadline_warning(): void
    {
        $operator = $this->makeUser('operator');
        $pub = $this->makePublication('PENDING_DATA');
        $pub->update([
            'soft_deadline' => now()->subDay(),
            'hard_deadline' => now()->addDay(),
        ]);

        $this->actingAs($operator)->get("/ingestion?publication_id={$pub->id}")
            ->assertSessionHas('deadline_warning');
    }

    public function test_check_deadline_middleware_hard_deadline_blocks_operator(): void
    {
        $operator = $this->makeUser('operator');
        $pub = $this->makePublication('PENDING_DATA');
        $pub->update([
            'hard_deadline' => now()->subHour(),
        ]);

        $this->actingAs($operator)->post('/ingestion/upload', [
            'publication_id' => $pub->id,
            'opd_source_name' => 'Dinas C',
            'excel_file' => UploadedFile::fake()->create('test.xlsx'),
            'chapter_number' => 1,
            'data_mode' => 'DIRECT',
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_check_deadline_middleware_hard_deadline_exempts_admin(): void
    {
        $admin = $this->makeUser('admin');
        $pub = $this->makePublication('PENDING_APPROVAL');
        $pub->update([
            'hard_deadline' => now()->subHour(),
        ]);

        // Admin dibebaskan dari penguncian hard deadline agar tetap bisa menyetujui.
        $this->actingAs($admin)->post("/approval/{$pub->id}/approve")
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame('APPROVED_LOCKED', $pub->fresh()->status);
    }

    public function test_compilation_download_pdf_missing_returns_error(): void
    {
        $viewer = $this->makeUser('viewer');
        // Judul unik agar tidak tertukar dengan artefak PDF hasil uji kompilasi lain.
        $pub = Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Uji Tanpa Berkas PDF',
            'status' => 'PENDING_DATA',
        ]);

        $this->actingAs($viewer)->get("/compilation/{$pub->id}/download")
            ->assertRedirect()->assertSessionHas('error');
    }
}
