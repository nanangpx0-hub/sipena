<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\RawDataFile;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestionTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create([
            'role_id' => Role::where('name', 'operator')->value('id'),
            'is_active' => true,
        ]);
    }

    private function pythonAvailable(): bool
    {
        return is_file(base_path('python_engine'.DIRECTORY_SEPARATOR.'venv'.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/ingestion/template/standard')->assertRedirect('/login');
        $this->get('/ingestion/template/schools')->assertRedirect('/login');
        $this->get('/ingestion/template/skd')->assertRedirect('/login');
    }

    public function test_operator_can_download_standard_template(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();

        $response = $this->actingAs($operator)
            ->get('/ingestion/template/standard');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition', 'attachment; filename=Template_Tabel_Standar_DDA_Jember.xlsx');
    }

    public function test_operator_can_download_schools_template(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();

        $response = $this->actingAs($operator)
            ->get('/ingestion/template/schools');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition', 'attachment; filename=Template_Dapodik_EMIS_Jember.xlsx');
    }

    public function test_operator_can_download_skd_template(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();

        $response = $this->actingAs($operator)
            ->get('/ingestion/template/skd');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition', 'attachment; filename=Template_Kuesioner_VKD_SKD.xlsx');
    }

    public function test_operator_can_download_kda_publication_specific_template(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();
        $district = District::create([
            'bps_code' => '3509050',
            'name' => 'Ambulu',
            'capital_city' => 'Ambulu',
            'altitude_min' => 8,
            'altitude_max' => 30,
            'total_area_sqkm' => 116.5,
        ]);
        Village::create([
            'district_id' => $district->id,
            'bps_code' => '3509050001',
            'name' => 'Andongsari',
            'is_kelurahan' => false,
            'area_sqm' => 1000000,
        ]);
        $pub = Publication::create([
            'type' => 'KDA',
            'district_id' => $district->id,
            'year' => 2026,
            'title' => 'Kecamatan Ambulu Dalam Angka 2026',
            'status' => 'PENDING_DATA',
        ]);

        $response = $this->actingAs($operator)
            ->get("/ingestion/template/standard?publication_id={$pub->id}");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('Template_Tabel_KDA_ambulu.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_invalid_template_type_returns_404(): void
    {
        $operator = $this->operator();

        $this->actingAs($operator)
            ->get('/ingestion/template/invalid-type')
            ->assertNotFound();
    }

    public function test_operator_can_download_raw_file(): void
    {
        $operator = $this->operator();
        $pub = Publication::create([
            'type' => 'DDA',
            'year' => 2026,
            'title' => 'Kabupaten Jember Dalam Angka 2026',
            'status' => 'PENDING_DATA',
        ]);

        $tmpFile = storage_path('temp'.DIRECTORY_SEPARATOR.'test_raw_upload.xlsx');
        if (! is_dir(dirname($tmpFile))) {
            mkdir(dirname($tmpFile), 0755, true);
        }
        file_put_contents($tmpFile, 'dummy excel content');

        $raw = RawDataFile::create([
            'publication_id' => $pub->id,
            'opd_source_name' => 'Dinas Pendidikan',
            'original_filename' => 'data_sekolah_asli.xlsx',
            'storage_path' => str_replace(base_path().DIRECTORY_SEPARATOR, '', $tmpFile),
            'file_hash_sha256' => hash('sha256', 'dummy excel content'),
            'version_number' => 1,
            'uploaded_by' => $operator->id,
            'status' => 'INGESTED',
        ]);

        $response = $this->actingAs($operator)
            ->get("/ingestion/raw-files/{$raw->id}/download");

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=data_sekolah_asli.xlsx');

        if (file_exists($tmpFile)) {
            @unlink($tmpFile);
        }
    }

    public function test_operator_can_preview_extracted_data(): void
    {
        $operator = $this->operator();
        $pub = Publication::create([
            'type' => 'DDA',
            'year' => 2026,
            'title' => 'Kabupaten Jember Dalam Angka 2026',
            'status' => 'DATA_INGESTED',
        ]);

        $raw = RawDataFile::create([
            'publication_id' => $pub->id,
            'opd_source_name' => 'Dinas Pendidikan',
            'original_filename' => 'dapodik_2026.xlsx',
            'storage_path' => 'storage/app/private/dummy.xlsx',
            'file_hash_sha256' => str_repeat('a', 64),
            'version_number' => 1,
            'uploaded_by' => $operator->id,
            'status' => 'INGESTED',
        ]);

        PublicationTable::create([
            'publication_id' => $pub->id,
            'chapter_number' => 4,
            'table_number' => '4.1',
            'title_id' => 'Jumlah Sekolah Menurut Kecamatan',
            'title_en' => 'Number of Schools by District',
            'source_agency' => 'Dinas Pendidikan',
            'table_data' => [
                'headers' => ['Kecamatan', 'Guru', 'Murid'],
                'data' => [
                    ['Kecamatan' => 'Kencong', 'Guru' => 25, 'Murid' => 500],
                ],
            ],
            'is_verified' => false,
        ]);

        $response = $this->actingAs($operator)
            ->getJson("/ingestion/raw-files/{$raw->id}/preview");

        $response->assertOk();
        $response->assertJsonPath('raw_file.filename', 'dapodik_2026.xlsx');
        $response->assertJsonPath('tables.0.table_number', '4.1');
        $response->assertJsonPath('tables.0.title_id', 'Jumlah Sekolah Menurut Kecamatan');
    }
}
