@extends('layouts.app', ['title' => 'Redaksi Ulasan (' . ($activeType === 'KDA' ? 'KCA / KDA' : $activeType) . ') - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- 1. TOP NAVIGATION TABS: 3 DIMENSI PUBLIKASI (DDA, KCA/KDA, SKD) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 sm:p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 px-1">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">PILIH AREA REDAKSI PUBLIKASI</span>
                <h1 class="text-lg font-bold text-bps-navy">Meja Kerja Redaksi Bahasa & Ulasan Bilingual</h1>
            </div>
            <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 w-fit">
                <span class="text-slate-400 font-medium">Tahun Terbit:</span>
                <span class="text-bps-navy">{{ $activeYear ?? 'Semua Tahun' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- TAB 1: KCA / KDA (KECAMATAN DALAM ANGKA) -->
            <a href="{{ route('editorial.index', ['type' => 'KDA']) }}" 
               class="relative rounded-xl p-4 transition-all border-2 text-left flex items-start space-x-3.5 {{ $activeType === 'KDA' ? 'border-bps-orange bg-orange-50/70 shadow-sm ring-2 ring-orange-200' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100 text-slate-600' }}">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-xl flex-shrink-0 {{ $activeType === 'KDA' ? 'bg-bps-orange text-white shadow-md' : 'bg-slate-200 text-slate-600' }}">
                    🌾
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm {{ $activeType === 'KDA' ? 'text-bps-darkorange' : 'text-slate-800' }}">
                            KCA / KDA
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $activeType === 'KDA' ? 'bg-bps-orange text-white' : 'bg-slate-200 text-slate-600' }}">
                            {{ $kdaPublications->count() }} Kecamatan
                        </span>
                    </div>
                    <p class="text-xs font-bold text-slate-700 mt-0.5 truncate">Kecamatan Dalam Angka</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">7 Bab standar per kecamatan se-Kabupaten Jember.</p>
                </div>
                @if($activeType === 'KDA')
                <div class="absolute -top-2.5 right-3 bg-bps-orange text-white text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm tracking-wider">
                    Sedang Aktif
                </div>
                @endif
            </a>

            <!-- TAB 2: DDA (KABUPATEN DALAM ANGKA) -->
            <a href="{{ route('editorial.index', ['type' => 'DDA']) }}" 
               class="relative rounded-xl p-4 transition-all border-2 text-left flex items-start space-x-3.5 {{ $activeType === 'DDA' ? 'border-emerald-600 bg-emerald-50/70 shadow-sm ring-2 ring-emerald-200' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100 text-slate-600' }}">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-xl flex-shrink-0 {{ $activeType === 'DDA' ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-200 text-slate-600' }}">
                    🏛️
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm {{ $activeType === 'DDA' ? 'text-emerald-800' : 'text-slate-800' }}">
                            DDA
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $activeType === 'DDA' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                            Publikasi Induk
                        </span>
                    </div>
                    <p class="text-xs font-bold text-slate-700 mt-0.5 truncate">Kabupaten Dalam Angka</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">13 Bab komprehensif tingkat makro Kabupaten Jember.</p>
                </div>
                @if($activeType === 'DDA')
                <div class="absolute -top-2.5 right-3 bg-emerald-600 text-white text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm tracking-wider">
                    Sedang Aktif
                </div>
                @endif
            </a>

            <!-- TAB 3: SKD (SURVEI KEBUTUHAN DATA) -->
            <a href="{{ route('editorial.index', ['type' => 'SKD']) }}" 
               class="relative rounded-xl p-4 transition-all border-2 text-left flex items-start space-x-3.5 {{ $activeType === 'SKD' ? 'border-indigo-600 bg-indigo-50/70 shadow-sm ring-2 ring-indigo-200' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50 hover:bg-slate-100 text-slate-600' }}">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-xl flex-shrink-0 {{ $activeType === 'SKD' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-200 text-slate-600' }}">
                    📊
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="font-black text-sm {{ $activeType === 'SKD' ? 'text-indigo-800' : 'text-slate-800' }}">
                            SKD
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $activeType === 'SKD' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }}">
                            Survei Kepuasan
                        </span>
                    </div>
                    <p class="text-xs font-bold text-slate-700 mt-0.5 truncate">Survei Kebutuhan Data</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">5 Bab analisis IKK, IPAK, dan evaluasi layanan PST.</p>
                </div>
                @if($activeType === 'SKD')
                <div class="absolute -top-2.5 right-3 bg-indigo-600 text-white text-[9px] font-black uppercase px-2.5 py-0.5 rounded-full shadow-sm tracking-wider">
                    Sedang Aktif
                </div>
                @endif
            </a>
        </div>
    </div>

    <!-- 2. CONTEXTUAL WORKSPACE BANNER SESUAI DIMENSI AKTIF -->
    @if($activeType === 'KDA')
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 text-white rounded-xl shadow-md border-l-8 border-bps-orange p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1.5 flex-1">
            <div class="inline-flex items-center space-x-2 bg-bps-orange/20 border border-bps-orange/40 text-amber-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                <span>🌾</span>
                <span>AREA EDITOR: KCA / KDA (KECAMATAN DALAM ANGKA)</span>
            </div>
            <h2 class="text-xl font-black text-white tracking-tight">
                {{ $selectedPub?->title ?? 'Pilih Kecamatan' }}
            </h2>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                Anda berada di modul redaksi ulasan bilingual tingkat kecamatan. Setiap buku memuat <strong>7 Bab</strong> ulasan standar yang mencakup Geografi, Pemerintahan, Penduduk, Sosial, Pertanian, Pariwisata, dan Perbankan.
            </p>
        </div>

        <!-- Quick Switcher Dropdown (KCA) -->
        <div class="bg-white/10 p-3.5 rounded-xl border border-white/15 backdrop-blur-sm sm:min-w-[320px]">
            <form method="GET" action="{{ route('editorial.index') }}" class="space-y-2">
                <input type="hidden" name="type" value="KDA">
                <label for="pub-select-kda" class="block text-[11px] font-bold text-amber-300 uppercase tracking-wider">
                    Ganti Kecamatan (31 Kecamatan):
                </label>
                <select id="pub-select-kda" name="publication_id" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-white/20 bg-slate-900/90 text-white p-2.5 font-semibold focus:border-bps-orange focus:ring focus:ring-bps-orange/30 cursor-pointer">
                    <optgroup label="🌾 Kecamatan Dalam Angka (31 Kecamatan)">
                        @foreach($kdaPublications as $pub)
                            <option value="{{ $pub->id }}" {{ $selectedPub?->id == $pub->id ? 'selected' : '' }}>
                                {{ $pub->title }}{{ $pub->isLocked() ? ' - TERKUNCI (' . $pub->status . ')' : '' }}
                            </option>
                        @endforeach
                    </optgroup>
                    @if($publications->where('type', '!=', 'KDA')->isNotEmpty())
                    <optgroup label="Publikasi Dimensi Lainnya">
                        @foreach($publications->where('type', '!=', 'KDA') as $otherPub)
                            <option value="{{ $otherPub->id }}" {{ $selectedPub?->id == $otherPub->id ? 'selected' : '' }}>
                                [{{ $otherPub->type }}] {{ $otherPub->title }}
                            </option>
                        @endforeach
                    </optgroup>
                    @endif
                </select>
            </form>
        </div>
    </div>
    @elseif($activeType === 'DDA')
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white rounded-xl shadow-md border-l-8 border-emerald-500 p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1.5 flex-1">
            <div class="inline-flex items-center space-x-2 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                <span>🏛️</span>
                <span>AREA EDITOR: DDA (KABUPATEN DALAM ANGKA)</span>
            </div>
            <h2 class="text-xl font-black text-white tracking-tight">
                {{ $selectedPub?->title ?? 'Kabupaten Jember Dalam Angka' }}
            </h2>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                Anda berada di modul redaksi ulasan bilingual publikasi induk tingkat makro Kabupaten Jember. Buku ini memuat <strong>13 Bab</strong> komprehensif dari geografi hingga pendapatan regional.
            </p>
        </div>

        <!-- Quick Switcher Dropdown (DDA) -->
        <div class="bg-white/10 p-3.5 rounded-xl border border-white/15 backdrop-blur-sm sm:min-w-[320px]">
            <form method="GET" action="{{ route('editorial.index') }}" class="space-y-2">
                <input type="hidden" name="type" value="DDA">
                <label for="pub-select-dda" class="block text-[11px] font-bold text-emerald-300 uppercase tracking-wider">
                    Pilih Edisi DDA:
                </label>
                <select id="pub-select-dda" name="publication_id" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-white/20 bg-slate-900/90 text-white p-2.5 font-semibold focus:border-emerald-400 focus:ring focus:ring-emerald-400/30 cursor-pointer">
                    <optgroup label="🏛️ Kabupaten Dalam Angka (DDA)">
                        @foreach($ddaPublications as $pub)
                            <option value="{{ $pub->id }}" {{ $selectedPub?->id == $pub->id ? 'selected' : '' }}>
                                {{ $pub->title }}{{ $pub->isLocked() ? ' - TERKUNCI (' . $pub->status . ')' : '' }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Semua Publikasi">
                        @foreach($publications as $p)
                            <option value="{{ $p->id }}" {{ $selectedPub?->id == $p->id ? 'selected' : '' }}>
                                [{{ $p->type }}] {{ $p->title }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </form>
        </div>
    </div>
    @elseif($activeType === 'SKD')
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 text-white rounded-xl shadow-md border-l-8 border-indigo-500 p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1.5 flex-1">
            <div class="inline-flex items-center space-x-2 bg-indigo-500/20 border border-indigo-500/40 text-indigo-300 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">
                <span>📊</span>
                <span>AREA EDITOR: SKD (SURVEI KEBUTUHAN DATA)</span>
            </div>
            <h2 class="text-xl font-black text-white tracking-tight">
                {{ $selectedPub?->title ?? 'Survei Kebutuhan Data BPS Jember' }}
            </h2>
            <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                Anda berada di modul redaksi ulasan tematik survei konsumen. Dokumen ini memuat <strong>5 Bab</strong> analisis hasil survei, IKK, IPAK, dan evaluasi Pelayanan Statistik Terpadu (PST).
            </p>
        </div>

        <!-- Quick Switcher Dropdown (SKD) -->
        <div class="bg-white/10 p-3.5 rounded-xl border border-white/15 backdrop-blur-sm sm:min-w-[320px]">
            <form method="GET" action="{{ route('editorial.index') }}" class="space-y-2">
                <input type="hidden" name="type" value="SKD">
                <label for="pub-select-skd" class="block text-[11px] font-bold text-indigo-300 uppercase tracking-wider">
                    Pilih Edisi SKD:
                </label>
                <select id="pub-select-skd" name="publication_id" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-white/20 bg-slate-900/90 text-white p-2.5 font-semibold focus:border-indigo-400 focus:ring focus:ring-indigo-400/30 cursor-pointer">
                    <optgroup label="📊 Survei Kebutuhan Data (SKD)">
                        @foreach($skdPublications as $pub)
                            <option value="{{ $pub->id }}" {{ $selectedPub?->id == $pub->id ? 'selected' : '' }}>
                                {{ $pub->title }}{{ $pub->isLocked() ? ' - TERKUNCI (' . $pub->status . ')' : '' }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Semua Publikasi">
                        @foreach($publications as $p)
                            <option value="{{ $p->id }}" {{ $selectedPub?->id == $p->id ? 'selected' : '' }}>
                                [{{ $p->type }}] {{ $p->title }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </form>
        </div>
    </div>
    @endif

<!-- 2b. RINGKASAN BILINGUAL COMPLETENESS INDEX & STATUS CHIP -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kartu indeks kelengkapan bilingual -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col sm:flex-row items-center gap-5">
            @php
                $idxCircumference = 2 * M_PI * 40;
                $idxDash = ($bilingualIndex / 100) * $idxCircumference;
                $idxColor = $bilingualIndex >= 80 ? '#16A085' : ($bilingualIndex >= 40 ? '#E67E22' : '#C0392B');
            @endphp
            <svg viewBox="0 0 100 100" class="w-28 h-28 shrink-0" role="img" aria-label="Bilingual Completeness Index">
                <circle cx="50" cy="50" r="40" fill="none" stroke="#E2E8F0" stroke-width="10"/>
                <g transform="rotate(-90 50 50)">
                    <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $idxColor }}" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="{{ number_format($idxDash, 2, '.', '') }} {{ number_format($idxCircumference - $idxDash, 2, '.', '') }}"/>
                </g>
                <text x="50" y="48" text-anchor="middle" font-size="20" font-weight="900" fill="#0A3866">{{ $bilingualIndex }}%</text>
                <text x="50" y="63" text-anchor="middle" font-size="8" fill="#64748B">BILINGUAL</text>
            </svg>
            <div class="min-w-0">
                <h3 class="text-sm font-bold text-bps-navy">Bilingual Completeness Index</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    {{ $totalEnFilled }} dari <strong>{{ $totalNarratives }}</strong> naskah ({{ $bilingualIndex }}%)
                    sudah memuat terjemahan bahasa Inggris pada tahun {{ $activeYear ?? 'aktif' }}.
                </p>
                <div class="mt-2 flex flex-wrap gap-2 text-[10px] font-bold">
                    <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded">Total Naskah: {{ $totalNarratives }}</span>
                    <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded" title="Naskah yang sudah terisi bahasa Indonesia">Terisi ID: {{ $idFilledNarratives }} ({{ $idFilledPercent }}%)</span>
                    <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded" title="Naskah yang sudah terisi bahasa Inggris">Terisi EN: {{ $totalEnFilled }} ({{ $bilingualIndex }}%)</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-2 uppercase tracking-wide font-bold">
                    Status Alur Publikasi ( satuan: buku)
                </p>
                <div class="mt-1 flex flex-wrap gap-2 text-[10px] font-bold">
                    <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded">Siap Review: {{ $reviewReadyCount }}</span>
                    <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded">Draf: {{ $draftCount }}</span>
                </div>
            </div>
        </div>

        <!-- Radial meter kelengkapan per buku publikasi -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Kelengkapan Narasi per Buku Publikasi</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Radial meter persentase ulasan (ID) dan Description (EN) per publikasi.</p>
                </div>
                <div class="flex items-center space-x-3 text-[10px] font-bold">
                    <span class="inline-flex items-center text-bps-navy"><span class="w-2 h-2 rounded-full bg-bps-navy mr-1"></span>ID</span>
                    <span class="inline-flex items-center text-bps-orange"><span class="w-2 h-2 rounded-full bg-bps-orange mr-1"></span>EN</span>
                </div>
            </div>

            @if(!empty($bookCompleteness))
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($bookCompleteness as $book)
                    @php
                        $c = 2 * M_PI * 26;
                        $idDash = ($book['id_pct'] / 100) * $c;
                        $enDash = ($book['en_pct'] / 100) * $c;
                    @endphp
                    <div class="flex flex-col items-center text-center border border-slate-200 rounded-xl p-3 hover:shadow-sm transition-shadow">
                        <svg viewBox="0 0 80 80" class="w-20 h-20" role="img" aria-label="Kelengkapan {{ $book['title'] }}">
                            <g transform="rotate(-90 40 40)">
                                <circle cx="40" cy="40" r="26" fill="none" stroke="#0A3866" stroke-width="7"
                                        stroke-dasharray="{{ number_format($idDash, 2, '.', '') }} {{ number_format($c - $idDash, 2, '.', '') }}"/>
                                <circle cx="40" cy="40" r="16" fill="none" stroke="#E67E22" stroke-width="7"
                                        stroke-dasharray="{{ number_format($enDash, 2, '.', '') }} {{ number_format($c - $enDash, 2, '.', '') }}"/>
                            </g>
                            <text x="40" y="43" text-anchor="middle" font-size="12" font-weight="800" fill="#0A3866">{{ $book['en_pct'] }}%</text>
                        </svg>
                        <p class="text-[10px] font-bold text-slate-700 mt-1 line-clamp-2" title="{{ $book['title'] }}">{{ $book['district'] ?? $book['title'] }}</p>
                        <span class="text-[9px] font-bold uppercase tracking-wide {{ $book['type'] == 'KDA' ? 'text-bps-orange' : ($book['type'] == 'DDA' ? 'text-emerald-700' : 'text-indigo-700') }}">{{ $book['type'] }}</span>
                    </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada publikasi pada tahun aktif.</p>
            @endif
        </div>
    </div>
    <!-- 3. DETAIL PUBLIKASI & DAFTAR BAB -->
    @if($selectedPub)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <!-- Header Info Bar -->
        <div class="p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $selectedPub->type === 'DDA' ? 'bg-emerald-100 text-emerald-800' : ($selectedPub->type === 'SKD' ? 'bg-indigo-100 text-indigo-800' : 'bg-orange-100 text-bps-darkorange') }}">
                        {{ $selectedPub->type === 'KDA' ? 'KCA / KDA' : $selectedPub->type }}
                    </span>
                    <h3 class="text-base font-bold text-bps-navy">{{ $selectedPub->title }}</h3>
                </div>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>No. Katalog: <strong class="text-slate-700">{{ $selectedPub->catalog_number ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>No. Publikasi: <strong class="text-slate-700">{{ $selectedPub->publication_number ?? '-' }}</strong></span>
                    <span>&bull;</span>
                    <span>Status: <strong class="text-bps-orange uppercase">{{ $selectedPub->status }}</strong></span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('covers.index', ['publication_id' => $selectedPub->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-bps-blue hover:text-bps-navy bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
                    Pratinjau Pembatas Bab &rarr;
                </a>
                
                @if(in_array($selectedPub->status, ['DATA_INGESTED', 'IN_EDITORIAL']) && $selectedPub->narratives->isNotEmpty())
                <form action="{{ route('editorial.submit', $selectedPub->narratives->first()->id) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('Ajukan publikasi ini ke meja Approver untuk Quality Control & Kunci Status?')" class="inline-flex items-center px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Ajukan ke Approver
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Chapter Narratives List -->
        <div class="divide-y divide-slate-100">
            @php
                // Ikon tematik vektor representatif untuk 7 bab standar publikasi BPS.
                $chapterIcons = [
                    1 => ['label' => 'Geografi', 'path' => 'M6 20l5-11 4 8 3-5 3 8M3 20h18'],
                    2 => ['label' => 'Pemerintahan', 'path' => 'M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6'],
                    3 => ['label' => 'Kependudukan', 'path' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a3 3 0 11-6 0 3 3 0 016 0z'],
                    4 => ['label' => 'Sosial', 'path' => 'M12 21s-6-4.35-6-9a4 4 0 016-3.46A4 4 0 0118 12c0 4.65-6 9-6 9z'],
                    5 => ['label' => 'Pertanian', 'path' => 'M5 21c8 0 14-6 14-14-8 0-14 6-14 14z'],
                    6 => ['label' => 'Pariwisata', 'path' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.36-6.36l-.7.7M6.34 17.66l-.7.7m12.72 0l-.7-.7M6.34 6.34l-.7-.7M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
                    7 => ['label' => 'Perbankan', 'path' => 'M3 21h18M4 10h16M12 3l8 5H4l8-5zM6 10v8m4-8v8m4-8v8m4-8v8'],
                ];
            @endphp
            @forelse($selectedPub->narratives as $nar)
            @php
                $chapIcon = $chapterIcons[$nar->chapter_number] ?? ['label' => 'Umum', 'path' => 'M4 6h16M4 12h16M4 18h10'];
                $enFilled = trim((string) $nar->narrative_en) !== '';
                $idFilled = trim((string) $nar->narrative_id) !== '';
                $readyForReview = $idFilled && $enFilled;
            @endphp
            <div class="p-5 hover:bg-slate-50/70 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center space-x-3">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm {{ $selectedPub->type === 'DDA' ? 'bg-emerald-100 text-emerald-800' : ($selectedPub->type === 'SKD' ? 'bg-indigo-100 text-indigo-800' : 'bg-orange-100 text-bps-orange') }}">
                            {{ $nar->chapter_number }}
                        </span>
                        <span class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center shrink-0" title="Ikon tematik: {{ $chapIcon['label'] }}">
                            <svg class="w-5 h-5 text-bps-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $chapIcon['path'] }}"/></svg>
                        </span>
                        <div class="min-w-0">
                            <div class="flex items-center flex-wrap gap-2">
                                <h4 class="font-bold text-sm text-bps-navy">{{ $nar->title_id }}</h4>
                                @if($readyForReview)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span> Siap Review
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1"></span> Draf
                                    </span>
                                @endif
                                <span class="text-[9px] font-mono text-slate-400">{{ $chapIcon['label'] }}</span>
                            </div>
                            <p class="text-xs text-slate-400 italic">{{ $nar->title_en ?: '— belum ada terjemahan' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 bg-slate-50 p-3 rounded-xl border border-slate-200/70 text-xs">
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-500 uppercase block mb-1">ULASAN (INDONESIA)</span>
                            <p class="text-slate-700 line-clamp-2 leading-relaxed">{{ $nar->narrative_id ?: 'Belum ada narasi ulasan.' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-500 uppercase block mb-1">DESCRIPTION (ENGLISH)</span>
                            <p class="text-slate-500 italic line-clamp-2 leading-relaxed">{{ $nar->narrative_en ?: 'No english description available.' }}</p>
                        </div>
                    </div>

                    @if($nar->highlight_label && $nar->highlight_value)
                    <div class="flex items-center space-x-2 mt-1.5 text-xs">
                        <span class="text-slate-500 font-medium">Indikator Kunci:</span>
                        <span class="font-bold {{ $selectedPub->type === 'DDA' ? 'text-emerald-700' : ($selectedPub->type === 'SKD' ? 'text-indigo-700' : 'text-bps-darkorange') }}">
                            {{ $nar->highlight_value }}
                        </span>
                        <span class="text-slate-400">({{ $nar->highlight_label }})</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('pub.workspace', $selectedPub->id) }}" class="inline-flex items-center px-3 py-2 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        Workspace
                    </a>
                    <a href="{{ route('editorial.edit', $nar->id) }}" class="inline-flex items-center px-4 py-2 {{ $selectedPub->type === 'DDA' ? 'bg-emerald-700 hover:bg-emerald-800' : ($selectedPub->type === 'SKD' ? 'bg-indigo-700 hover:bg-indigo-800' : 'bg-bps-navy hover:bg-bps-darknavy') }} text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Sunting Ulasan
                    </a>
                </div>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400 text-xs">
                Belum ada data ulasan bab untuk publikasi ini.
            </div>
            @endforelse
        </div>
    </div>
    @endif
<!-- 4. PANDUAN KAIDAH PENULISAN NARASI ANGKA STATISTIK BPS -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Kaidah Penulisan Narasi Angka Statistik BPS</h2>
                <p class="text-xs text-slate-500 mt-0.5">Acuan penyuntingan ulasan bab agar konsisten dengan pedoman resmi BPS RI.</p>
            </div>
            <span class="text-[11px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">Pedoman BPS</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="font-black text-bps-navy text-[11px] uppercase tracking-wide mb-1">1 &middot; Sajikan Angka Faktual</p>
                <p class="text-slate-600 leading-relaxed">Mulai ulasan dengan angka kunci tahun berjalan, lalu bandingkan dengan tahun sebelumnya untuk menonjolkan arah perubahan.</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="font-black text-bps-navy text-[11px] uppercase tracking-wide mb-1">2 &middot; Format Angka Baku</p>
                <p class="text-slate-600 leading-relaxed">Gunakan pemisah ribuan titik (mis. 2.564.890) dan desimal koma (mis. 3,45). Tulis satuan pada setiap nilai.</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="font-black text-bps-navy text-[11px] uppercase tracking-wide mb-1">3 &middot; Bahasa Lugas &amp; Objektif</p>
                <p class="text-slate-600 leading-relaxed">Hindari kata subjektif dan opini. Gunakan kalimat pasif statistik baku serta istilah yang terdaftar pada glosarium.</p>
            </div>
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <p class="font-black text-bps-navy text-[11px] uppercase tracking-wide mb-1">4 &middot; Bilingual Sejajar</p>
                <p class="text-slate-600 leading-relaxed">Terjemahan bahasa Inggris wajib sepadan makna dengan versi Indonesia, termasuk angka dan satuannya.</p>
            </div>
        </div>
    </div>

    <!-- 5. GLOSARIUM ISTILAH RESMI STATISTIK -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Glosarium Istilah Resmi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Referensi cepat penulisan istilah statistik yang baku pada narasi publikasi.</p>
            </div>
        </div>
        @php
            $glossary = [
                ['id' => 'KDA', 'full' => 'Kecamatan Dalam Angka', 'en' => 'District in Figures'],
                ['id' => 'DDA', 'full' => 'Kabupaten Dalam Angka', 'en' => 'Regency in Figures'],
                ['id' => 'SKD', 'full' => 'Survei Kebutuhan Data', 'en' => 'Data Needs Survey'],
                ['id' => 'IKK', 'full' => 'Indeks Kepuasan Konsumen', 'en' => 'Consumer Satisfaction Index'],
                ['id' => 'IPAK', 'full' => 'Indeks Persepsi Anti Korupsi', 'en' => 'Anti-Corruption Perception Index'],
                ['id' => 'PST', 'full' => 'Pelayanan Statistik Terpadu', 'en' => 'Integrated Statistical Services'],
                ['id' => 'PDRB', 'full' => 'Produk Domestik Regional Bruto', 'en' => 'Gross Regional Domestic Product'],
                ['id' => 'TPT', 'full' => 'Tingkat Pengangguran Terbuka', 'en' => 'Open Unemployment Rate'],
            ];
        @endphp
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-2.5 px-4">Singkatan</th>
                        <th class="py-2.5 px-4">Kepanjangan (Indonesia)</th>
                        <th class="py-2.5 px-4">English</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($glossary as $g)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-2.5 px-4 font-mono font-bold text-bps-navy">{{ $g['id'] }}</td>
                        <td class="py-2.5 px-4 text-slate-700">{{ $g['full'] }}</td>
                        <td class="py-2.5 px-4 text-slate-500 italic">{{ $g['en'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
