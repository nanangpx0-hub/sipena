<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\ChapterNarrative;
use App\Services\NarrativeEngineService;
use Illuminate\Support\Facades\Auth;

class EditorialController extends Controller
{
    protected NarrativeEngineService $narrativeService;

    public function __construct(NarrativeEngineService $narrativeService)
    {
        $this->narrativeService = $narrativeService;
    }

    /**
     * Tampilan daftar narasi ulasan bab yang siap disunting
     */
    public function index(Request $request)
    {
        $publications = Publication::with(['district', 'narratives'])->orderBy('title')->get();
        // SELF-HEAL: default jangan publikasi alfabetis pertama (SKD "Analisis..." tanpa narasi),
        // melainkan publikasi yang MEMILIKI narasi bab agar tombol "Sunting Ulasan" selalu ada.
        $selectedPubId = $request->get('publication_id');
        if (!$selectedPubId) {
            $withNarratives = Publication::has('narratives')->orderBy('title')->first();
            $selectedPubId = $withNarratives?->id ?? $publications->first()?->id;
        }
        $selectedPub = Publication::with(['district', 'narratives'])->find($selectedPubId);

        return view('editorial.index', compact('publications', 'selectedPub'));
    }

    /**
     * Form penyuntingan narasi bab dua kolom bilingual
     */
    public function edit(int $narrativeId)
    {
        $narrative = ChapterNarrative::with(['publication.district'])->findOrFail($narrativeId);
        
        // Auto-resolve token jika narasi masih kosong
        if (empty($narrative->narrative_id)) {
            $defaultTemplateId = "Kecamatan {{ nama_kecamatan }} memiliki luas wilayah {{ luas_wilayah }} dengan ibukota di {{ ibukota_kecamatan }}. Pada tahun {{ tahun }}, perkembangan indikator menunjukkan tren yang stabil.";
            $narrative->narrative_id = $this->narrativeService->resolveTokens($defaultTemplateId, $narrative->publication, $narrative->chapter_number);
        }

        return view('editorial.edit', compact('narrative'));
    }

    /**
     * Simpan pembaruan redaksi dan update status menjadi IN_EDITORIAL
     */
    public function update(Request $request, int $narrativeId)
    {
        $request->validate([
            'narrative_id' => 'required|string',
            'narrative_en' => 'required|string',
            'highlight_label' => 'nullable|string|max:100',
            'highlight_value' => 'nullable|string|max:100',
        ]);

        $narrative = ChapterNarrative::findOrFail($narrativeId);
        $pub = $narrative->publication;

        if ($pub->isLocked()) {
            return redirect()->route('editorial.index', ['publication_id' => $pub->id])
                ->with('error', "Publikasi '{$pub->title}' berstatus {$pub->status} dan TERKUNCI dari penyuntingan. Minta Approver membuka kunci terlebih dahulu.");
        }

        $userId = Auth::id();

        $narrative->update([
            'narrative_id' => $request->narrative_id,
            'narrative_en' => $request->narrative_en,
            'highlight_label' => $request->highlight_label,
            'highlight_value' => $request->highlight_value,
            'last_edited_by' => $userId,
        ]);

        if ($pub->canTransitionTo('IN_EDITORIAL')) {
            $pub->transitionTo('IN_EDITORIAL', $userId, "Editor memperbarui ulasan bilingual bab {$narrative->chapter_number}.");
        }

        return redirect()->route('editorial.index', ['publication_id' => $pub->id])
            ->with('success', "Ulasan Bab {$narrative->chapter_number} ({$narrative->title_id}) berhasil disimpan!");
    }

    /**
     * Ajukan publikasi ke meja Approver (IN_EDITORIAL / DATA_INGESTED -> PENDING_APPROVAL)
     */
    public function submitForApproval(int $narrativeId)
    {
        $narrative = ChapterNarrative::with('publication')->findOrFail($narrativeId);
        $pub = $narrative->publication;
        $userId = Auth::id();

        if ($pub->isLocked()) {
            return redirect()->route('editorial.index', ['publication_id' => $pub->id])
                ->with('warning', "Publikasi sudah berstatus {$pub->status}; tidak perlu diajukan ulang.");
        }

        if (!$pub->canTransitionTo('PENDING_APPROVAL')) {
            return redirect()->route('editorial.index', ['publication_id' => $pub->id])
                ->with('error', "Pengajuan ditolak: status '{$pub->status}' belum memenuhi syarat untuk diajukan ke Approver.");
        }

        $pub->transitionTo('PENDING_APPROVAL', $userId, "Editor mengajukan publikasi ke meja Quality Control & Approval.");

        return redirect()->route('approval.show', $pub->id)
            ->with('success', "Publikasi '{$pub->title}' telah diajukan ke Approver (PENDING_APPROVAL).");
    }
}
