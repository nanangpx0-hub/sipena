<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\TypstCompilerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected TypstCompilerService $typstService;

    public function __construct(TypstCompilerService $typstService)
    {
        $this->typstService = $typstService;
    }

    /**
     * Tampilan meja kerja Approver (Quality Control & Approval)
     */
    public function index(Request $request)
    {
        $publications = Publication::with(['district', 'tables', 'narratives', 'rawDataFiles'])
            ->orderBy('status')
            ->paginate(15);

        return view('approval.index', compact('publications'));
    }

    /**
     * Detail tinjauan publikasi sebelum persetujuan
     */
    public function show(int $id)
    {
        $publication = Publication::with(['district', 'tables', 'narratives', 'rawDataFiles.uploader', 'workflowLogs.user'])
            ->findOrFail($id);

        return view('approval.show', compact('publication'));
    }

    /**
     * Setujui dan kunci bab publikasi (Approve & Lock)
     */
    public function approve(Request $request, int $id)
    {
        $publication = Publication::findOrFail($id);
        $userId = Auth::id();

        if (! $publication->canTransitionTo('APPROVED_LOCKED')) {
            return redirect()->route('approval.index')->with('error', "Persetujuan ditolak: status '{$publication->status}' tidak mengizinkan transisi ke APPROVED_LOCKED.");
        }

        $publication->transitionTo(
            'APPROVED_LOCKED',
            $userId,
            $request->remarks ?: 'Publikasi telah diverifikasi dan disetujui secara resmi. Status terkunci.'
        );

        return redirect()->route('approval.index')
            ->with('success', "Publikasi '{$publication->title}' berhasil disetujui dan dikunci (APPROVED_LOCKED)!");
    }

    /**
     * Tolak draf publikasi dan kembalikan ke Operator / Editor disertai catatan
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'remarks' => 'required|string|min:5',
        ]);

        $publication = Publication::findOrFail($id);
        $userId = Auth::id();

        if (! $publication->canTransitionTo('IN_EDITORIAL')) {
            return redirect()->route('approval.index')->with('error', "Penolakan ditolak: status '{$publication->status}' tidak mengizinkan pengembalian ke IN_EDITORIAL.");
        }

        $publication->transitionTo('IN_EDITORIAL', $userId, '[REVISI DIBUTUHKAN]: '.$request->remarks);

        return redirect()->route('approval.index')
            ->with('warning', "Publikasi '{$publication->title}' dikembalikan untuk perbaikan revisi.");
    }
}
