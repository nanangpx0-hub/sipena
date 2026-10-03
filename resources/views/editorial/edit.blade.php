@extends('layouts.app', ['title' => 'Sunting Ulasan Bab ' . $narrative->chapter_number . ' (' . ($narrative->publication->type === 'KDA' ? 'KCA / KDA' : $narrative->publication->type) . ') - SI-PENA'])

@php
    $pub = $narrative->publication;
    $type = $pub->type;
    $isDda = $type === 'DDA';
    $isSkd = $type === 'SKD';
    $isKda = !$isDda && !$isSkd;

    if ($isDda) {
        $theme = [
            'type_badge' => 'DDA',
            'type_name' => 'Kabupaten Dalam Angka',
            'dim_badge' => "\u{1F3EF} AREA EDITOR: DDA (KABUPATEN DALAM ANGKA)",
            'dim_badge_class' => 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300',
            'hero_gradient' => 'from-slate-900 via-slate-800 to-emerald-950 border-emerald-500',
            'accent_text' => 'text-emerald-700',
            'accent_bg' => 'bg-emerald-600',
            'accent_hover' => 'hover:bg-emerald-700',
            'accent_border' => 'border-emerald-500',
            'ring_color' => 'focus:ring-emerald-500/20 focus:border-emerald-500',
            'highlight_card' => 'bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 border-emerald-200',
            'highlight_title' => 'Indikator Kunci Pembatas Bab Makro (Key Figures on Divider)',
            'highlight_label_color' => 'text-emerald-900',
            'save_btn_class' => 'bg-emerald-700 hover:bg-emerald-800 text-white',
            'total_chapters_label' => '13 Bab Komprehensif Makro Kabupaten',
            'guidance_desc' => 'Ulasan DDA memfokuskan gambaran makroekonomi, agregasi data 31 kecamatan se-Kabupaten Jember, tren data runtun waktu (time-series), dan capaian strategis daerah.',
            'sample_tokens' => [
                '{{ nama_kabupaten }}' => 'Nama wilayah (Kabupaten Jember)',
                '{{ luas_wilayah }}' => 'Luas wilayah (diisi otomatis dari tabel wilayah, tanpa angka tebakan)',
                '{{ jumlah_kecamatan }}' => 'Jumlah kecamatan (31 Kecamatan)',
                '{{ tahun }}' => 'Tahun rilis publikasi (' . $pub->year . ')',
                '{{ pdrb_adhb }}' => 'PDRB Atas Dasar Harga Berlaku',
                '{{ judul_publikasi }}' => 'Judul publikasi induk',
            ]
        ];
    } elseif ($isSkd) {
        $theme = [
            'type_badge' => 'SKD',
            'type_name' => 'Survei Kebutuhan Data',
            'dim_badge' => '📊 AREA EDITOR: SKD (SURVEI KEBUTUHAN DATA)',
            'dim_badge_class' => 'bg-indigo-500/20 border-indigo-500/40 text-indigo-300',
            'hero_gradient' => 'from-slate-900 via-slate-800 to-indigo-950 border-indigo-500',
            'accent_text' => 'text-indigo-700',
            'accent_bg' => 'bg-indigo-600',
            'accent_hover' => 'hover:bg-indigo-700',
            'accent_border' => 'border-indigo-500',
            'ring_color' => 'focus:ring-indigo-500/20 focus:border-indigo-500',
            'highlight_card' => 'bg-gradient-to-r from-indigo-50 via-purple-50 to-indigo-50 border-indigo-200',
            'highlight_title' => 'Metrik & Indikator Kunci Survei (Key Survey Figures)',
            'highlight_label_color' => 'text-indigo-900',
            'save_btn_class' => 'bg-indigo-700 hover:bg-indigo-800 text-white',
            'total_chapters_label' => '5 Bab Analisis Tematik Pelayanan Statistik',
            'guidance_desc' => 'Ulasan SKD berfokus pada hasil analisis kepuasan konsumen Pelayanan Statistik Terpadu (PST), Indeks Kepuasan Konsumen (IKK), Indeks Persepsi Anti Korupsi (IPAK), dan pemetaan Diagram Kartesius IPA.',
            'sample_tokens' => [
                '{{ tahun }}' => 'Tahun pelaksanaan survei (' . $pub->year . ')',
                '{{ ikk }}' => 'Indeks Kepuasan Konsumen PST',
                '{{ ipak }}' => 'Indeks Persepsi Anti Korupsi',
                '{{ responden_utama }}' => 'Segmen konsumen terbanyak',
                '{{ mutu_pelayanan }}' => 'Predikat mutu layanan (Sangat Baik/A)',
                '{{ judul_publikasi }}' => 'Judul publikasi laporan',
            ]
        ];
    } else {
        $theme = [
            'type_badge' => 'KCA / KDA',
            'type_name' => 'Kecamatan Dalam Angka',
            'dim_badge' => '📊 AREA EDITOR: KCA / KDA (KECAMATAN DALAM ANGKA)',
            'dim_badge_class' => 'bg-bps-orange/20 border-bps-orange/40 text-amber-300',
            'hero_gradient' => 'from-slate-900 via-slate-800 to-amber-950 border-bps-orange',
            'accent_text' => 'text-bps-darkorange',
            'accent_bg' => 'bg-bps-orange',
            'accent_hover' => 'hover:bg-bps-darkorange',
            'accent_border' => 'border-bps-orange',
            'ring_color' => 'focus:ring-bps-orange/20 focus:border-bps-orange',
            'highlight_card' => 'bg-gradient-to-r from-orange-50 via-amber-50 to-orange-50 border-orange-200',
            'highlight_title' => 'Indikator Kunci Pembatas Bab (Key Figures on Divider)',
            'highlight_label_color' => 'text-bps-darkorange',
            'save_btn_class' => 'bg-bps-navy hover:bg-bps-darknavy text-white',
            'total_chapters_label' => '7 Bab Standar Kewilayahan Kecamatan',
            'guidance_desc' => 'Ulasan KCA/KDA difokuskan pada perbandingan mikro antar desa/kelurahan di Kecamatan ' . ($pub->district->name ?? 'Jember') . ', dinamika demografi lokal, dan capaian sektoral wilayah.',
            'sample_tokens' => [
                '{{ nama_kecamatan }}' => 'Nama kecamatan (' . ($pub->district->name ?? 'Kecamatan') . ')',
                '{{ ibukota_kecamatan }}' => 'Ibukota kecamatan',
                '{{ luas_wilayah }}' => 'Luas wilayah kecamatan',
                '{{ jumlah_desa }}' => 'Jumlah desa/kelurahan',
                '{{ total_penduduk }}' => 'Jumlah penduduk kecamatan',
                '{{ tahun }}' => 'Tahun rilis publikasi (' . $pub->year . ')',
            ]
        ];
    }
