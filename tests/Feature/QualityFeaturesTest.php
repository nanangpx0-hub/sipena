<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\AnomalyDetectionService;
use App\Services\FuzzyMatchService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pengujian rekomendasi mutu SI-PENA: ekspor CSV, status antrean JSON,
 * halaman catatan audit (RBAC), deteksi anomali lonjakan, dan saran
 * koreksi nama desa (fuzzy matching).
 */
class QualityFeaturesTest extends TestCase
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

    private function publication(): Publication
    {
        return Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Uji Mutu Dalam Angka 2026',
            'status' => 'PENDING_DATA',
        ]);
    }

    private function tableWith(array $data): PublicationTable
    {
        return PublicationTable::create([
            'publication_id' => $this->publication()->id,
            'chapter_number' => 4,
            'table_number' => '4.99',
            'title_id' => 'Tabel Jumlah Penduduk',
            'title_en' => 'Table of Population',
            'source_agency' => 'Dinas Kependudukan Uji',
            'table_data' => $data,
            'is_verified' => false,
        ]);
    }

    private function sampleTableData(): array
    {
        return [
            'status' => 'success',
            'sheet_name' => 'Sheet1',
            'headers' => ['Desa', 'Jumlah Penduduk'],
            'total_rows' => 2,
            'data' => [
                ['Desa' => 'Cakru', 'Jumlah Penduduk' => '1.234'],
                ['Desa' => 'Kenen', 'Jumlah Penduduk' => '987'],
            ],
        ];
    }

    // ------------------------------------------------------------------
    // 1. Ekspor CSV streaming
    // ------------------------------------------------------------------

    public function test_csv_export_streams_utf8_bom_and_rows(): void
    {
        $table = $this->tableWith($this->sampleTableData());
        $user = $this->userWith('admin');

        $response = $this->actingAs($user)->get(route('tables.export-csv', $table->id));

        $response->assertOk();
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));

        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('tabel_4.99_tabel-jumlah-penduduk.csv', $disposition);
        $this->assertStringContainsString('attachment', $disposition);

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'BOM UTF-8 wajib ada agar Excel aman');
        $this->assertStringContainsString('Desa,"Jumlah Penduduk"', $content);
        $this->assertStringContainsString('Cakru,1.234', $content);
        $this->assertStringContainsString('Kenen,987', $content);
    }

    public function test_csv_export_available_from_compilation_route(): void
    {
        $table = $this->tableWith($this->sampleTableData());
        $user = $this->userWith('admin');

        $response = $this->actingAs($user)
            ->get(route('compilation.export-csv', ['id' => $table->publication_id, 'tableId' => $table->id]));

        $response->assertOk();
        $this->assertStringContainsString('Cakru', $response->streamedContent());
    }

    public function test_csv_export_requires_authentication(): void
    {
        $table = $this->tableWith($this->sampleTableData());

        $this->get(route('tables.export-csv', $table->id))->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // 2. Status antrean kompilasi (JSON polling)
    // ------------------------------------------------------------------

    public function test_queue_status_returns_counts_json(): void
    {
        $approver = $this->userWith('admin');

        $response = $this->actingAs($approver)->getJson(route('compilation.queue-status'));

        $response->assertOk()->assertJsonStructure([
            'pendingJobs',
            'failedJobs',
            'totalKda',
            'approvedKda',
            'releasedKda',
            'activeYear',
        ]);

        $this->assertSame(0, $response->json('pendingJobs'));
        $this->assertSame(0, $response->json('totalKda'));
    }

    public function test_queue_status_counts_kda_on_active_year(): void
    {
        $approver = $this->userWith('admin');

        Publication::create(['type' => 'KDA', 'year' => 2026, 'title' => 'KDA Uji A', 'status' => 'APPROVED_LOCKED']);
        Publication::create(['type' => 'KDA', 'year' => 2026, 'title' => 'KDA Uji B', 'status' => 'FINAL_RELEASED']);
        Publication::create(['type' => 'KDA', 'year' => 2026, 'title' => 'KDA Uji C', 'status' => 'PENDING_DATA']);
        Publication::create(['type' => 'DDA', 'year' => 2026, 'title' => 'DDA Uji', 'status' => 'PENDING_DATA']);

        $response = $this->actingAs($approver)->getJson(route('compilation.queue-status'));

        $response->assertOk();
        $this->assertSame(3, $response->json('totalKda'));
        $this->assertSame(1, $response->json('approvedKda'));
        $this->assertSame(1, $response->json('releasedKda'));
    }

    public function test_queue_status_requires_login(): void
    {
        $this->getJson(route('compilation.queue-status'))->assertUnauthorized();
        $this->get(route('compilation.queue-status'))->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // 3. Halaman catatan audit (A4) & RBAC-nya
    // ------------------------------------------------------------------

    public function test_approver_can_open_audit_note(): void
    {
        $table = $this->tableWith([
            'status' => 'success',
            'headers' => ['Desa', 'Jumlah'],
            'total_rows' => 1,
            'data' => [
                ['Desa' => 'Cakru', 'Jumlah' => 10],
            ],
            'warnings' => ['Total angka tabel: 10 menjadi 50 (+400.0%).'],
            'village_suggestions' => [
                ['row' => 1, 'column' => 'Desa', 'raw_name' => 'Ds. Cakru', 'official_name' => 'Cakru', 'similarity' => 100, 'reason' => 'Berbeda awalan/singkatan (mis. Ds./ Kel.)'],
            ],
        ]);

        $approver = $this->userWith('admin');

        $response = $this->actingAs($approver)
            ->get(route('approval.audit-note', $table->publication_id));

        $response->assertOk();
        $response->assertSee('Catatan Audit');
        $response->assertSee('Kecamatan Uji Mutu Dalam Angka 2026');
        $response->assertSee('Riwayat Berkas Sumber');
        $response->assertSee('Jejak Audit Alur Kerja');
        $response->assertSee('Peringatan Anomali Data');
        $response->assertSee('Saran Koreksi Nama Desa');
        $response->assertSee('Ds. Cakru');
    }

    public function test_viewer_can_open_audit_note(): void
    {
        $table = $this->tableWith($this->sampleTableData());
        $viewer = $this->userWith('viewer');

        $this->actingAs($viewer)
            ->get(route('approval.audit-note', $table->publication_id))
            ->assertOk();
    }

    public function test_operator_cannot_open_audit_note(): void
    {
        $table = $this->tableWith($this->sampleTableData());

        foreach (['operator'] as $role) {
            $user = $this->userWith($role);

            $this->actingAs($user)
                ->get(route('approval.audit-note', $table->publication_id))
                ->assertRedirect()
                ->assertSessionHas('error');
        }
    }

    public function test_guest_cannot_open_audit_note(): void
    {
        $table = $this->tableWith($this->sampleTableData());

        $this->get(route('approval.audit-note', $table->publication_id))
            ->assertRedirect(route('login'));
    }

    // ------------------------------------------------------------------
    // 4. Deteksi anomali lonjakan / nilai negatif
    // ------------------------------------------------------------------

    public function test_anomaly_service_flags_year_over_year_spike(): void
    {
        $service = new AnomalyDetectionService;

        $previous = [
            'headers' => ['Desa', 'Jumlah'],
            'data' => [
                ['Desa' => 'Cakru', 'Jumlah' => 100],
                ['Desa' => 'Kenen', 'Jumlah' => 200],
            ],
        ];

        $current = [
            'headers' => ['Desa', 'Jumlah'],
            'data' => [
                ['Desa' => 'Cakru', 'Jumlah' => 500],
                ['Desa' => 'Kenen', 'Jumlah' => 210],
            ],
        ];

        $warnings = $service->detect($current, $previous);

        $this->assertNotEmpty($warnings);
        $this->assertTrue(
            collect($warnings)->contains(fn (string $message) => str_contains($message, 'Baris kolom "Jumlah"')),
            'Lonjakan 100 -> 500 pada kolom Jumlah wajib terdeteksi'
        );
        $this->assertFalse(
            collect($warnings)->contains(fn (string $message) => str_contains($message, '210')),
            'Perubahan 200 -> 210 (5%) di bawah ambang tidak boleh ditandai'
        );
    }

    public function test_anomaly_service_flags_total_surge_and_negative_values(): void
    {
        $service = new AnomalyDetectionService;

        $warnings = $service->detect(
            ['headers' => ['Desa', 'Jumlah'], 'data' => [['Desa' => 'Cakru', 'Jumlah' => -5]]],
            ['headers' => ['Desa', 'Jumlah'], 'data' => [['Desa' => 'Cakru', 'Jumlah' => 100]]],
        );

        $this->assertTrue(collect($warnings)->contains(fn (string $m) => str_contains($m, 'Total angka tabel')));
        $this->assertTrue(collect($warnings)->contains(fn (string $m) => str_contains($m, 'negatif')));
    }

    public function test_anomaly_service_is_quiet_without_findings(): void
    {
        $service = new AnomalyDetectionService;

        $current = ['headers' => ['Desa', 'Jumlah'], 'data' => [['Desa' => 'Cakru', 'Jumlah' => 110]]];
        $previous = ['headers' => ['Desa', 'Jumlah'], 'data' => [['Desa' => 'Cakru', 'Jumlah' => 100]]];

        $this->assertSame([], $service->detect($current, $previous));
        $this->assertSame([], $service->detect($current, null));
    }

    public function test_anomaly_service_caps_warning_list(): void
    {
        $service = new AnomalyDetectionService;

        $rows = [];
        for ($i = 0; $i < 40; $i++) {
            $rows[] = ['Desa' => 'Desa '.$i, 'Jumlah' => 10];
        }

        $previousRows = [];
        for ($i = 0; $i < 40; $i++) {
            $previousRows[] = ['Desa' => 'Desa '.$i, 'Jumlah' => 1000];
        }

        $warnings = $service->detect(
            ['headers' => ['Desa', 'Jumlah'], 'data' => $rows],
            ['headers' => ['Desa', 'Jumlah'], 'data' => $previousRows],
        );

        $this->assertLessThanOrEqual(AnomalyDetectionService::MAX_WARNINGS + 1, count($warnings));
        $this->assertTrue(collect($warnings)->contains(fn (string $m) => str_contains($m, 'tidak ditampilkan')));
    }

    // ------------------------------------------------------------------
    // 5. Fuzzy matching saran koreksi nama desa
    // ------------------------------------------------------------------

    private function villageFixture(): array
    {
        $district = District::create([
            'bps_code' => '3509010',
            'name' => 'Uji Mutu',
            'capital_city' => 'Jember',
        ]);

        Village::create(['district_id' => $district->id, 'bps_code' => '3509010001', 'name' => 'Cakru']);
        Village::create(['district_id' => $district->id, 'bps_code' => '3509010002', 'name' => 'Kenen']);

        return [$district];
    }

    public function test_fuzzy_suggestion_for_prefix_variant_and_typo(): void
    {
        [$district] = $this->villageFixture();
        $service = new FuzzyMatchService;

        $prefix = $service->suggestVillage('Ds. Cakru', $district->id);
        $this->assertNotNull($prefix);
        $this->assertSame('Cakru', $prefix['official_name']);
        $this->assertSame('Berbeda awalan/singkatan (mis. Ds./ Kel.)', $prefix['reason']);

        $typo = $service->suggestVillage('Cakruu', $district->id);
        $this->assertNotNull($typo);
        $this->assertSame('Cakru', $typo['official_name']);
        $this->assertGreaterThanOrEqual(75.0, $typo['similarity']);
        $this->assertLessThanOrEqual(95.0, $typo['similarity']);
    }

    public function test_fuzzy_suggestion_absent_for_exact_or_unrelated_names(): void
    {
        [$district] = $this->villageFixture();
        $service = new FuzzyMatchService;

        $this->assertNull($service->suggestVillage('Cakru', $district->id), 'Nama baku tidak perlu saran');
        $this->assertNull($service->suggestVillage('   ', $district->id));
        $this->assertNull($service->suggestVillage('Wonorejo Makmur Jaya', $district->id), 'Nama tak berhubungan di luar pita 75-95%');
    }

    public function test_village_column_detector_and_normalizer(): void
    {
        $service = new FuzzyMatchService;

        $this->assertSame('Desa', $service->villageColumn(['Kecamatan', 'Desa', 'Jumlah']));
        $this->assertSame('Nama Kelurahan', $service->villageColumn(['Kecamatan', 'Nama Kelurahan']));
        $this->assertNull($service->villageColumn(['Kecamatan', 'Jumlah Penduduk']));

        $this->assertSame('cakru', $service->normalizeVillageName('Ds. Cakru'));
        $this->assertSame('kenen', $service->normalizeVillageName('Kel. Kenen'));
        $this->assertSame('dusun baru', $service->normalizeVillageName('Dsn. Dusun Baru'));
    }
}
