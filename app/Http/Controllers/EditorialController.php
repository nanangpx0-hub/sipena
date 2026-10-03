<?php

namespace App\Http\Controllers;

use App\Models\ChapterNarrative;
use App\Models\Publication;
use App\Services\NarrativeEngineService;
use App\Support\ActiveYear;
use Illuminate\Http\Request;
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
     * Mendukung mode khusus 3 dimensi publikasi: DDA, KCA/KDA, dan SKD
     */
    public function index(Request $request)
    {
        $activeYear = ActiveYear::get();
        $publications = Publication::with(['district', 'narratives'])
            ->when($activeYear !== null, fn ($query) => $query->where('year', $activeYear))
            ->orderBy('title')->get();

        $ddaPublications = $publications->where('type', 'DDA')->values();
        $kdaPublications = $publications->where('type', 'KDA')->values();
        $skdPublications = $publications->where('type', 'SKD')->values();

        $selectedPubId = $request->get('publication_id');
        $activeType = strtoupper((string) $request->get('type', ''));

        $selectedPub = null;
        if ($selectedPubId) {
            $selectedPub = Publication::with(['district', 'narratives'])->find($selectedPubId);
            if ($selectedPub) {
                $activeType = $selectedPub->type;
            }
        }

        if (! in_array($activeType, ['DDA', 'KDA', 'SKD'], true)) {
            $activeType = $kdaPublications->isNotEmpty() ? 'KDA' : ($publications->first()?->type ?? 'KDA');
        }

        if (! $selectedPub) {
            if ($activeType === 'DDA') {
                $selectedPub = $ddaPublications->first();
            } elseif ($activeType === 'SKD') {
                $selectedPub = $skdPublications->first();
            } else {
                $selectedPub = $kdaPublications->first(fn ($p) => $p->narratives->isNotEmpty())
                    ?? $kdaPublications->first();
            }
        }

        if ($selectedPub && $selectedPub->narratives->isEmpty()) {
            $this->initializeDefaultChapters($selectedPub);
            $selectedPub->load('narratives');
        }

        // Bilingual Completeness Index: persentase naskah bahasa Inggris yang
        // sudah terisi pada seluruh publikasi tahun aktif (data nyata).
        $bookCompleteness = $publications->map(function (Publication $pub) {
            $total = $pub->narratives->count();
            $idFilled = $pub->narratives->filter(fn ($n) => trim((string) $n->narrative_id) !== '')->count();
            $enFilled = $pub->narratives->filter(fn ($n) => trim((string) $n->narrative_en) !== '')->count();

            return [
                'id' => $pub->id,
                'title' => $pub->title,
                'type' => $pub->type,
                'district' => $pub->district?->name,
                'total' => $total,
                'id_pct' => $total > 0 ? (int) round($idFilled * 100 / $total) : 0,
                'en_pct' => $total > 0 ? (int) round($enFilled * 100 / $total) : 0,
            ];
        })->values()->all();

        $totalNarratives = $publications->sum(fn ($p) => $p->narratives->count());
        $totalEnFilled = $publications->sum(fn ($p) => $p->narratives->filter(fn ($n) => trim((string) $n->narrative_en) !== '')->count());
        $bilingualIndex = $totalNarratives > 0 ? (int) round($totalEnFilled * 100 / $totalNarratives) : 0;

        // Ringkasan status chip per publikasi (draf vs siap review).
        $reviewReadyCount = $publications->filter(fn ($p) => in_array($p->status, ['PENDING_APPROVAL', 'APPROVED_LOCKED', 'FINAL_RELEASED'], true))->count();
        $draftCount = $publications->count() - $reviewReadyCount;

        // Naskah bahasa Indonesia yang sudah terisi. Dipasangkan dengan
        // $totalEnFilled agar kartu Bilingual Completeness Index memakai satu
        // satuan yang sama (jumlah naskah), bukan campuran dengan publikasi.
        $idFilledNarratives = $publications->sum(
            fn ($p) => $p->narratives->filter(fn ($n) => trim((string) $n->narrative_id) !== '')->count()
        );
        $idFilledPercent = $totalNarratives > 0 ? (int) round($idFilledNarratives * 100 / $totalNarratives) : 0;

        return view('editorial.index', compact(
            'publications',
            'ddaPublications',
            'kdaPublications',
            'skdPublications',
            'selectedPub',
            'activeType',
            'activeYear',
            'bookCompleteness',
            'bilingualIndex',
            'totalNarratives',
            'totalEnFilled',
            'idFilledNarratives',
            'idFilledPercent',
            'reviewReadyCount',
            'draftCount'
        ));
    }

    /**
     * Inisialisasi struktur bab standar jika publikasi terpilih belum memiliki ulasan bab
     */
    protected function initializeDefaultChapters(Publication $publication): void
    {
        if ($publication->narratives()->count() > 0) {
            return;
        }

        $year = $publication->year;

        if ($publication->type === 'DDA') {
            $chapters = [
                ['num' => 1, 'id' => 'Geografi dan Iklim', 'en' => 'Geography and Climate', 'hl' => 'Luas Wilayah Jember', 'val' => '3.306,68 kmÂ²'],
                ['num' => 2, 'id' => 'Pemerintahan', 'en' => 'Government', 'hl' => 'Jumlah Kecamatan', 'val' => '31 Kecamatan'],
                ['num' => 3, 'id' => 'Penduduk dan Ketenagakerjaan', 'en' => 'Population and Employment', 'hl' => 'Jumlah Penduduk Jember', 'val' => '2.564.890 Jiwa'],
                ['num' => 4, 'id' => 'Sosial dan Kesejahteraan Rakyat', 'en' => 'Social and Welfare', 'hl' => 'Indeks Pembangunan Manusia (IPM)', 'val' => '69,12 Poin'],
                ['num' => 5, 'id' => 'Pertanian, Kehutanan, Peternakan, dan Perikanan', 'en' => 'Agriculture, Forestry, Animal Husbandry, and Fisheries', 'hl' => 'Produksi Padi Kabupaten', 'val' => '620.450 Ton'],
                ['num' => 6, 'id' => 'Pertambangan dan Energi', 'en' => 'Mining and Energy', 'hl' => 'Pelanggan Listrik PLN', 'val' => '780.200 Pelanggan'],
                ['num' => 7, 'id' => 'Industri Pengolahan', 'en' => 'Manufacturing', 'hl' => 'Unit Industri Mikro Kecil', 'val' => '42.150 Unit'],
                ['num' => 8, 'id' => 'Konstruksi', 'en' => 'Construction', 'hl' => 'Panjang Jalan Kabupaten', 'val' => '2.845,60 km'],
                ['num' => 9, 'id' => 'Perdagangan, Hotel, dan Pariwisata', 'en' => 'Trade, Hotel, and Tourism', 'hl' => 'Kunjungan Wisatawan', 'val' => '1.240.500 Orang'],
                ['num' => 10, 'id' => 'Transportasi dan Komunikasi', 'en' => 'Transportation and Communication', 'hl' => 'Penumpang Kereta Api', 'val' => '3.450.200 Orang'],
                ['num' => 11, 'id' => 'Keuangan Daerah dan Harga', 'en' => 'Local Finance and Price', 'hl' => 'Realisasi Pendapatan Daerah', 'val' => 'Rp 4,28 Triliun'],
                ['num' => 12, 'id' => 'Pengeluaran Penduduk', 'en' => 'Household Consumption and Expenditure', 'hl' => 'Rata-rata Pengeluaran per Kapita', 'val' => 'Rp 1.150.000 / bln'],
                ['num' => 13, 'id' => 'Pendapatan Regional', 'en' => 'Regional Income', 'hl' => 'PDRB Atas Dasar Harga Berlaku', 'val' => 'Rp 92,45 Triliun'],
            ];

            foreach ($chapters as $ch) {
                ChapterNarrative::create([
                    'publication_id' => $publication->id,
                    'chapter_number' => $ch['num'],
                    'title_id' => $ch['id'],
                    'title_en' => $ch['en'],
                    'narrative_id' => 'Kabupaten Jember pada Bab '.$ch['num'].' ('.$ch['id'].') tahun '.$year.' mencatatkan kinerja pembangunan yang konsisten dan berkelanjutan di seluruh 31 wilayah kecamatan.',
                    'narrative_en' => 'Jember Regency in Chapter '.$ch['num'].' ('.$ch['en'].') year '.$year.' recorded consistent and sustainable development performance across all 31 district areas.',
                    'highlight_label' => $ch['hl'],
                    'highlight_value' => $ch['val'],
                ]);
            }
        } elseif ($publication->type === 'SKD') {
            $chapters = [
                ['num' => 1, 'id' => 'Pendahuluan', 'en' => 'Introduction', 'hl' => 'Fokus Evaluasi', 'val' => 'Pelayanan Statistik Terpadu', 'desc_id' => 'Survei Kebutuhan Data (SKD) merupakan survei rutin BPS untuk mengevaluasi kualitas layanan data publikasi dan konsultasi statistik di BPS Kabupaten Jember tahun '.$year.'.', 'desc_en' => 'The Data Needs Survey (SKD) is a regular BPS survey to evaluate the service quality of publications and statistical consultations at BPS Jember Regency in '.$year.'.'],
                ['num' => 2, 'id' => 'Metodologi Survei Kebutuhan Data', 'en' => 'Survey Methodology', 'hl' => 'Metode Pencacahan', 'val' => 'Kuesioner VKD & Online', 'desc_id' => 'Pencacahan SKD tahun '.$year.' menggunakan metode kuesioner VKD digital dan tatap muka pada loket Pelayanan Statistik Terpadu (PST) BPS Kabupaten Jember.', 'desc_en' => 'The '.$year.' SKD data collection utilized digital VKD questionnaires and in-person interviews at the Integrated Statistical Service (PST) desk.'],
                ['num' => 3, 'id' => 'Karakteristik Konsumen PST', 'en' => 'Consumer Characteristics', 'hl' => 'Profil Mayoritas Konsumen', 'val' => 'Akademisi & Peneliti', 'desc_id' => 'Profil konsumen data BPS Kabupaten Jember tahun '.$year.' didominasi oleh segmen akademisi (mahasiswa/dosen) disusul instansi pemerintah daerah dan swasta.', 'desc_en' => 'Consumer profiles of BPS Jember Regency in '.$year.' were dominated by academic researchers followed by local government agencies and private sectors.'],
                ['num' => 4, 'id' => 'Analisis Kepuasan Layanan & Anti Korupsi', 'en' => 'Service Satisfaction & Anti-Corruption Analysis', 'hl' => 'Indeks Kepuasan & Anti Korupsi', 'val' => 'Mutu Sangat Baik (A)', 'desc_id' => 'Berdasarkan hasil survei tahun '.$year.', Indeks Kepuasan Konsumen (IKK) dan Indeks Persepsi Anti Korupsi (IPAK) BPS Kabupaten Jember masuk dalam kategori Sangat Baik dan Bebas Pungli.', 'desc_en' => 'Based on the '.$year.' survey, the Consumer Satisfaction Index (IKK) and Anti-Corruption Perception Index (IPAK) achieved Excellent category ratings.'],
                ['num' => 5, 'id' => 'Analisis Tingkat Kepentingan dan Kinerja (IPA)', 'en' => 'Importance-Performance Analysis (IPA)', 'hl' => 'Fokus Pembenahan', 'val' => 'Kuadran Prioritas Terjaga', 'desc_id' => 'Diagram Kartesius memetakan seluruh indikator pelayanan ke dalam Kuadran A, B, C, dan D untuk memprioritaskan pembenahan fasilitas dan akses data.', 'desc_en' => 'The Cartesian diagram maps all service indicators into Quadrants A, B, C, and D to prioritize improvements in statistical access.'],
            ];

            foreach ($chapters as $ch) {
                ChapterNarrative::create([
                    'publication_id' => $publication->id,
                    'chapter_number' => $ch['num'],
                    'title_id' => $ch['id'],
                    'title_en' => $ch['en'],
                    'narrative_id' => $ch['desc_id'],
                    'narrative_en' => $ch['desc_en'],
                    'highlight_label' => $ch['hl'],
                    'highlight_value' => $ch['val'],
                ]);
            }
        } elseif ($publication->type === 'KDA') {
            $district = $publication->district;
            $dName = $district?->name ?? 'Kecamatan';
            $chapters = [
                ['num' => 1, 'id' => 'Geografi dan Iklim', 'en' => 'Geography and Climate', 'hl' => 'Luas Wilayah', 'val' => ($district?->total_area_sqkm ?? 100).' kmÂ²'],
                ['num' => 2, 'id' => 'Pemerintahan', 'en' => 'Government', 'hl' => 'Jumlah Desa/Kelurahan', 'val' => '10 Desa'],
                ['num' => 3, 'id' => 'Penduduk', 'en' => 'Population', 'hl' => 'Jumlah Penduduk', 'val' => '72.450 Jiwa'],
                ['num' => 4, 'id' => 'Sosial dan Kesejahteraan Rakyat', 'en' => 'Social and Welfare', 'hl' => 'Jumlah Sekolah Dasar', 'val' => '34 Unit'],
                ['num' => 5, 'id' => 'Pertanian', 'en' => 'Agriculture', 'hl' => 'Produksi Padi', 'val' => '48.210 Ton'],
                ['num' => 6, 'id' => 'Pariwisata, Transportasi, dan Komunikasi', 'en' => 'Tourism and Transportation', 'hl' => 'Objek Wisata', 'val' => '6 Lokasi'],
                ['num' => 7, 'id' => 'Perbankan, Koperasi, dan Perdagangan', 'en' => 'Banking, Cooperatives, and Trade', 'hl' => 'Jumlah Koperasi Aktif', 'val' => '14 Koperasi'],
            ];

            foreach ($chapters as $ch) {
                ChapterNarrative::create([
                    'publication_id' => $publication->id,
                    'chapter_number' => $ch['num'],
                    'title_id' => $ch['id'],
                    'title_en' => $ch['en'],
                    'narrative_id' => 'Kecamatan '.$dName.' terletak di Kabupaten Jember pada ketinggian rata-rata 0-500 mdpl. Pada tahun '.$year.', luas wilayah tercatat sebesar '.($district?->total_area_sqkm ?? 100).' kmÂ².',
                    'narrative_en' => $dName.' Subdistrict is located in Jember Regency. In '.$year.', total area was recorded at '.($district?->total_area_sqkm ?? 100).' sq.km.',
                    'highlight_label' => $ch['hl'],
                    'highlight_value' => $ch['val'],
                ]);
            }
        }
    }

    /**
     * Form penyuntingan narasi bab dua kolom bilingual
     */
    public function edit(int $narrativeId)
    {
        $narrative = ChapterNarrative::with(['publication.district'])->findOrFail($narrativeId);
        $pub = $narrative->publication;

        // Auto-resolve token jika narasi masih kosong
        if (empty($narrative->narrative_id)) {
            if ($pub->type === 'DDA') {
                $defaultTemplateId = 'Kabupaten Jember memiliki luas wilayah {{ luas_wilayah }} dengan 31 kecamatan dan ibukota di {{ ibukota_kecamatan }}. Pada tahun {{ tahun }}, indikator Bab '.$narrative->chapter_number.' ('.$narrative->title_id.') menunjukkan tren pembangunan daerah yang stabil dan produktif.';
            } elseif ($pub->type === 'SKD') {
                $defaultTemplateId = 'Survei Kebutuhan Data (SKD) BPS Kabupaten Jember tahun {{ tahun }} bertujuan mengidentifikasi kepuasan dan persepsi anti korupsi pengguna data pada Pelayanan Statistik Terpadu (PST).';
            } else {
                $defaultTemplateId = 'Kecamatan {{ nama_kecamatan }} memiliki luas wilayah {{ luas_wilayah }} dengan ibukota di {{ ibukota_kecamatan }}. Pada tahun {{ tahun }}, perkembangan indikator menunjukkan tren yang stabil.';
            }
            $narrative->narrative_id = $this->narrativeService->resolveTokens($defaultTemplateId, $narrative->publication, $narrative->chapter_number);
        }

        $allChapters = $pub->narratives()->orderBy('chapter_number')->get();

        // Panel ringkasan data angka resmi dari tabel database yang relevan
        // dengan bab tersebut (bukan angka karangan — diambil apa adanya).
        $chapterTables = $pub->tables()
            ->where('chapter_number', $narrative->chapter_number)
            ->get();

        $officialDataPanels = $chapterTables->map(function ($table) {
            $data = is_array($table->table_data) ? $table->table_data : [];
            $headers = is_array($data['headers'] ?? null) ? array_slice($data['headers'], 0, 4) : [];
            $rows = is_array($data['data'] ?? null) ? array_slice($data['data'], 0, 5) : [];

            return [
                'number' => $table->table_number,
                'title' => $table->title_id,
                'source' => $table->source_agency,
                'verified' => (bool) $table->is_verified,
                'headers' => $headers,
                'rows' => $rows,
                'total_rows' => $data['total_rows'] ?? count($data['data'] ?? []),
            ];
        })->values()->all();

        // Saran frasa standar narasi statistik BPS (dapat disisipkan editor).
        $phraseSuggestions = [
            'Pada tahun '.$pub->year.', '.mb_strtolower($narrative->title_id).' '.($pub->district?->name ?? 'Kabupaten Jember').' menunjukkan perkembangan sebagai berikut:',
            'Jumlah '.($narrative->highlight_label ?? 'indikator utama').' tercatat sebesar '.($narrative->highlight_value ?? '…').' pada tahun '.$pub->year.'.',
            'Dibandingkan tahun sebelumnya, kondisi ini menunjukkan tren yang perlu dicermati dalam perencanaan pembangunan daerah.',
            'Sumber data: '.($chapterTables->first()?->source_agency ?? 'BPS Kabupaten Jember').'.',
        ];

        return view('editorial.edit', compact('narrative', 'allChapters', 'officialDataPanels', 'phraseSuggestions'));
    }

    /**
     * Simpan pembaruan redaksi dan update status menjadi IN_EDITORIAL
     */
    public function update(Request $request, int $narrativeId)
    {
        $request->validate([
            'narrative_id' => 'required|string',
            'narrative_en' => 'required|string',
            'title_id' => 'nullable|string|max:200',
            'title_en' => 'nullable|string|max:200',
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

        // Judul bab bilingual boleh disunting karena teks tersebut ikut tercetak
        // pada kartu pembatas bab hasil kompilasi Typst. Nomor bab TIDAK dapat
        // diubah karena menjadi kunci urutan lembar buku.
        $narrative->update([
            'narrative_id' => $request->narrative_id,
            'narrative_en' => $request->narrative_en,
            'title_id' => $request->filled('title_id') ? $request->title_id : $narrative->title_id,
            'title_en' => $request->filled('title_en') ? $request->title_en : $narrative->title_en,
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

        if (! $pub->canTransitionTo('PENDING_APPROVAL')) {
            return redirect()->route('editorial.index', ['publication_id' => $pub->id])
                ->with('error', "Pengajuan ditolak: status '{$pub->status}' belum memenuhi syarat untuk diajukan ke Approver.");
        }

        $pub->transitionTo('PENDING_APPROVAL', $userId, 'Editor mengajukan publikasi ke meja Quality Control & Approval.');

        if (Auth::user()?->hasRole(['admin'])) {
            return redirect()->route('approval.show', $pub->id)
                ->with('success', "Publikasi '{$pub->title}' telah diajukan ke Approver (PENDING_APPROVAL).");
        }

        return redirect()->route('editorial.index', ['publication_id' => $pub->id])
            ->with('success', "Publikasi '{$pub->title}' telah diajukan ke Approver (PENDING_APPROVAL).");
    }
}
