<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\District;
use App\Models\RawDataFile;
use App\Models\ChapterNarrative;

class DashboardController extends Controller
{
    /**
     * Dashboard Utama SI-PENA BPS Kabupaten Jember
     */
    public function index()
    {
        $kdaPublications = Publication::with('district')
            ->where('type', 'KDA')
            ->get();

        $dda = Publication::where('type', 'DDA')->latest()->first();
        $skd = Publication::where('type', 'SKD')->latest()->first();

        $totalRawFiles = RawDataFile::count();
        $totalDistricts = District::count();
        $statusCounts = Publication::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Target rilis terdekat (KDA hard deadline) — cast aman: value() string vs Carbon.
        $nearestDeadline = Publication::whereNotNull('hard_deadline')->orderBy('hard_deadline')->first()?->hard_deadline;
        if (is_string($nearestDeadline)) {
            try { $nearestDeadline = \Carbon\Carbon::parse($nearestDeadline); } catch (\Throwable $e) { $nearestDeadline = null; }
        }

        return view('dashboard.index', compact(
            'kdaPublications',
            'dda',
            'skd',
            'totalRawFiles',
            'totalDistricts',
            'statusCounts',
            'nearestDeadline'
        ));
    }

    /**
     * Tab Cover dan Pembatas Bab Preview & Pengaturan
     */
    public function covers(Request $request)
    {
        $publications = Publication::with('district')->orderBy('title')->get();
        $selectedId = $request->get('publication_id', $publications->first()?->id);
        $selectedPub = Publication::with(['district', 'narratives', 'visualAssets'])->find($selectedId);
        $customCover = null;
        if ($selectedPub) {
            $customCover = $selectedPub->visualAssets()
                ->where('asset_type', 'COVER_CUSTOM')
                ->latest()->first();
        }

        return view('covers.index', compact('publications', 'selectedPub', 'customCover'));
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
        $dir = storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'custom_assets');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = 'cover_custom_' . $pub->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $fname);
        $relPath = "storage/app/private/custom_assets/{$fname}";

        \App\Models\VisualAsset::create([
            'publication_id' => $pub->id,
            'asset_type' => 'COVER_CUSTOM',
            'mode' => 'MANUAL_OVERRIDE',
            'file_path' => $relPath,
        ]);

        return redirect()->route('covers.index', ['publication_id' => $pub->id])
            ->with('success', "Cover kustom untuk '{$pub->title}' berhasil diunggah (mode MANUAL_OVERRIDE)!");
    }
}
