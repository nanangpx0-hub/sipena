<?php

namespace Tests\Feature;

use App\Jobs\CompilePublicationJob;
use App\Models\District;
use App\Models\Publication;
use App\Models\PublicationTable;
use App\Models\VisualAsset;
use App\Services\PythonWorkerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji integrasi figure grafik Bab 1 (curah hujan) & Bab 3 (piramida penduduk):
 * grafik hanya dirender dari data ingesti riil; tanpa sumbernya tidak ada
 * #figure sehingga tidak pernah ada angka karangan pada dokumen resmi.
 */
class ChartFigureTest extends TestCase
{
    use RefreshDatabase;

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

    private function pythonAvailable(): bool
    {
        return is_file(base_path('python_engine'.DIRECTORY_SEPARATOR.'venv'.DIRECTORY_SEPARATOR.'Scripts'.DIRECTORY_SEPARATOR.'python.exe'));
    }

    private function publication(): Publication
    {
        $district = District::create([
            'bps_code' => '3509999',
            'name' => 'Uji Grafik',
            'capital_city' => 'Jember',
        ]);

        return Publication::create([
            'type' => 'KDA',
            'year' => 2026,
            'title' => 'Kecamatan Uji Grafik Dalam Angka 2026',
            'status' => 'PENDING_DATA',
            'district_id' => $district->id,
        ]);
    }

    private function climateTable(Publication $pub, int $months = 12): PublicationTable
    {
        $names = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

        $rows = [];
        for ($i = 0; $i < $months; $i++) {
            $rows[] = [
                'Bulan' => $names[$i],
                'Curah Hujan (mm)' => 100 + $i,
                'Jumlah Hari Hujan' => 5 + $i,
            ];
        }

        return PublicationTable::create([
            'publication_id' => $pub->id,
            'chapter_number' => 1,
            'table_number' => '1.1',
            'title_id' => 'Curah Hujan Bulanan',
            'title_en' => 'Monthly Rainfall',
            'source_agency' => 'BMKG Uji',
            'table_data' => ['status' => 'success', 'headers' => ['Bulan', 'Curah Hujan (mm)', 'Jumlah Hari Hujan'], 'data' => $rows],
        ]);
    }

    private function pyramidTable(Publication $pub, int $ageGroups = 16): PublicationTable
    {
        $labels = [
            '0-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-34', '35-39',
            '40-44', '45-49', '50-54', '55-59', '60-64', '65-69', '70-74', '75+',
        ];

        $rows = [];
        for ($i = 0; $i < $ageGroups; $i++) {
            $rows[] = [
                'Kelompok Umur' => $labels[$i],
                'Laki-laki' => 1000 + $i,
                'Perempuan' => 950 + $i,
            ];
        }

        return PublicationTable::create([
            'publication_id' => $pub->id,
            'chapter_number' => 3,
            'table_number' => '3.1',
            'title_id' => 'Piramida Penduduk',
            'title_en' => 'Population Pyramid',
            'source_agency' => 'Dispendukcapil Uji',
            'table_data' => ['status' => 'success', 'headers' => ['Kelompok Umur', 'Laki-laki', 'Perempuan'], 'data' => $rows],
        ]);
    }

    /**
     * @return array{0: CompilePublicationJob, 1: array{climate: ?string, pyramid: ?string}}
     */
    private function figuresFor(Publication $pub): array
    {
        $job = new CompilePublicationJob($pub->id);
        $method = new \ReflectionMethod($job, 'prepareChartFigures');
        $method->setAccessible(true);

        $figures = $method->invoke($job, $pub, new PythonWorkerService);

        foreach (['climate' => 'climate_chart', 'pyramid' => 'pyramid'] as $key => $slug) {
            $path = storage_path('custom_assets'.DIRECTORY_SEPARATOR."{$slug}_{$pub->id}.svg");
            if (is_file($path)) {
                $this->cleanup[] = $path;
            }
        }

        return [$job, $figures];
    }

    public function test_climate_figure_rendered_from_ingested_table(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('venv Python SI-PENA belum tersedia');
        }

        $pub = $this->publication();
        $this->climateTable($pub);

        [, $figures] = $this->figuresFor($pub);

        $this->assertNotNull($figures['climate']);
        $this->assertStringContainsString('#figure(image("/storage/custom_assets/climate_chart_', $figures['climate']);
        $this->assertStringContainsString('Grafik Curah Hujan dan Hari Hujan Bulanan', $figures['climate']);

        $svg = storage_path('custom_assets'.DIRECTORY_SEPARATOR."climate_chart_{$pub->id}.svg");
        $this->assertFileExists($svg);
        $this->assertGreaterThan(1000, filesize($svg));

        $asset = VisualAsset::where('publication_id', $pub->id)
            ->where('asset_type', 'CLIMATE_CHART')
            ->first();
        $this->assertNotNull($asset);
        $this->assertSame('AUTO_GENERATED', $asset->mode);
    }

    public function test_pyramid_figure_rendered_from_ingested_table(): void
    {
        if (! $this->pythonAvailable()) {
            $this->markTestSkipped('venv Python SI-PENA belum tersedia');
        }

        $pub = $this->publication();
        $this->pyramidTable($pub);

        [, $figures] = $this->figuresFor($pub);

        $this->assertNotNull($figures['pyramid']);
        $this->assertStringContainsString('#figure(image("/storage/custom_assets/pyramid_', $figures['pyramid']);
        $this->assertStringContainsString('Piramida Penduduk Menurut Kelompok Umur', $figures['pyramid']);

        $svg = storage_path('custom_assets'.DIRECTORY_SEPARATOR."pyramid_{$pub->id}.svg");
        $this->assertFileExists($svg);
        $this->assertGreaterThan(1000, filesize($svg));

        $this->assertNotNull(
            VisualAsset::where('publication_id', $pub->id)
                ->where('asset_type', 'POPULATION_PYRAMID')
                ->first()
        );
    }

    public function test_no_figure_without_ingested_series(): void
    {
        $pub = $this->publication();
        $this->climateTable($pub, months: 5); // deret tidak lengkap 12 bulan

        [, $figures] = $this->figuresFor($pub);

        $this->assertNull($figures['climate'], 'Deret tidak lengkap tidak boleh menghasilkan figure');
        $this->assertNull($figures['pyramid']);
        $this->assertFileDoesNotExist(storage_path('custom_assets'.DIRECTORY_SEPARATOR."climate_chart_{$pub->id}.svg"));
        $this->assertSame(0, VisualAsset::where('publication_id', $pub->id)->count());
    }

    public function test_no_figure_for_publication_without_tables(): void
    {
        $pub = $this->publication();

        [$job, $figures] = $this->figuresFor($pub);

        $this->assertNull($figures['climate']);
        $this->assertNull($figures['pyramid']);

        // Tanpa sumber data, dokumen tetap tersusun tanpa elemen figure.
        $content = (fn () => $this->buildTypstDocument($pub, [], $figures))->call($job);
        $this->assertStringNotContainsString('#figure(', $content);
        $this->assertStringContainsString('#import', $content);
    }
}