@endphp

@section('content')
<div class="space-y-6">

    <!-- 1. BREADCRUMB & BACK NAVIGATION -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center space-x-2 text-xs">
            <a href="{{ route('editorial.index', ['publication_id' => $pub->id, 'type' => $type]) }}" class="font-bold text-bps-blue hover:text-bps-navy hover:underline flex items-center">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Meja Redaksi {{ $theme['type_badge'] }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $isDda ? 'bg-emerald-100 text-emerald-800' : ($isSkd ? 'bg-indigo-100 text-indigo-800' : 'bg-orange-100 text-bps-darkorange') }}">
                {{ $theme['type_badge'] }}
            </span>
            <span class="text-slate-300">/</span>
            <span class="text-slate-500 font-medium truncate max-w-xs sm:max-w-md">{{ $pub->title }}</span>
            <span class="text-slate-300">/</span>
            <span class="text-bps-navy font-bold">Bab {{ $narrative->chapter_number }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <span class="text-[11px] text-slate-500 font-medium">Status Publikasi:</span>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider {{ $pub->isLocked() ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-bps-blue border border-blue-200' }}">
                {{ $pub->status }}
            </span>
        </div>
    </div>

    <!-- 2. CONTEXTUAL HERO BANNER IDENTITAS DIMENSI -->
    <div class="bg-gradient-to-r {{ $theme['hero_gradient'] }} text-white rounded-xl shadow-md border-l-8 p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-5">
        <div class="space-y-2 flex-1">
            <div class="inline-flex items-center space-x-2 border {{ $theme['dim_badge_class'] }} text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                <span>{{ $theme['dim_badge'] }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                Bab {{ $narrative->chapter_number }}: {{ $narrative->title_id }}
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 italic">
                {{ $narrative->title_en }} &bull; <span class="text-slate-200 font-semibold not-italic">{{ $pub->title }}</span> ({{ $theme['total_chapters_label'] }})
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <a href="{{ route('covers.index', ['publication_id' => $pub->id]) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg transition-colors backdrop-blur-xs">
                Pratinjau Pembatas Bab &rarr;
            </a>
            <a href="{{ route('editorial.index', ['publication_id' => $pub->id, 'type' => $type]) }}" class="inline-flex items-center justify-center px-3.5 py-2 text-xs font-bold text-slate-200 bg-slate-800/80 hover:bg-slate-800 border border-white/10 rounded-lg transition-colors">
                Daftar Bab {{ $theme['type_badge'] }}
            </a>
        </div>
    </div>

    <!-- 3. QUICK CHAPTER SWITCHER STRIP (NAVIGASI CEPAT ANTAR BAB) -->
    @if(isset($allChapters) && $allChapters->count() > 1)
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-3">
        <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">
                PILIH BAB LAINNYA DI PUBLIKASI INI ({{ $allChapters->count() }} BAB):
            </span>
            <span class="text-[11px] font-bold text-slate-600">
                Sedang mengedit Bab {{ $narrative->chapter_number }}
            </span>
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
            @foreach($allChapters as $ch)
            <a href="{{ route('editorial.edit', $ch->id) }}" 
               title="Bab {{ $ch->chapter_number }}: {{ $ch->title_id }}"
               class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 {{ $ch->id === $narrative->id ? ($isDda ? 'bg-emerald-600 text-white shadow-sm' : ($isSkd ? 'bg-indigo-600 text-white shadow-sm' : 'bg-bps-orange text-white shadow-sm')) : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200' }}">
                <span>Bab {{ $ch->chapter_number }}</span>
                @if($ch->id === $narrative->id)
                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($pub->isLocked())
    <!-- Lock Banner: APPROVED_LOCKED / FINAL_RELEASED -->
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-md p-4 flex items-start space-x-3 shadow-xs">
        <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
        <div class="text-sm text-amber-800">
            <strong>Publikasi berstatus {{ $pub->status }} dan sedang TERKUNCI.</strong>
            Penyuntingan teks ulasan dan pembatas bab dinonaktifkan demi integritas rilis resmi. Hubungi Administrator jika perlu membuka kunci revisi.
        </div>
    </div>
    @endif

    <!-- 4. PANDUAN REDAKSI & DYNAMIC TOKEN CHEAT-SHEET -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <span class="text-base">ðŸ’¡</span>
                <h2 class="text-xs font-black uppercase tracking-wider text-slate-800">
                    Panduan Redaksi & Variabel Dinamis (Token Helper) - {{ $theme['type_badge'] }}
                </h2>
            </div>
            <span class="text-[11px] text-slate-400">Klik token di bawah untuk otomatis menyisipkan ke naskah</span>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
            {{ $theme['guidance_desc'] }}
        </p>

        <!-- Dynamic Token Chips -->
        <div class="pt-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Token Tersedia:</span>
            <div class="flex flex-wrap gap-2">
                @foreach($theme['sample_tokens'] as $tokenKey => $tokenDesc)
                <button type="button" 
                        onclick="insertToken('{{ $tokenKey }}')" 
                        class="inline-flex items-center space-x-1.5 px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-bps-navy rounded-lg border border-slate-200 transition-colors text-xs font-mono shadow-2xs cursor-pointer group">
                    <span class="font-bold text-bps-blue group-hover:text-bps-navy">{{ $tokenKey }}</span>
                    <span class="text-[10px] text-slate-400 font-sans not-italic">({{ $tokenDesc }})</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 5. TWO-COLUMN BILINGUAL EDITOR FORM + SIDE PANEL DATA RESMI -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start">
    <form action="{{ route('editorial.update', $narrative->id) }}" method="POST" class="space-y-6 xl:col-span-3">
        @csrf
        @method('PUT')

        <!-- Highlight Key Figures Card -->
        <div class="{{ $theme['highlight_card'] }} rounded-xl p-5 border shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-black {{ $theme['highlight_label_color'] }} uppercase tracking-wider flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full {{ $theme['accent_bg'] }} mr-2"></span>
                    {{ $theme['highlight_title'] }}
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase">Halaman Pembatas Typst</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Label Indikator Kunci</label>
                    <input type="text" name="highlight_label" @disabled($pub->isLocked()) value="{{ old('highlight_label', $narrative->highlight_label) }}" placeholder="Contoh: Jumlah Penduduk / Luas Wilayah / IKK Pelayanan" class="w-full text-xs rounded-lg border-slate-300 {{ $theme['ring_color'] }} p-2.5 bg-white font-medium shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Angka Indikator</label>
                    <input type="text" name="highlight_value" @disabled($pub->isLocked()) value="{{ old('highlight_value', $narrative->highlight_value) }}" placeholder="Ketik angka resmi beserta satuannya, mis. 12.345 Jiwa / 1.234,56 km² / 88,40 (Sangat Baik)" class="w-full text-xs rounded-lg border-slate-300 {{ $theme['ring_color'] }} p-2.5 bg-white font-bold text-bps-navy shadow-2xs">
                </div>
            </div>

            {{-- Nomor bab dan judul bilingual pembatas bab. Ketiganya ikut
                 dirender instan oleh Live Interactive Divider Mockup di bawah. --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-100">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Bab</label>
                    <input type="text" disabled
                           value="{{ str_pad((string) $narrative->chapter_number, 2, '0', STR_PAD_LEFT) }}"
                           class="w-full text-xs rounded-lg border-slate-200 bg-slate-100 p-2.5 font-black text-bps-navy"
                           title="Nomor bab merupakan kunci urutan lembar buku dan tidak dapat diubah">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Bab (Indonesia)</label>
                    <input type="text" name="title_id" @disabled($pub->isLocked())
                           value="{{ old('title_id', $narrative->title_id) }}"
                           placeholder="Contoh: Keaginewaan Berdasarkan Marcellus" class="w-full text-xs rounded-lg border-slate-300 {{ $theme['ring_color'] }} p-2.5 bg-white font-semibold shadow-2xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Judul Bab (English)</label>
                    <input type="text" name="title_en" @disabled($pub->isLocked())
                           value="{{ old('title_en', $narrative->title_en) }}"
                           placeholder="e.g. Population Based on Marcellus" class="w-full text-xs rounded-lg border-slate-300 {{ $theme['ring_color'] }} p-2.5 bg-white italic shadow-2xs">
                </div>
            </div>
        </div>

        <!-- Bilingual Two-Column Editor -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-bps-navy flex items-center space-x-2">
                    <span>Struktur Naskah Ulasan Bilingual Berdampingan (Two-Column Layout)</span>
                </h2>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Format Standar Publikasi BPS RI</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Bahasa Indonesia -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-bps-navy uppercase flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full {{ $theme['accent_bg'] }} mr-2"></span>
                            Kolom Kiri: ULASAN (Bahasa Indonesia)
                        </label>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Heading: ULASAN</span>
                    </div>
                    <textarea id="narrative_id" name="narrative_id" rows="14" @disabled($pub->isLocked()) required class="w-full text-xs rounded-lg border-slate-300 {{ $theme['ring_color'] }} p-3.5 bg-slate-50 leading-relaxed font-sans shadow-2xs focus:bg-white transition-colors">{{ old('narrative_id', $narrative->narrative_id) }}</textarea>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span class="italic">Tip: Gunakan bahasa baku yang lugas dan mengacu pada tabel-tabel data resmi.</span>
                        <span id="counter_id" class="font-mono text-slate-400"></span>
                    </div>
                </div>

                <!-- Kolom Kanan: Bahasa Inggris -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-600 uppercase flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 mr-2"></span>
                            Kolom Kanan: DESCRIPTION (English)
                        </label>
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Heading: DESCRIPTION</span>
                    </div>
                    <textarea id="narrative_en" name="narrative_en" rows="14" @disabled($pub->isLocked()) required class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-500 focus:ring focus:ring-slate-500/20 p-3.5 bg-slate-50 leading-relaxed font-sans italic text-slate-700 shadow-2xs focus:bg-white transition-colors">{{ old('narrative_en', $narrative->narrative_en) }}</textarea>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span class="italic">Tip: Translated mirror description for international cataloging and readers.</span>
                        <span id="counter_en" class="font-mono text-slate-400"></span>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('editorial.index', ['publication_id' => $pub->id, 'type' => $type]) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors">
                    &larr; Batal &amp; Kembali
                </a>
                @unless($pub->isLocked())
                <button type="submit" class="inline-flex items-center px-6 py-2.5 {{ $theme['save_btn_class'] }} text-xs font-bold rounded-lg shadow-sm transition-all transform active:scale-98 cursor-pointer">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Ulasan Bab {{ $narrative->chapter_number }}
                </button>
                @endunless
            </div>
        </div>
    </form>
    <!-- PANEL DATA ANGKA RESMI + SARAN FRASA STANDAR BPS (Kolom Kanan) -->
    <aside class="xl:col-span-1 space-y-6">
        <!-- Panel ringkasan data angka resmi dari tabel terkait bab -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-bold text-bps-navy flex items-center mb-1">
                <svg class="w-4 h-4 mr-1.5 text-bps-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Data Angka Resmi Bab {{ $narrative->chapter_number }}
            </h3>
            <p class="text-[11px] text-slate-500 mb-3">Diambil langsung dari tabel database publikasi ini (tanpa angka tebakan).</p>

            @forelse($officialDataPanels ?? [] as $panel)
                <div class="border border-slate-200 rounded-lg p-3 mb-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[10px] font-mono font-bold text-bps-orange">Tabel {{ $panel['number'] }}</p>
                            <p class="text-[11px] font-semibold text-slate-700 leading-snug">{{ $panel['title'] }}</p>
                        </div>
                        @if($panel['verified'])
                            <span class="shrink-0 text-[9px] font-bold uppercase bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded-full">Terverifikasi</span>
                        @else
                            <span class="shrink-0 text-[9px] font-bold uppercase bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded-full">Belum Verifikasi</span>
                        @endif
                    </div>
                    @if(!empty($panel['headers']) && !empty($panel['rows']))
                    <div class="mt-2 overflow-x-auto">
                        <table class="w-full text-[10px] border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-800">
                                    @foreach($panel['headers'] as $h)
                                        <th class="text-left py-1 pr-2 font-bold text-slate-700">{{ $h }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($panel['rows'] as $row)
                                <tr class="border-b border-slate-200">
                                    @foreach(array_slice(array_values((array) $row), 0, count($panel['headers'])) as $cell)
                                        <td class="py-1 pr-2 text-slate-600">{{ is_scalar($cell) ? $cell : '—' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1.5">Sumber: {{ $panel['source'] ?? 'BPS Kabupaten Jember' }} &middot; {{ $panel['total_rows'] }} baris</p>
                    @else
                    <p class="text-[10px] text-slate-400 mt-2 italic">Tabel belum memuat baris data untuk ditampilkan.</p>
                    @endif
                </div>
            @empty
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-[11px] text-amber-800">
                Belum ada tabel teringesti untuk Bab {{ $narrative->chapter_number }}. Angka akan muncul otomatis setelah operator mengunggah berkas OPD.
            </div>
            @endforelse
        </div>

        <!-- Saran frasa standar narasi statistik BPS -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-bold text-bps-navy mb-1">Saran Frasa Standar BPS</h3>
            <p class="text-[11px] text-slate-500 mb-3">Klik frasa untuk menyisipkannya ke kolom ULASAN.</p>
            <div class="space-y-2">
                @foreach($phraseSuggestions ?? [] as $phrase)
                <button type="button" onclick="insertToken({{ Illuminate\Support\Js::from($phrase) }} + ' ')"
                        class="w-full text-left text-[11px] text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg p-2.5 transition-colors leading-snug">
                    {{ $phrase }}
                </button>
                @endforeach
            </div>
        </div>
    </aside>
    </div>
<!-- LIVE INTERACTIVE DIVIDER MOCKUP + WORD COUNT GAUGE -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Divider mockup: merender kartu pembatas bab secara instan -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Live Interactive Divider Mockup</h3>
                    <p class="text-[11px] text-slate-500">Kartu pembatas bab dirender instan mengikuti input Anda.</p>
                </div>
                <span class="text-[10px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">Typst divider</span>
            </div>

            <div id="divider-mockup" class="rounded-lg bg-white border-2 border-slate-200 p-6 flex flex-col justify-between min-h-[260px] shadow-inner">
                <div class="flex items-start justify-between">
                    <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400">
                        BPS Kabupaten Jember<br>{{ $pub->title }}
                    </div>
                    <div class="text-5xl font-black leading-none" style="color: {{ $theme['accent_text'] === 'text-emerald-700' ? '#047857' : ($theme['accent_text'] === 'text-indigo-700' ? '#4338CA' : '#D35400') }}" id="divider-number">
                        {{ str_pad((string) $narrative->chapter_number, 2, '0', STR_PAD_LEFT) }}
                    </div>
                </div>
                <div class="my-4">
                    <h4 id="divider-title-id" class="text-lg font-black uppercase text-bps-navy leading-tight">{{ $narrative->title_id }}</h4>
                    <p id="divider-title-en" class="text-xs italic text-slate-400">{{ $narrative->title_en }}</p>
                </div>
                <div class="rounded-lg p-4" style="background: {{ $theme['highlight_card'] }}">
                    <p id="divider-label" class="text-[9px] font-black uppercase tracking-wider text-slate-500">Indikator Kunci</p>
                    <p id="divider-value" class="text-2xl font-black text-bps-darkorange mt-1">{{ $narrative->highlight_value ?: '—' }}</p>
                    <p id="divider-label-2" class="text-[11px] font-semibold text-slate-600">{{ $narrative->highlight_label ?: 'Label indikator' }}</p>
                    <p id="divider-sample" class="text-[10px] text-slate-500 mt-2 italic border-t border-slate-300/50 pt-2 leading-snug">Sampel ulasan pembatas bab akan tampil di sini.</p>
                </div>
            </div>
        </div>

        <!-- Word Count Gauge: rekomendasi 100-300 kata + keseimbangan ID vs EN -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="text-sm font-bold text-bps-navy mb-1">Word Count Gauge &amp; Keseimbangan Bilingual</h3>
            <p class="text-[11px] text-slate-500 mb-4">Rekomendasi panjang narasi BPS: <strong>100&ndash;300 kata</strong> per kolom.</p>

            <!-- Gauge panjang narasi ID -->
            <div class="mb-4">
                <div class="flex items-center justify-between text-[11px] mb-1">
                    <span class="font-semibold text-slate-600">Panjang Narasi (Indonesia)</span>
                    <span id="gauge-id-label" class="font-mono font-bold text-slate-700">0 kata</span>
                </div>
                <div class="relative h-4 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                    <div class="absolute inset-y-0 left-0 bg-emerald-100" style="width: 100%;"></div>
                    <div class="absolute inset-y-0" style="left: 33.33%; width: 0.5px; background:#16A085;"></div>
                    <div class="absolute inset-y-0" style="left: 100%;"></div>
                    <div id="gauge-id-bar" class="absolute inset-y-0 left-0 bg-gradient-to-r from-sky-500 to-emerald-500 transition-all" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-[9px] text-slate-400 mt-1">
                    <span>0</span><span>100 (min)</span><span>300 (maks)</span><span>&gt;400</span>
                </div>
            </div>

            <!-- Bar perbandingan jumlah kata ID vs EN -->
            <div class="mb-2">
                <div class="flex items-center justify-between text-[11px] mb-1">
                    <span class="font-semibold text-slate-600">Keseimbangan ID vs EN</span>
                    <span id="balance-label" class="font-mono text-slate-500">—</span>
                </div>
                <div class="flex h-5 w-full rounded-full overflow-hidden border border-slate-200">
                    <div id="balance-id" class="bg-bps-navy flex items-center justify-center text-[9px] font-bold text-white transition-all" style="width: 50%">ID</div>
                    <div id="balance-en" class="bg-bps-orange flex items-center justify-center text-[9px] font-bold text-white transition-all" style="width: 50%">EN</div>
                </div>
                <p class="text-[10px] text-slate-400 mt-1.5">Idealnya kedua kolom seimbang (&plusmn;15% selisih jumlah kata).</p>
            </div>
        </div>
    </div>




    @unless($pub->isLocked())
    <!-- 6. PENGAJUAN PUBLIKASI KE MEJA APPROVER -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-sm font-bold text-bps-navy flex items-center space-x-1.5">
                <span>Selesai menyunting seluruh bab {{ $theme['type_badge'] }}?</span>
            </h3>
            <p class="text-xs text-slate-500 mt-1">
                Status publikasi saat ini: <strong class="text-bps-orange uppercase">{{ $pub->status }}</strong>.
                Pengajuan akan memajukan workflow ke meja Quality Control &amp; Approval.
            </p>
        </div>
        <form action="{{ route('editorial.submit', $narrative->id) }}" method="POST"
              onsubmit="return confirm('Ajukan publikasi ini ke meja Approver (PENDING_APPROVAL)? Pastikan seluruh bab telah selesai disunting.')">
            @csrf
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-bps-blue hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                Ajukan ke Approver
            </button>
        </form>
    </div>
    @endunless

</div>

<script>
    // Fungsi untuk menyisipkan dynamic token ke textarea ulasan Indonesia
    function insertToken(token) {
        const textarea = document.getElementById('narrative_id');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + token + text.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + token.length;
        updateCounts();
    }

    function updateCounts() {
        const tId = document.getElementById('narrative_id');
        const tEn = document.getElementById('narrative_en');
        const cId = document.getElementById('counter_id');
        const cEn = document.getElementById('counter_en');

        const idWords = tId && tId.value.trim() ? tId.value.trim().split(/\s+/).length : 0;
        const enWords = tEn && tEn.value.trim() ? tEn.value.trim().split(/\s+/).length : 0;

        if (tId && cId) {
            cId.textContent = idWords + ' kata';
        }
        if (tEn && cEn) {
            cEn.textContent = enWords + ' words';
        }

        // Word Count Gauge: skala linear 0-400 kata (min 100, maks 300).
        const gaugeBar = document.getElementById('gauge-id-bar');
        const gaugeLabel = document.getElementById('gauge-id-label');
        if (gaugeBar && gaugeLabel) {
            const pct = Math.min(100, (idWords / 400) * 100);
            gaugeBar.style.width = pct + '%';
            gaugeBar.classList.remove('from-sky-500', 'from-amber-500', 'from-rose-500', 'to-emerald-500', 'to-amber-400', 'to-rose-500');
            if (idWords < 100) {
                gaugeBar.classList.add('from-rose-500', 'to-rose-500');
            } else if (idWords <= 300) {
                gaugeBar.classList.add('from-sky-500', 'to-emerald-500');
            } else {
                gaugeBar.classList.add('from-amber-500', 'to-amber-400');
            }
            gaugeLabel.textContent = idWords + ' kata';
            gaugeLabel.className = 'font-mono font-bold ' + (idWords >= 100 && idWords <= 300 ? 'text-emerald-700' : (idWords === 0 ? 'text-slate-400' : 'text-amber-700'));
        }

        // Keseimbangan jumlah kata ID vs EN.
        const balId = document.getElementById('balance-id');
        const balEn = document.getElementById('balance-en');
        const balLabel = document.getElementById('balance-label');
        if (balId && balEn) {
            const total = idWords + enWords;
            const idPct = total > 0 ? Math.round((idWords / total) * 100) : 50;
            balId.style.width = idPct + '%';
            balEn.style.width = (100 - idPct) + '%';
            if (balLabel) {
                if (total === 0) {
                    balLabel.textContent = '—';
                } else {
                    const diff = Math.abs(idWords - enWords);
                    const pctDiff = idWords > 0 ? Math.round((diff / Math.max(idWords, 1)) * 100) : 0;
                    balLabel.textContent = 'ID ' + idWords + ' / EN ' + enWords + (pctDiff <= 15 ? ' · seimbang' : ' · selisih ' + pctDiff + '%');
                    balLabel.className = 'font-mono ' + (pctDiff <= 15 ? 'text-emerald-700' : 'text-amber-700');
                }
            }
        }

        // Live Divider Mockup: sampel ulasan bab pertama dari kolom ID.
        const dividerSample = document.getElementById('divider-sample');
        if (dividerSample && tId) {
            const sample = tId.value.trim().split(/\s+/).slice(0, 24).join(' ');
            dividerSample.textContent = sample ? sample + '…' : 'Sampel ulasan pembatas bab akan tampil di sini.';
        }

        // Live Divider Mockup: nomor bab, judul bilingual, dan angka indikator kunci.
        const hLabel = document.querySelector('input[name="highlight_label"]');
        const hValue = document.querySelector('input[name="highlight_value"]');
        const titleInputId = document.querySelector('input[name="title_id"]');
        const titleInputEn = document.querySelector('input[name="title_en"]');
        const titleId = document.getElementById('divider-title-id');
        const titleEn = document.getElementById('divider-title-en');
        const dLabel = document.getElementById('divider-label');
        const dLabel2 = document.getElementById('divider-label-2');
        const dValue = document.getElementById('divider-value');

        if (hLabel && dLabel2) dLabel2.textContent = hLabel.value || 'Label indikator';
        if (hValue && dValue) dValue.textContent = hValue.value || '—';
        // Judul bilingual dibaca langsung dari input form sehingga mockup ikut
        // berubah seketika saat editor mengetik.
        if (titleId && titleInputId) titleId.textContent = titleInputId.value || 'Judul Bab Belum Ada Data';
        if (titleEn && titleInputEn) titleEn.textContent = titleInputEn.value || 'Chapter title not yet available';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateCounts();
        const tId = document.getElementById('narrative_id');
        const tEn = document.getElementById('narrative_en');
        if (tId) tId.addEventListener('input', updateCounts);
        if (tEn) tEn.addEventListener('input', updateCounts);
        document.querySelectorAll(
            'input[name="highlight_label"], input[name="highlight_value"], input[name="title_id"], input[name="title_en"]'
        ).forEach(function (el) {
            el.addEventListener('input', updateCounts);
        });
    });
</script>
@endsection
