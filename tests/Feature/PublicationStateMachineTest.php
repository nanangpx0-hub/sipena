<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function pub(string $status): Publication
    {
        return Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan State Machine 2026',
            'status' => $status,
        ]);
    }

    public function test_state_machine_matrix_matches_prd(): void
    {
        foreach (Publication::ALLOWED_TRANSITIONS as $from => $targets) {
            foreach ($targets as $to) {
                $this->assertTrue($this->pub($from)->canTransitionTo($to), "{$from} -> {$to} seharusnya diizinkan");
            }
        }

        $illegal = [
            ['PENDING_DATA', 'APPROVED_LOCKED'],
            ['PENDING_DATA', 'FINAL_RELEASED'],
            ['DATA_INGESTED', 'FINAL_RELEASED'],
            ['IN_EDITORIAL', 'APPROVED_LOCKED'],
            ['PENDING_APPROVAL', 'FINAL_RELEASED'],
            ['APPROVED_LOCKED', 'PENDING_APPROVAL'],
            ['FINAL_RELEASED', 'APPROVED_LOCKED'],
        ];

        foreach ($illegal as [$from, $to]) {
            $this->assertFalse($this->pub($from)->canTransitionTo($to), "{$from} -> {$to} seharusnya ditolak");
        }
    }

    public function test_transition_updates_status_and_writes_audit_log(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'operator')->value('id'),
            'is_active' => true,
        ]);
        $publication = $this->pub('PENDING_DATA');

        $this->assertTrue($publication->transitionTo('DATA_INGESTED', $user->id, 'Unggah data uji.'));
        $this->assertSame('DATA_INGESTED', $publication->fresh()->status);

        $log = $publication->workflowLogs()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame('PENDING_DATA', $log->from_status);
        $this->assertSame('DATA_INGESTED', $log->to_status);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('Unggah data uji.', $log->remarks);
    }

    public function test_illegal_transition_changes_nothing_and_writes_no_log(): void
    {
        $publication = $this->pub('PENDING_DATA');

        $this->assertFalse($publication->transitionTo('APPROVED_LOCKED', null, 'loncat status'));
        $this->assertSame('PENDING_DATA', $publication->fresh()->status);
        $this->assertSame(0, $publication->workflowLogs()->count());
    }

    public function test_only_final_statuses_are_locked(): void
    {
        $this->assertFalse($this->pub('PENDING_DATA')->isLocked());
        $this->assertFalse($this->pub('DATA_INGESTED')->isLocked());
        $this->assertFalse($this->pub('IN_EDITORIAL')->isLocked());
        $this->assertFalse($this->pub('PENDING_APPROVAL')->isLocked());
        $this->assertTrue($this->pub('APPROVED_LOCKED')->isLocked());
        $this->assertTrue($this->pub('FINAL_RELEASED')->isLocked());
    }

    public function test_editor_cannot_edit_locked_publication(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->value('id'),
            'is_active' => true,
        ]);

        $publication = $this->pub('FINAL_RELEASED');
        $narrative = $publication->narratives()->create([
            'chapter_number' => 1,
            'title_id' => 'Pendahuluan',
            'title_en' => 'Introduction',
            'narrative_id' => 'Narasi asli.',
            'narrative_en' => 'Original narrative.',
            'highlight_label' => 'Luas',
            'highlight_value' => '100 km2',
        ]);

        $this->actingAs($editor)
            ->put("/editorial/{$narrative->id}", [
                'narrative_id' => 'Narasi curian.',
                'narrative_en' => 'Stolen narrative.',
            ])
            ->assertSessionHas('error');

        $this->assertSame('Narasi asli.', $narrative->fresh()->narrative_id);
        $this->assertSame('FINAL_RELEASED', $publication->fresh()->status);
    }

    public function test_approver_flow_approve_and_reject_guards(): void
    {
        $this->seed(RoleSeeder::class);
        $approver = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->value('id'),
            'is_active' => true,
        ]);

        $ready = $this->pub('PENDING_APPROVAL');
        $this->actingAs($approver)
            ->post("/approval/{$ready->id}/approve", ['remarks' => 'Sudah sesuai.'])
            ->assertSessionHas('success');
        $this->assertSame('APPROVED_LOCKED', $ready->fresh()->status);

        $notReady = $this->pub('PENDING_DATA');
        $this->actingAs($approver)
            ->post("/approval/{$notReady->id}/approve", ['remarks' => 'Tidak boleh.'])
            ->assertSessionHas('error');
        $this->assertSame('PENDING_DATA', $notReady->fresh()->status);

        $forRejection = $this->pub('PENDING_APPROVAL');
        $this->actingAs($approver)
            ->post("/approval/{$forRejection->id}/reject", ['remarks' => 'Tabel bab 3 belum konsisten.'])
            ->assertSessionHas('warning');
        $this->assertSame('IN_EDITORIAL', $forRejection->fresh()->status);
        $this->assertStringContainsString(
            'REVISI DIBUTUHKAN',
            $forRejection->workflowLogs()->latest('id')->first()->remarks
        );
    }
}
