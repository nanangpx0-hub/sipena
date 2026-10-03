<?php

namespace App\Http\Controllers;

use App\Jobs\CompilePublicationJob;
use App\Models\ChapterNarrative;
use App\Models\Publication;
use App\Models\PublicationTeamMember;
use App\Models\VisualAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicationWorkspaceController extends Controller
{
    /**
     * Daftar singkatan default BPS (fallback bila belum dikustomisasi).
     *
     * @return array<int, array{0: string, 1: string, 2: string}>
     */
    private function defaultAbbreviations(): array
    {
        return [
            ['BPS', 'Badan Pusat Statistik', 'Statistics Indonesia'],
            ['KDA', 'Kecamatan Dalam Angka', 'District in Figures'],
            ['DDA', 'Kabupaten Dalam Angka', 'Regency in Figures'],
            ['rb', 'ribu', 'thousand'],
            ['jt', 'juta', 'million'],
            ['%', 'persen', 'percent'],
            ['dpl', 'di atas permukaan laut', 'above sea level'],
            ['km', 'kilometer', 'kilometer'],
            ['ha', 'hektar', 'hectare'],
        ];
    }

    /**
     * Bangun daftar halaman buku sesuai tipe publikasi.
     *
     * @return array<int, array{key: string, label: string, label_en: string, icon: string, editable: bool, auto: bool}>
     */
    private function buildPages(Publication $publication): array
    {
        $pages = [
            ['key' => 'cover',         'label' => 'Cover Depan',      'label_en' => 'Front Cover',        'icon' => '🖼️',  'editable' => true,  'auto' => false],
            ['key' => 'imprimatur',    'label' => 'Halaman Judul',     'label_en' => 'Title Page',         'icon' => '📋', 'editable' => true,  'auto' => false],
            ['key' => 'team',          'label' => 'Tim Penyusun',      'label_en' => 'Team Members',       'icon' => '👥', 'editable' => true,  'auto' => false],
            ['key' => 'preface',       'label' => 'Kata Pengantar',    'label_en' => 'Preface',            'icon' => '✍️', 'editable' => true,  'auto' => false],
            ['key' => 'toc',           'label' => 'Daftar Isi',        'label_en' => 'Table of Contents',  'icon' => '📑', 'editable' => false, 'auto' => true],
            ['key' => 'tables_index',  'label' => 'Daftar Tabel',      'label_en' => 'List of Tables',     'icon' => '📊', 'editable' => false, 'auto' => true],
            ['key' => 'explanatory',   'label' => 'Penjelasan Umum',   'label_en' => 'Explanatory Notes',  'icon' => '📝', 'editable' => false, 'auto' => true],
            ['key' => 'abbreviations', 'label' => 'Daftar Singkatan',  'label_en' => 'Abbreviations',      'icon' => '🔤', 'editable' => true,  'auto' => false],
        ];

        foreach ($publication->narratives as $narrative) {
            $pages[] = [
                'key' => 'chapter_'.$narrative->chapter_number,
                'label' => 'Bab '.$narrative->chapter_number.' — '.$narrative->title_id,
                'label_en' => $narrative->title_en ?? '',
                'icon' => '📄',
                'editable' => true,
                'auto' => false,
            ];
        }

        return $pages;
    }

    /**
     * Kesiapan cetak publikasi: daftar pemeriksaan seluruh halaman buku.
     *
     * Setiap halaman bernilai 'ok' (hijau), 'warn' (kuning) atau 'blocking'
     * (merah). Halaman otomatis (daftar isi, daftar tabel, penjelasan umum)
     * dihitung 'ok' karena dihasilkan Typst; ketersediaannya tetap
     * bergantung pada keberadaan tabel dan naskah.
     *
     * PERSENTASE KESIAPAN dihitung hanya dari halaman yang wajib disunting
     * manusia; halaman otomatis tidak boleh menaikkan skor secara semu.
     * Tidak ada angka karangan: setiap butir diturunkan dari isi database.
     *
     * @return array{
     *     items: array<int, array{key: string, label: string, label_en: string, icon: string, state: string, auto: bool, editable: bool, note: string}>,
     *     manual_total: int,
     *     manual_ok: int,
     *     percent: int,
     *     blocking: array<int, string>
     * }
     */
    private function buildPrintReadiness(Publication $publication, array $pages): array
    {
        $prefaceId = trim((string) $publication->preface_id) !== '';
        $prefaceEn = trim((string) $publication->preface_en) !== '';
        $hasTeam = $publication->teamMembers->isNotEmpty();
        $hasAbbreviations = ! empty($publication->custom_abbreviations);
        $hasTables = $publication->tables->isNotEmpty();

        $metadataComplete = filled($publication->title)
            && filled($publication->year)
            && filled($publication->catalog_number)
            && filled($publication->publication_number)
            && filled($publication->issn)
            && filled($publication->book_size);

        $items = [];

        foreach ($pages as $page) {
            $state = 'ok';
            $note = 'Siap dicetak.';

            switch ($page['key']) {
                case 'cover':
                    if (! $metadataComplete) {
                        $state = 'warn';
                        $note = 'Metadata cover belum lengkap (katalog/nomor publikasi/ISSN/ukuran buku).';
                    }
                    break;

                case 'imprimatur':
                    $missingImprimatur = array_values(array_filter([
                        'nomor katalog' => $publication->catalog_number,
                        'nomor publikasi' => $publication->publication_number,
                        'ISSN' => $publication->issn,
                        'ukuran buku' => $publication->book_size,
                    ], fn ($value) => blank($value)));

                    if ($missingImprimatur !== []) {
                        $state = 'warn';
                        $note = 'Kolofon belum memuat: '.implode(', ', $missingImprimatur).'.';
                    }
                    break;

                case 'team':
                    if (! $hasTeam) {
                        $state = 'blocking';
                        $note = 'Tim penyusun belum diisi; halaman judul wajib mencantumkan penyusun.';
                    }
                    break;

                case 'preface':
                    if (! $prefaceId) {
                        $state = 'blocking';
                        $note = 'Kata pengantar bahasa Indonesia belum diisi.';
                    } elseif (! $prefaceEn) {
                        $state = 'warn';
                        $note = 'Kata pengantar bahasa Inggris belum diisi (kelengkapan bilingual).';
                    }
                    break;

                case 'tables_index':
                    if (! $hasTables) {
                        $state = 'blocking';
                        $note = 'Belum ada tabel teringesti sehingga daftar tabel kosong.';
                    }
                    break;

                case 'explanatory':
                    if (! $publication->narratives->isNotEmpty()) {
                        $state = 'blocking';
                        $note = 'Belum ada naskah bab sehingga penjelasan umum tidak memiliki konteks.';
                    }
                    break;

                case 'abbreviations':
                    if (! $hasAbbreviations) {
                        $state = 'warn';
                        $note = 'Memakai daftar singkatan bawaan BPS; sesuaikan bila ada singkatan khusus.';
                    }
                    break;

                default:
                    if (str_starts_with($page['key'], 'chapter_')) {
                        $narrative = $publication->narratives
                            ->firstWhere('chapter_number', (int) Str::after($page['key'], 'chapter_'));

                        if (! $narrative) {
                            $state = 'blocking';
                            $note = 'Naskah bab tidak ditemukan pada database.';
                        } elseif (blank($narrative->narrative_id)) {
                            $state = 'blocking';
                            $note = 'Ulasan bahasa Indonesia belum diisi.';
                        } elseif (blank($narrative->narrative_en)) {
                            $state = 'warn';
                            $note = 'Ulasan bahasa Inggris belum diisi (kelengkapan bilingual).';
                        } elseif (blank($narrative->highlight_value)) {
                            $state = 'warn';
                            $note = 'Angka indikator kunci pada pembatas bab belum diisi.';
                        }
                    }
                    break;
            }

            $items[] = [
                'key' => $page['key'],
                'label' => $page['label'],
                'label_en' => $page['label_en'],
                'icon' => $page['icon'],
                'state' => $state,
                'auto' => $page['auto'],
                'editable' => $page['editable'],
                'note' => $note,
            ];
        }

        // Persentase hanya menghitung halaman yang disunting manusia, supaya
        // halaman otomatis tidak boleh menaikkan skor secara semu.
        $manualItems = array_values(array_filter($items, fn ($item) => ! $item['auto']));
        $manualTotal = count($manualItems);
        $manualOk = count(array_filter($manualItems, fn ($item) => $item['state'] === 'ok'));
        $blocking = array_values(array_map(
            fn ($item) => $item['label'],
            array_filter($items, fn ($item) => $item['state'] === 'blocking')
        ));

        return [
            'items' => $items,
            'manual_total' => $manualTotal,
            'manual_ok' => $manualOk,
            'percent' => $manualTotal > 0 ? (int) round($manualOk * 100 / $manualTotal) : 0,
            'blocking' => $blocking,
        ];
    }

    /**
     * Halaman utama workspace per publikasi.
     */
    public function show(Publication $publication): View
    {
        $publication->load([
            'district',
            'narratives' => fn ($q) => $q->orderBy('chapter_number'),
            'tables',
            'teamMembers',
            'visualAssets',
        ]);

        $pages = $this->buildPages($publication);
        $readiness = $this->buildPrintReadiness($publication, $pages);

        $abbreviations = $publication->custom_abbreviations ?? $this->defaultAbbreviations();

        return view('publications.workspace', compact('publication', 'pages', 'abbreviations', 'readiness'));
    }

    /**
     * Simpan perubahan Cover Depan (metadata + opsional gambar kustom).
     */
    public function updateCover(Request $request, Publication $publication): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'year' => 'required|string|max:10',
            'catalog_number' => 'nullable|string|max:50',
            'publication_number' => 'nullable|string|max:50',
            'issn' => 'nullable|string|max:30',
            'volume' => 'nullable|string|max:20',
            'cover_image' => 'nullable|file|mimes:jpg,jpeg,png,webp,svg|max:5120',
        ]);

        $publication->update([
            'title' => $validated['title'],
            'year' => (int) $validated['year'],
            'catalog_number' => $validated['catalog_number'] ?? $publication->catalog_number,
            'publication_number' => $validated['publication_number'] ?? $publication->publication_number,
            'issn' => $validated['issn'] ?? $publication->issn,
            'volume' => $validated['volume'] ?? $publication->volume,
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $slug = Str::slug($publication->title);
            $ext = $file->getClientOriginalExtension();
            $dest = 'app'.DIRECTORY_SEPARATOR.'private'.DIRECTORY_SEPARATOR.'custom_assets';
            $filename = "{$slug}_cover.{$ext}";
            $file->move(storage_path($dest), $filename);

            $storagePath = 'storage/app/private/custom_assets/'.$filename;

            $existing = VisualAsset::where('publication_id', $publication->id)
                ->where('asset_type', 'COVER_CUSTOM')
                ->where('mode', 'MANUAL_OVERRIDE')
                ->first();

            if ($existing) {
                $existing->update(['file_path' => $storagePath]);
            } else {
                VisualAsset::create([
                    'publication_id' => $publication->id,
                    'asset_type' => 'COVER_CUSTOM',
                    'mode' => 'MANUAL_OVERRIDE',
                    'chapter_number' => null,
                    'file_path' => $storagePath,
                ]);
            }
        }

        return redirect()->route('pub.workspace', $publication)
            ->with('success', 'Cover Depan berhasil disimpan.');
    }

    /**
     * Simpan perubahan Halaman Judul (kolofon/imprimatur).
     */
    public function updateImprimatur(Request $request, Publication $publication): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'catalog_number' => 'nullable|string|max:50',
            'publication_number' => 'nullable|string|max:50',
            'issn' => 'nullable|string|max:30',
            'volume' => 'nullable|string|max:20',
            'book_size' => 'nullable|string|max:50',
        ]);

        $publication->update(array_filter($validated, fn ($v) => $v !== null));

        return redirect()->route('pub.workspace', $publication)
            ->with('success', 'Halaman Judul berhasil disimpan.');
    }

    /**
     * Simpan Tim Penyusun (hapus semua lama → insert baru).
     */
    public function updateTeam(Request $request, Publication $publication): RedirectResponse
    {
        $request->validate([
            'entries' => 'nullable|array',
            'entries.*.role_id' => 'required|string|max:150',
            'entries.*.role_en' => 'required|string|max:150',
            'entries.*.names' => 'required|string',
        ]);

        PublicationTeamMember::where('publication_id', $publication->id)->delete();

        foreach (($request->input('entries', [])) as $index => $entry) {
            $names = array_values(array_filter(
                array_map('trim', explode("\n", $entry['names'] ?? '')),
                fn ($n) => $n !== ''
            ));

            if ($names === []) {
                continue;
            }

            PublicationTeamMember::create([
                'publication_id' => $publication->id,
                'role_id' => $entry['role_id'],
                'role_en' => $entry['role_en'],
                'names' => $names,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('pub.workspace', $publication)
            ->with('success', 'Tim Penyusun berhasil disimpan.');
    }

    /**
     * Simpan Kata Pengantar (ID + EN) dan tanggal tanda tangan.
     */
    public function updatePreface(Request $request, Publication $publication): RedirectResponse
    {
        $validated = $request->validate([
            'preface_id' => 'nullable|string',
            'preface_en' => 'nullable|string',
            'sign_date' => 'nullable|string|max:120',
        ]);

        $publication->update($validated);

        return redirect()->route('pub.workspace', $publication)
            ->with('success', 'Kata Pengantar berhasil disimpan.');
    }

    /**
     * Simpan Daftar Singkatan kustom.
     */
    public function updateAbbreviations(Request $request, Publication $publication): RedirectResponse
    {
        $request->validate([
            'abbreviations' => 'nullable|array',
            'abbreviations.*.0' => 'required|string|max:30',
            'abbreviations.*.1' => 'required|string|max:200',
            'abbreviations.*.2' => 'required|string|max:200',
        ]);

        $publication->update([
            'custom_abbreviations' => $request->input('abbreviations', []),
        ]);

        return redirect()->route('pub.workspace', $publication)
            ->with('success', 'Daftar Singkatan berhasil disimpan.');
    }

    /**
     * Simpan narasi ulasan satu bab.
     */
    public function updateChapter(
        Request $request,
        Publication $publication,
        ChapterNarrative $narrative
    ): RedirectResponse {
        abort_if($narrative->publication_id !== $publication->id, 403, 'Narrative tidak milik publikasi ini.');

        if ($publication->isLocked()) {
            return redirect()->route('pub.workspace', $publication)
                ->with('error', "Publikasi '{$publication->title}' berstatus {$publication->status} dan TERKUNCI. Minta Approver membuka kunci terlebih dahulu.");
        }

        $validated = $request->validate([
            'narrative_id' => 'required|string',
            'narrative_en' => 'required|string',
            'highlight_label' => 'nullable|string|max:100',
            'highlight_value' => 'nullable|string|max:100',
        ]);

        $narrative->update(array_merge($validated, ['last_edited_by' => Auth::id()]));

        $userId = Auth::id();
        if ($publication->canTransitionTo('IN_EDITORIAL')) {
            $publication->transitionTo('IN_EDITORIAL', $userId, "Editor memperbarui ulasan bilingual bab {$narrative->chapter_number} via Workspace.");
        }

        return redirect()->route('pub.workspace', $publication)
            ->with('success', "Bab {$narrative->chapter_number} ({$narrative->title_id}) berhasil disimpan.");
    }

    /**
     * Dispatch job kompilasi PDF dari workspace.
     */
    public function generate(Request $request, Publication $publication): RedirectResponse
    {
        dispatch(new CompilePublicationJob($publication->id, Auth::id()));

        return redirect()->route('pub.workspace', $publication)
            ->with('success', "Job kompilasi PDF untuk '{$publication->title}' telah dimasukkan ke antrean. Cek status di halaman Kompilasi.");
    }
}
