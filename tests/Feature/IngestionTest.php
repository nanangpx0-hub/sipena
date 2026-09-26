<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\RawDataFile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\BuildsXlsx;
use Tests\TestCase;

class IngestionTest extends TestCase
{
    use BuildsXlsx, RefreshDatabase;

    private array $cleanup = [];

    protected function tearDown(): void
    {
        foreach ($this->cleanup as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
        parent::tearDown();
    }

    private function operator(): User
    {
        $this->seed(RoleSeeder::class);

        return User::factory()->create([
            'role_id' => Role::where('name', 'operator')->value('id'),
            'is_active' => true,
        ]);
    }

    private function publication(string $status): Publication
    {
        return Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Ingosti Dalam Angka 2026',
            'status' => $status,
        ]);
    }

    private function pythonAvailable(): bool
    {
        return is_file(base_path('python_engine'.DIRECTORY_SEPARATOR.'venv'.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe'));
    }

    private function uploadPayload(Publication $pub, string $xlsxPath): array
    {
        $file = new UploadedFile($xlsxPath, 'data_uji.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        return [
            '_token' => 'test-token',
            'publication_id' => $pub->id,
            'opd_source_name' => 'Dinas Uji Ingesti',
            'data_mode' => 'DIRECT',
            'chapter_number' => '4',
            'table_number' => '4.99',
            'excel_file' => $file,
        ];
    }

    public function test_upload_requires_valid_input(): void
    {
        $operator = $this->operator();
        $pub = $this->publication('PENDING_DATA');

        $this->actingAs($operator)
            ->post('/ingestion/upload', ['_token' => 'x', 'publication_id' => $pub->id])
            ->assertSessionHasErrors(['excel_file', 'opd_source_name', 'data_mode', 'chapter_number']);
    }

    public function test_locked_publication_rejects_upload(): void
    {
        $operator = $this->operator();
        $pub = $this->publication('APPROVED_LOCKED');
        $xlsx = $this->buildXlsx('locked', [['Kecamatan', 'Desa'], ['Kencong', 'Cakru']]);

        $this->actingAs($operator)
            ->from('/ingestion')
            ->post('/ingestion/upload', $this->uploadPayload($pub, $xlsx))
            ->assertRedirect('/ingestion')
            ->assertSessionHas('error');

        $this->assertSame(0, RawDataFile::count());
        $this->assertSame(0, PublicationTable::count());
        $this->assertSame('APPROVED_LOCKED', $pub->fresh()->status);
    }

    public function test_successful_upload_creates_table_and_advances_status(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();
        $pub = $this->publication('PENDING_DATA');
        $xlsx = $this->buildXlsx('ok', [
            ['Kecamatan', 'Desa', 'Guru'],
            ['Kencong', 'Cakru', '10'],
            ['Kencong', 'Sukorejo', '12'],
        ]);

        $this->actingAs($operator)
            ->post('/ingestion/upload', $this->uploadPayload($pub, $xlsx))
            ->assertSessionHas('success');

        $raw = RawDataFile::first();
        $this->assertNotNull($raw);
        $this->cleanup[] = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $raw->storage_path));
        $this->assertSame('INGESTED', $raw->status);
        $this->assertSame(64, strlen($raw->file_hash_sha256));

        $table = PublicationTable::first();
        $this->assertNotNull($table);
        $this->assertFalse((bool) $table->is_verified);
        $this->assertSame('Dinas Uji Ingesti', $table->source_agency);
        $this->assertSame('success', $table->table_data['status']);
        $this->assertSame(['Kecamatan', 'Desa', 'Guru'], array_values($table->table_data['headers']));
        $this->assertCount(2, $table->table_data['data']);
        $this->assertSame('Kencong', $table->table_data['data'][0]['Kecamatan']);

        $this->assertSame('DATA_INGESTED', $pub->fresh()->status);
        $this->assertSame(1, $pub->workflowLogs()->count());
    }

    public function test_failing_worker_marks_raw_file_failed_without_touching_data(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();
        $pub = $this->publication('PENDING_DATA');
        $xlsx = $this->buildXlsx('kosong', []); // sheet tanpa data -> worker melaporkan error

        $this->actingAs($operator)
            ->post('/ingestion/upload', $this->uploadPayload($pub, $xlsx))
            ->assertSessionHas('error');

        $raw = RawDataFile::first();
        $this->assertNotNull($raw);
        $this->cleanup[] = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $raw->storage_path));
        $this->assertSame('FAILED', $raw->status);

        $this->assertSame(0, PublicationTable::count());
        $this->assertSame('PENDING_DATA', $pub->fresh()->status);
    }

    public function test_same_opd_file_is_versioned(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('Python venv SI-PENA tidak tersedia.');
        }

        $operator = $this->operator();
        $pub = $this->publication('PENDING_DATA');

        foreach ([1, 2] as $attempt) {
            $xlsx = $this->buildXlsx('versi', [['Kecamatan', 'Desa'], ['Ambulu', 'Wonojati']]);
            $this->actingAs($operator)
                ->post('/ingestion/upload', $this->uploadPayload($pub, $xlsx))
                ->assertSessionHas('success');
        }

        $versions = RawDataFile::orderBy('version_number')->pluck('version_number')->all();
        $this->assertSame([1, 2], $versions);

        foreach (RawDataFile::all() as $raw) {
            $this->cleanup[] = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $raw->storage_path));
        }
    }
}
