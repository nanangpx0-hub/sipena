<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Publication;
use App\Models\RawDataFile;
use App\Models\VisualAsset;
use App\Support\ActiveYear;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard Utama SI-PENA BPS Kabupaten Jember
     */
    public function index()
    {
        $activeYear = ActiveYear::get();

        $kdaPublications = Publication::with('district')
            ->where('type', 'KDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->get();

        $dda = Publication::where('type', 'DDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->latest()->first();
        $skd = Publication::where('type', 'SKD')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->latest()->first();

        $totalRawFiles = RawDataFile::count();
        $totalDistricts = District::count();
        $statusCounts = Publication::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Highlight wilayah dari data riil (luas & jumlah desa). Bila data tidak
        // tersedia, tampilkan status "belum ada data" — bukan angka karangan.
        $widestDistrict = District::query()->orderByDesc('total_area_sqkm')->first();
        $mostVillagesDistrict = District::withCount('villages')->orderByDesc('villages_count')->first();
        $villageCounts = District::withCount('villages')->pluck('villages_count', 'name');

        // Progres 31 KDA per status bab (untuk bar progres penyelesaian).
        $kdaStatusCounts = collect($kdaPublications)->groupBy('status')->map->count()->toArray();

        // Target rilis terdekat (KDA hard deadline) — cast aman: value() string vs Carbon.
        $nearestDeadline = Publication::whereNotNull('hard_deadline')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('hard_deadline')->first()?->hard_deadline;
        if (is_string($nearestDeadline)) {
            try {
                $nearestDeadline = Carbon::parse($nearestDeadline);
            } catch (\Throwable $e) {
                $nearestDeadline = null;
            }
        }

        // Pratinjau grafik SVG hasil Python Engine bila sudah dirender.
        $pyramidSvg = $this->svgRelOrNull('storage'.DIRECTORY_SEPARATOR.'custom_assets'.DIRECTORY_SEPARATOR.'population_pyramid.svg');
        $climateSvg = $this->svgRelOrNull('storage'.DIRECTORY_SEPARATOR.'custom_assets'.DIRECTORY_SEPARATOR.'climate_chart.svg');

        return view('dashboard.index', compact(
            'activeYear',
            'kdaPublications',
            'dda',
            'skd',
            'totalRawFiles',
            'totalDistricts',
            'statusCounts',
            'nearestDeadline',
            'widestDistrict',
            'mostVillagesDistrict',
            'villageCounts',
            'kdaStatusCounts',
            'pyramidSvg',
            'climateSvg'
        ));
    }

    /**
     * Path relatif SVG (memakai pemisah aman) bila berkas benar-benar ada.
     */
    protected function svgRelOrNull(string $relative): ?string
    {
        $abs = base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative));

        return file_exists($abs) ? str_replace(DIRECTORY_SEPARATOR, '/', $relative) : null;
    }

    /**
     * Tab Cover dan Pembatas Bab Preview & Pengaturan
     */
    public function covers(Request $request)
    {
        $activeYear = ActiveYear::get();
        $publications = Publication::with('district')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('title')->get();
        $selectedId = $request->get('publication_id', $publications->first()?->id);
        $selectedPub = Publication::with(['district', 'narratives', 'visualAssets'])->find($selectedId);
        $customCover = null;
        if ($selectedPub) {
            $customCover = $selectedPub->visualAssets()
                ->where('asset_type', 'COVER_CUSTOM')
                ->latest()->first();
        }

        return view('covers.index', compact('publications', 'selectedPub', 'customCover', 'activeYear'));
    }

    /**
     * Suite 7 SELF-HEAL: unggah cover kustom (manual override) — endpoint yang
     * diharapkan skenario E2E "sakelar mode manual override".
     */
    public function uploadCover(Request $request)
    {
        $request->validate([
            'publication_id' => 'required|exists:publications,id',
            'cover_image' => 'required|file|mimes:jpg,jpeg,png,webp,svg,pdf|max:20480',
        ]);

        $pub = Publication::findOrFail($request->publication_id);

        if ($pub->isLocked()) {
            return redirect()->route('covers.index', ['publication_id' => $pub->id])
                ->with('error', "Publikasi '{$pub->title}' berstatus {$pub->status} dan TERKUNCI. Penggantian cover kustom ditolak.");
        }

        $file = $request->file('cover_image');
        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'custom_assets');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fname = 'cover_custom_'.$pub->id.'_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($dir, $fname);
        $relPath = "storage/app/private/custom_assets/{$fname}";

        VisualAsset::create([
            'publication_id' => $pub->id,
            'asset_type' => 'COVER_CUSTOM',
            'mode' => 'MANUAL_OVERRIDE',
            'file_path' => $relPath,
        ]);

        return redirect()->route('covers.index', ['publication_id' => $pub->id])
            ->with('success', "Cover kustom untuk '{$pub->title}' berhasil diunggah (mode MANUAL_OVERRIDE)!");
    }
}
