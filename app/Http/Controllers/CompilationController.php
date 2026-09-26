<?php

namespace App\Http\Controllers;

use App\Jobs\CompilePublicationJob;
use App\Models\Publication;
use App\Services\TypstCompilerService;
use App\Support\ActiveYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CompilationController extends Controller
{
    protected TypstCompilerService $typstService;

    public function __construct(TypstCompilerService $typstService)
    {
        $this->typstService = $typstService;
    }

    /**
     * Dashboard pemantauan kompilasi publikasi dan antrean masal
     */
    public function index()
    {
        $publications = Publication::with('district')->orderBy('type')->orderBy('title')->get();

        $totalKda = Publication::where('type', 'KDA')->count();
        $releasedKda = Publication::where('type', 'KDA')->where('status', 'FINAL_RELEASED')->count();
        $approvedKda = Publication::where('type', 'KDA')->where('status', 'APPROVED_LOCKED')->count();
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        return view('compilation.index', compact(
            'publications',
            'totalKda',
            'releasedKda',
            'approvedKda',
            'pendingJobs',
            'failedJobs'
        ));
    }

    /**
     * Kompilasi instan satu publikasi
     */
    public function compileSingle(Request $request, int $id)
    {
        $pub = Publication::findOrFail($id);

        // Dispatch job or run synchronously if requested
        if ($request->boolean('sync', false)) {
            dispatch_sync(new CompilePublicationJob($pub->id, Auth::id()));

            return redirect()->back()->with('success', "Kompilasi PDF untuk '{$pub->title}' selesai!");
        }

        dispatch(new CompilePublicationJob($pub->id, Auth::id()));

        return redirect()->back()->with('success', "Job kompilasi untuk '{$pub->title}' telah dimasukkan ke dalam antrean.");
    }

    /**
     * Kompilasi Masal Seluruh 31 Kecamatan (KDA) pada TAHUN AKTIF saja.
     */
    public function batchCompileAllKDA()
    {
        $activeYear = ActiveYear::get();

        $kdaList = Publication::where('type', 'KDA')
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->get();
        $count = 0;

        foreach ($kdaList as $pub) {
            dispatch(new CompilePublicationJob($pub->id, Auth::id()));
            $count++;
        }

        return redirect()->route('compilation.index')
            ->with('success', "Berhasil mendispatch {$count} antrean kompilasi KDA tahun ".($activeYear ?? '-').' ke dalam antrean server!');
    }

    /**
     * Unduh file PDF hasil kompilasi
     */
    public function downloadPdf(int $id)
    {
        $pub = Publication::findOrFail($id);
        $safeTitle = Str::slug($pub->title);
        // FIX: job menulis ke storage/output_pdf/{year}/ — selaraskan pembaca (blade memakai path yang sama).
        $filePath = storage_path('output_pdf'.DIRECTORY_SEPARATOR.$pub->year.DIRECTORY_SEPARATOR."{$safeTitle}.pdf");

        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'Berkas PDF belum tersedia atau belum selesai dikompilasi.');
        }

        return response()->download($filePath, "{$safeTitle}.pdf");
    }
}
