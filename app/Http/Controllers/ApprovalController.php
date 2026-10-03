<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\TypstCompilerService;
use App\Support\ActiveYear;
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
        $activeYear = ActiveYear::get();

        $publications = Publication::with(['district', 'tables', 'narratives', 'rawDataFiles'])
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('status')
            ->paginate(15);

        return view('approval.index', compact('publications', 'activeYear'));
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
     * Laporan cetak (A4) catatan audit & mutu data untuk diarsipkan bersama
     * dokumen keluaran: identitas publikasi, riwayat berkas OPD, editor ulasan,
     * jejak audit alur kerja, serta temuan anomali data ingesti.
     */
    public function auditNote(int $id)
    {
        $publication = Publication::with([
            'district',
            'tables',
            'rawDataFiles.uploader',
            'workflowLogs.user',
            'narratives.editor',
        ])->findOrFail($id);

        $warningCount = 0;
        $suggestionCount = 0;

        foreach ($publication->tables as $table) {
            $data = is_array($table->table_data) ? $table->table_data : [];

            if (is_array($data['warnings'] ?? null)) {
                $warningCount += count($data['warnings']);
            }

            if (is_array($data['village_suggestions'] ?? null)) {
                $suggestionCount += count($data['village_suggestions']);
            }
        }

        $editors = $publication->narratives
            ->filter(fn ($narrative) => $narrative->last_edited_by !== null)
            ->unique(fn ($narrative) => $narrative->last_edited_by)
            ->values();

        $workflowLogs = $publication->workflowLogs
            ->sortBy('created_at')
            ->values();

        return view('approval.audit-note', compact(
            'publication',
            'warningCount',
            'suggestionCount',
            'editors',
            'workflowLogs'
        ));
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
