@extends('layouts.app', ['title' => 'Workspace: ' . $publication->title . ' — SI-PENA'])

@section('content')
@php
    $type   = $publication->type;
    $isKda  = $type === 'KDA';
    $isDda  = $type === 'DDA';
    $isSkd  = $type === 'SKD';

    $themeAccent = $isKda ? 'orange' : ($isDda ? 'emerald' : 'indigo');

    $badgeClass = match($publication->status) {
        'PENDING_DATA'       => 'bg-slate-200 text-slate-700',
        'DATA_INGESTED'      => 'bg-blue-100 text-blue-800',
        'IN_EDITORIAL'       => 'bg-amber-100 text-amber-800',
        'PENDING_APPROVAL'   => 'bg-purple-100 text-purple-800',
        'APPROVED_LOCKED'    => 'bg-emerald-100 text-emerald-800',
        'FINAL_RELEASED'     => 'bg-green-200 text-green-900',
        default              => 'bg-slate-100 text-slate-600',
    };

    $typeBadge = match($type) {
        'KDA' => ['label' => 'KCA / KDA', 'class' => 'bg-orange-100 text-bps-darkorange'],
        'DDA' => ['label' => 'DDA', 'class' => 'bg-emerald-100 text-emerald-800'],
        'SKD' => ['label' => 'SKD', 'class' => 'bg-indigo-100 text-indigo-800'],
        default => ['label' => $type, 'class' => 'bg-slate-100 text-slate-700'],
    };

    $accentBg     = $isKda ? 'bg-bps-orange'    : ($isDda ? 'bg-emerald-600'  : 'bg-indigo-600');
    $accentHover  = $isKda ? 'hover:bg-bps-darkorange' : ($isDda ? 'hover:bg-emerald-700' : 'hover:bg-indigo-700');
    $accentText   = $isKda ? 'text-bps-darkorange' : ($isDda ? 'text-emerald-700' : 'text-indigo-700');
    $accentBorder = $isKda ? 'border-bps-orange' : ($isDda ? 'border-emerald-500' : 'border-indigo-500');
    $accentRing   = $isKda ? 'focus:ring-bps-orange/30' : ($isDda ? 'focus:ring-emerald-400/30' : 'focus:ring-indigo-400/30');
@endphp

<div
    x-data="{
        activePage: 'cover',
        saving: false,
        setPage(key) { this.activePage = key; window.scrollTo({ top: 0, behavior: 'smooth' }); }
    }"
    class="space-y-0"
>

{{-- ══════════════════════════════════════════════════════════
     HEADER BAR
═══════════════════════════════════════════════════════════ --}}
<div class="bg-white border border-slate-200 rounded-xl shadow-sm mb-4 p-4">
    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-400 font-medium mb-2 flex items-center space-x-1.5">
        <a href="{{ route('dashboard') }}" class="hover:text-bps-navy">Dashboard</a>
        <span>/</span>
        <a href="{{ route('editorial.index', ['type' => $type]) }}" class="hover:text-bps-navy">
            {{ $typeBadge['label'] }}
        </a>
        <span>/</span>
        <span class="text-slate-600 font-semibold">{{ $publication->title }}</span>
    </nav>

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center space-x-3">
            <span class="text-xs font-black px-2 py-0.5 rounded {{ $typeBadge['class'] }}">{{ $typeBadge['label'] }}</span>
            <div>
                <h1 class="text-lg font-black text-bps-navy leading-tight">{{ $publication->title }}</h1>
                <div class="flex items-center space-x-2 mt-0.5">
                    <span class="text-xs font-bold px-2 py-0.5 rounded {{ $badgeClass }}">{{ $publication->status }}</span>
                    <span class="text-xs text-slate-400">Tahun {{ $publication->year }}</span>
                    @if($publication->catalog_number)
                    <span class="text-xs text-slate-400">· Katalog: {{ $publication->catalog_number }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            {{-- Generate Draft --}}
            <form action="{{ route('pub.generate', $publication) }}" method="POST">
                @csrf
                <button type="submit"
                    onclick="return confirm('Masukkan job kompilasi PDF Draft ke antrean?')"
                    class="inline-flex items-center px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    🔄 Generate Draft PDF
                </button>
            </form>

            {{-- Generate Final (hanya jika approved/released) --}}
            @if(in_array($publication->status, ['APPROVED_LOCKED', 'FINAL_RELEASED']))
            <form action="{{ route('pub.generate', $publication) }}" method="POST">
                @csrf
                <button type="submit"
                    onclick="return confirm('Kompilasi PDF Final dari publikasi TERKUNCI ini?')"
                    class="inline-flex items-center px-3 py-1.5 {{ $accentBg }} {{ $accentHover }} text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    📄 Generate Final PDF
                </button>
            </form>
            @endif

            {{-- Download PDF bila sudah ada --}}
            <a href="{{ route('compilation.download', $publication->id) }}"
               class="inline-flex items-center px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg border border-slate-300 transition-colors">
                ⬇️ Unduh PDF
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     LAYOUT DUA KOLOM: SIDEBAR + PANEL
═══════════════════════════════════════════════════════════ --}}
<div class="flex flex-col lg:flex-row gap-4 items-start">

    {{-- Diagram alur lembar buku (Book Page Sequencing) --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alur Lembar Buku</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Book Page Sequencing &mdash; urutan halaman pada PDF final</p>
            </div>
            <span class="text-[9px] font-bold text-slate-400">{{ count($pages) }} halaman</span>
        </div>
        <div class="p-4">
            <div class="flex items-center overflow-x-auto pb-2 gap-0" role="list" aria-label="Urutan lembar buku">
                @foreach($readiness['items'] ?? $pages as $index => $page)
                    @php
                        $flowTone = match ($page['state'] ?? 'ok') {
                            'blocking' => ['bg-rose-50', 'border-rose-300', 'text-rose-800'],
                            'warn' => ['bg-amber-50', 'border-amber-300', 'text-amber-800'],
                            default => ['bg-emerald-50', 'border-emerald-300', 'text-emerald-800'],
                        };
                    @endphp
                    <div class="flex items-center shrink-0" role="listitem">
                        <button type="button" @click="setPage(@js($page['key']))"
                                class="w-28 border {{ $flowTone[1] }} {{ $flowTone[0] }} rounded-lg px-2 py-2 text-left hover:shadow-md transition-shadow"
                                title="{{ $page['label'] }} &mdash; {{ $page['note'] ?? '' }}">
                            <span class="block text-[8px] font-black uppercase text-slate-400 mb-0.5">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="block text-[9px] font-bold {{ $flowTone[2] }} leading-tight line-clamp-2">{{ $page['icon'] }} {{ $page['label'] }}</span>
                            @if($page['auto'])
                                <span class="block text-[7px] font-black text-slate-400 uppercase mt-0.5">Otomatis</span>
                            @endif
                        </button>
                        @if(! $loop->last)
                            <svg class="w-4 h-4 mx-1 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-2 pt-2 border-t border-slate-100 text-[9px] font-bold">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm border border-emerald-300 bg-emerald-50 inline-block"></span>Siap dicetak</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm border border-amber-300 bg-amber-50 inline-block"></span>Perlu dilengkapi</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm border border-rose-300 bg-rose-50 inline-block"></span>Menghalangi cetak</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm border border-slate-300 bg-slate-50 inline-block"></span>Dibuat Typst otomatis</span>
            </div>
        </div>
    </div>

    {{-- ── SIDEBAR KIRI ── --}}
    <aside class="w-full lg:w-72 flex-shrink-0 lg:sticky lg:top-20">
        {{-- Kartu kesiapan cetak: persentase checklist seluruh halaman buku. --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Kesiapan Cetak</p>
            </div>
            <div class="p-4">
                @php
                    $readinessPercent = (int) ($readiness['percent'] ?? 0);
                    $readinessTone = match (true) {
                        $readinessPercent >= 100 => ['bg-emerald-500', 'text-emerald-800', 'Siap dicetak'],
                        $readinessPercent >= 60 => ['bg-amber-500', 'text-amber-800', 'Perlu dilengkapi'],
                        default => ['bg-rose-500', 'text-rose-800', 'Belum siap'],
                    };
                @endphp
                <div class="flex items-end justify-between">
                    <div>
                        <span class="text-3xl font-black text-bps-navy">{{ $readinessPercent }}%</span>
                        <span class="text-[10px] text-slate-400 block">
                            {{ $readiness['manual_ok'] ?? 0 }} dari {{ $readiness['manual_total'] ?? 0 }} halaman tersusun
                        </span>
                    </div>
                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-slate-100 {{ $readinessTone[1] }}">{{ $readinessTone[2] }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-200 mt-2.5">
                    <div class="h-2.5 rounded-full {{ $readinessTone[0] }} transition-all" style="width: {{ $readinessPercent }}%"></div>
                </div>
                <p class="text-[10px] text-slate-400 mt-2 leading-relaxed">
                    Hanya halaman yang disunting manusia yang dihitung; daftar isi, daftar tabel,
                    dan penjelasan umum dibuat otomatis oleh Typst.
                </p>
                @if(! empty($readiness['blocking']))
                    <div class="mt-2.5 pt-2.5 border-t border-slate-100">
                        <p class="text-[9px] font-black uppercase text-rose-700 mb-1">Halang Cetak</p>
                        <ul class="text-[10px] text-rose-700 space-y-0.5 list-disc list-inside">
                            @foreach(array_slice($readiness['blocking'], 0, 4) as $blocker)
                                <li>{{ $blocker }}</li>
                            @endforeach
                            @if(count($readiness['blocking']) > 4)
                                <li class="list-none italic text-slate-400">+{{ count($readiness['blocking']) - 4 }} halaman lain</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Navigasi Halaman Buku</p>
                <span class="text-[9px] font-bold text-slate-400">Merah / Kuning / Hijau</span>
            </div>
            <nav class="divide-y divide-slate-50 max-h-[75vh] overflow-y-auto">
                @foreach($readiness['items'] ?? $pages as $page)
                @php
                    // Status berasal dari komputasi kesiapan cetak di backend.
                    $pageState = $page['state'] ?? 'ok';
                    $stateDot = match ($pageState) {
                        'blocking' => 'bg-rose-500',
                        'warn' => 'bg-amber-400',
                        default => 'bg-emerald-500',
                    };
                    $stateTitle = match ($pageState) {
                        'blocking' => 'Menghalangi cetak',
                        'warn' => 'Perlu dilengkapi',
                        default => 'Siap dicetak',
                    };
                @endphp
                <button
                    type="button"
                    @click="setPage(@js($page['key']))"
                    :class="activePage === @js($page['key'])
                        ? 'bg-bps-navy text-white shadow-inner'
                        : 'hover:bg-slate-50 text-slate-700'"
                    class="w-full text-left px-4 py-3 transition-colors flex items-start space-x-2.5 group"
                    title="{{ $page['label'] }} &mdash; {{ $page['note'] ?? $stateTitle }}"
                >
                    <span class="text-base flex-shrink-0 mt-0.5">{{ $page['icon'] }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold truncate block leading-tight">{{ $page['label'] }}</span>
                            <span class="flex items-center gap-1 flex-shrink-0 ml-1">
                                @if($page['auto'])
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-500">AUTO</span>
                                @endif
                                {{-- Indikator status merah/kuning/hijau untuk SETIAP halaman,
                                     termasuk halaman otomatis. --}}
                                <span class="w-2 h-2 rounded-full {{ $stateDot }} {{ $pageState === 'ok' ? 'opacity-40' : '' }} flex-shrink-0"
                                      title="{{ $stateTitle }}{{ $page['auto'] ? ' (halaman otomatis)' : '' }}"></span>
                            </span>
                        </div>
                        @if($page['label_en'])
                        <span class="text-[10px] italic opacity-60 truncate block leading-tight mt-0.5">{{ $page['label_en'] }}</span>
                        @endif
                    </div>
                </button>
                @endforeach
            </nav>
        </div>
    </aside>

    {{-- ── PANEL KANAN ── --}}
    <div class="flex-1 min-w-0 space-y-0">

{{-- ════════════════════════════════════
     PANEL: COVER DEPAN
═════════════════════════════════════ --}}
<div x-show="activePage === 'cover'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-black text-bps-navy">🖼️ Cover Depan</h2>
        <p class="text-xs text-slate-400 mt-0.5">Front Cover — metadata publikasi dan gambar latar.</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Form --}}
            <div>
                <form action="{{ route('pub.page.cover.update', $publication) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Publikasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $publication->title) }}" required
                            class="w-full text-sm rounded-lg border-slate-300 focus:border-bps-navy {{ $accentRing }} p-2.5 font-medium">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tahun <span class="text-rose-500">*</span></label>
                            <input type="text" name="year" value="{{ old('year', $publication->year) }}" required
                                class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-medium">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Volume</label>
                            <input type="text" name="volume" value="{{ old('volume', $publication->volume) }}"
                                class="w-full text-sm rounded-lg border-slate-300 p-2.5">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Katalog</label>
                            <input type="text" name="catalog_number" value="{{ old('catalog_number', $publication->catalog_number) }}"
                                class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Publikasi</label>
                            <input type="text" name="publication_number" value="{{ old('publication_number', $publication->publication_number) }}"
                                class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ISSN</label>
                        <input type="text" name="issn" value="{{ old('issn', $publication->issn) }}"
                            class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gambar Latar Cover (opsional)</label>
                        <input type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,.svg"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        @php $customCover = $publication->visualAssets->where('asset_type','COVER_CUSTOM')->where('mode','MANUAL_OVERRIDE')->last(); @endphp
                        @if($customCover)
                        <p class="text-[10px] text-emerald-600 mt-1">✅ Gambar kustom aktif: {{ basename($customCover->file_path) }}</p>
                        @endif
                    </div>

                    <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                        💾 Simpan Cover Depan
                    </button>
                </form>
            </div>

            {{-- Preview Mini Cover --}}
            <div class="flex flex-col items-center">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Preview (Skala)</p>
                <div class="w-full max-w-[200px] aspect-[148/210] bg-[#0A3866] text-white rounded-lg shadow-xl overflow-hidden flex flex-col justify-between p-4 border-2 border-slate-300 relative">
                    <div>
                        <div class="text-[6px] text-slate-300 font-semibold leading-tight">
                            BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER
                        </div>
                        <div class="mt-5">
                            <h3 class="text-[9px] font-black tracking-tight leading-tight uppercase text-white">{{ $publication->title }}</h3>
                            <div class="text-bps-orange font-extrabold text-[8px] mt-1">TAHUN {{ $publication->year }}</div>
                            @if($publication->volume)
                            <div class="text-[6px] text-slate-300">VOLUME {{ $publication->volume }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="border-t border-white/20 pt-2 flex items-end justify-between">
                        <div>
                            <div class="text-[5.5px] font-bold text-white">BPS KABUPATEN JEMBER</div>
                            <div class="text-[4.5px] text-slate-300">Jl. Kalimantan No. 42</div>
                        </div>
                        @if($publication->issn)
                        <div class="bg-white text-slate-900 px-1 py-0.5 rounded text-[5px] font-bold font-mono">
                            ISSN: {{ $publication->issn }}
                        </div>
                        @endif
                    </div>
                </div>
                <p class="text-[9px] text-slate-400 mt-2 text-center">Tampilan akhir dikompilasi oleh Typst</p>
            </div>
        </div>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: HALAMAN JUDUL (IMPRIMATUR)
═════════════════════════════════════ --}}
<div x-show="activePage === 'imprimatur'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-black text-bps-navy">📋 Halaman Judul (Kolofon)</h2>
        <p class="text-xs text-slate-400 mt-0.5">Title Page / Imprimatur — metadata bilingual halaman kedua buku.</p>
    </div>
    <div class="p-6">
        <form action="{{ route('pub.page.imprimatur.update', $publication) }}" method="POST" class="space-y-4 max-w-xl">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Judul Publikasi <span class="text-rose-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $publication->title) }}" required
                    class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-medium">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Katalog</label>
                    <input type="text" name="catalog_number" value="{{ old('catalog_number', $publication->catalog_number) }}"
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Publikasi</label>
                    <input type="text" name="publication_number" value="{{ old('publication_number', $publication->publication_number) }}"
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ISSN</label>
                    <input type="text" name="issn" value="{{ old('issn', $publication->issn) }}"
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Volume</label>
                    <input type="text" name="volume" value="{{ old('volume', $publication->volume) }}"
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Ukuran Buku</label>
                <input type="text" name="book_size" value="{{ old('book_size', $publication->book_size) }}"
                    placeholder="14,8 cm x 21 cm"
                    class="w-full text-sm rounded-lg border-slate-300 p-2.5">
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700 space-y-2">
                <p class="flex items-start gap-1.5">
                    <span aria-hidden="true">ℹ️</span>
                    <span>Jumlah halaman (hal/pages) dihitung otomatis oleh Typst saat kompilasi.</span>
                </p>
                {{-- Panduan tata letak kolofon BPS. --}}
                <div class="pt-2 border-t border-blue-200">
                    <p class="font-black text-[10px] uppercase tracking-wider text-blue-900 mb-1.5">Panduan Tata Letak Kolofon</p>
                    <ol class="space-y-1 list-decimal list-inside text-[11px] leading-relaxed">
                        <li><strong>Atas halaman:</strong> nama lembaga penerbit dan lingkup wilayah, dicetak kapital tanpa titik.</li>
                        <li><strong>Baris kiri bawah:</strong> judul publikasi, tahun terbit, dan nomor katalog berurutan ke bawah.</li>
                        <li><strong>Blok kanan bawah:</strong> nomor publikasi publikasi, ISSN, dan nomor edging (volume).</li>
                        <li><strong>Baris paling bawah:</strong> pencetak, tahun cetak, dan nomor ISBN bila tercetak.</li>
                        <li>Seluruh angka katalog, nomor publikasi, dan ISSN <strong>wajib diisi</strong>; kolom kosong tidak boleh dicetak sebagai nol.</li>
                    </ol>
                </div>
            </div>
            <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                💾 Simpan Halaman Judul
            </button>
        </form>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: TIM PENYUSUN
═════════════════════════════════════ --}}
<div x-show="activePage === 'team'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-black text-bps-navy">👥 Tim Penyusun</h2>
        <p class="text-xs text-slate-400 mt-0.5">Team Members — daftar peran dan nama anggota tim penyusun publikasi.</p>
    </div>
    <div class="p-6">
        <form action="{{ route('pub.page.team.update', $publication) }}" method="POST">
            @csrf
            @method('PUT')

            <div
                x-data="{
                    entries: {{ json_encode(
                        $publication->teamMembers->map(fn($m) => [
                            'role_id' => $m->role_id,
                            'role_en' => $m->role_en,
                            'names'   => implode("\n", $m->names),
                        ])->values()->toArray()
                    ) }},
                    addEntry() { this.entries.push({ role_id: '', role_en: '', names: '' }); },
                    removeEntry(i) { this.entries.splice(i, 1); }
                }"
                class="space-y-4"
            >
                <template x-for="(entry, index) in entries" :key="index">
                    <div class="border border-slate-200 rounded-lg p-4 bg-slate-50 space-y-3 relative">
                        <button type="button" @click="removeEntry(index)"
                            class="absolute top-3 right-3 text-xs text-rose-500 hover:text-rose-700 font-bold">✕ Hapus</button>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 uppercase tracking-wide">Peran (Indonesia)</label>
                                <input type="text" :name="'entries[' + index + '][role_id]'" x-model="entry.role_id"
                                    placeholder="Penanggung Jawab"
                                    class="w-full text-sm rounded-lg border-slate-300 p-2 font-medium">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 mb-1 uppercase tracking-wide">Role (English)</label>
                                <input type="text" :name="'entries[' + index + '][role_en]'" x-model="entry.role_en"
                                    placeholder="Person in Charge"
                                    class="w-full text-sm rounded-lg border-slate-300 p-2 font-medium italic">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-1 uppercase tracking-wide">
                                Nama-nama <span class="text-slate-400 font-normal normal-case">(satu nama per baris)</span>
                            </label>
                            <textarea :name="'entries[' + index + '][names]'" x-model="entry.names" rows="3"
                                placeholder="Budi Santoso&#10;Ani Widayati"
                                class="w-full text-sm rounded-lg border-slate-300 p-2 resize-none font-medium"></textarea>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addEntry()"
                    class="w-full py-2 border-2 border-dashed border-slate-300 hover:border-slate-400 text-slate-500 hover:text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    + Tambah Peran
                </button>

                <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                    💾 Simpan Tim Penyusun
                </button>
            </div>
        </form>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: KATA PENGANTAR
═════════════════════════════════════ --}}
<div x-show="activePage === 'preface'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-black text-bps-navy">✍️ Kata Pengantar / Preface</h2>
        <p class="text-xs text-slate-400 mt-0.5">Teks bilingual halaman Kata Pengantar dan Preface (English).</p>
    </div>
    {{-- Panduan penulisan kata pengantar BPS. --}}
    <div class="px-6 py-3 bg-slate-50 border-b border-slate-100">
        <p class="text-[10px] font-black uppercase tracking-wider text-bps-navy mb-2">Panduan Penulisan Kata Pengantar</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 text-[11px] text-slate-600">
            <p class="flex gap-1.5"><span class="text-emerald-600 font-black">✓</span> Sebut tujuan dan ruang lingkup publikasi.</p>
            <p class="flex gap-1.5"><span class="text-emerald-600 font-black">✓</span> Sampaikan sumber data dan metode pengumpulan.</p>
            <p class="flex gap-1.5"><span class="text-emerald-600 font-black">✓</span> Jelaskan tahun periode dan cakupan wilayah.</p>
            <p class="flex gap-1.5"><span class="text-emerald-600 font-black">✓</span> Nyatakan harapan manfaat bagi pengguna.</p>
            <p class="flex gap-1.5"><span class="text-rose-500 font-black">✗</span> Jangan memasukkan angka yang belum ada di tabel.</p>
            <p class="flex gap-1.5"><span class="text-rose-500 font-black">✗</span> Jangan menyalin uraian bab secara utuh.</p>
        </div>
        <p class="text-[10px] text-slate-400 mt-2">
            Rekomendasi panjang: 150&ndash;300 kata. Kedalaman kata pengantar bahasa Inggris
            sebaiknya setara dengan versi Indonesia untuk menjaga kesetaraan bilingual.
        </p>
    </div>
    <div class="p-6">
        <form action="{{ route('pub.page.preface.update', $publication) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Pengantar <span class="text-slate-400 font-normal">(Bahasa Indonesia)</span></label>
                    <textarea name="preface_id" rows="12"
                        placeholder="Publikasi Kecamatan ... Dalam Angka ... merupakan publikasi berkala tahunan..."
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 resize-y leading-relaxed">{{ old('preface_id', $publication->preface_id) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 italic">Preface <span class="text-slate-400 font-normal not-italic">(English)</span></label>
                    <textarea name="preface_en" rows="12"
                        placeholder="... District in Figures ... is an annual publication..."
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 resize-y leading-relaxed italic text-slate-600">{{ old('preface_en', $publication->preface_en) }}</textarea>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Tanda Tangan</label>
                <input type="text" name="sign_date"
                    value="{{ old('sign_date', $publication->sign_date) }}"
                    placeholder="Jember, September {{ $publication->year }}"
                    class="w-full sm:w-72 text-sm rounded-lg border-slate-300 p-2.5">
            </div>

            <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                💾 Simpan Kata Pengantar
            </button>
        </form>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: DAFTAR ISI (AUTO)
═════════════════════════════════════ --}}
<div x-show="activePage === 'toc'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
    <div class="flex items-start space-x-4 text-blue-700 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <span class="text-2xl">📑</span>
        <div>
            <h3 class="font-bold text-sm">Daftar Isi — Auto-Generate</h3>
            <p class="text-xs mt-1 leading-relaxed">Halaman ini digenerate otomatis oleh Typst berdasarkan struktur bab. Tidak perlu diedit secara manual. Daftar isi akan mencerminkan semua bab yang telah disusun.</p>
        </div>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: DAFTAR TABEL (AUTO)
═════════════════════════════════════ --}}
<div x-show="activePage === 'tables_index'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
    <div class="flex items-start space-x-4 text-blue-700 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <span class="text-2xl">📊</span>
        <div>
            <h3 class="font-bold text-sm">Daftar Tabel — Auto-Generate</h3>
            <p class="text-xs mt-1 leading-relaxed">Daftar tabel digenerate otomatis oleh Typst dari semua tabel data yang diingesti. Hanya muncul bila publikasi memiliki data tabel ({{ $publication->tables->count() }} tabel terdeteksi).</p>
        </div>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: PENJELASAN UMUM (AUTO)
═════════════════════════════════════ --}}
<div x-show="activePage === 'explanatory'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
    <div class="flex items-start space-x-4 text-blue-700 bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <span class="text-2xl">📝</span>
        <div>
            <h3 class="font-bold text-sm">Penjelasan Umum — Template Standar BPS</h3>
            <p class="text-xs mt-1">Konten bilingual ini adalah teks standar BPS RI, tidak dapat diubah per publikasi.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-xs text-slate-700 leading-relaxed">
            <p class="font-bold text-slate-500 text-[10px] uppercase tracking-wide mb-2">Indonesia</p>
            Publikasi ini menyajikan data statistik yang diperoleh dari instansi pemerintah, lembaga, dan sumber lain yang dicantumkan pada setiap tabel. Angka mengikuti satuan dan tahun rujukan yang tertulis pada judul tabel. Pemisah desimal menggunakan koma (,). Tanda (–) berarti data tidak tersedia atau tidak berlaku, sedangkan 0 (nol) berarti data tersedia dengan nilai nol.
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-xs text-slate-500 italic leading-relaxed">
            <p class="font-bold text-slate-400 text-[10px] uppercase tracking-wide mb-2 not-italic">English</p>
            This publication presents statistical data obtained from government agencies, institutions, and other sources listed in each table. Figures follow the units and reference years stated in the table titles. A comma (,) is used as the decimal separator. A dash (–) indicates data not available or not applicable, while 0 (zero) indicates available data with a zero value.
        </div>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: DAFTAR SINGKATAN
═════════════════════════════════════ --}}
<div x-show="activePage === 'abbreviations'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100">
        <h2 class="text-base font-black text-bps-navy">🔤 Daftar Singkatan</h2>
        <p class="text-xs text-slate-400 mt-0.5">List of Abbreviations — kustomisasi daftar singkatan per publikasi.</p>
    </div>
    <div class="p-6">
        <form action="{{ route('pub.page.abbr.update', $publication) }}" method="POST">
            @csrf
            @method('PUT')

            <div
                x-data="{
                    rows: {{ json_encode(array_values($abbreviations)) }},
                    addRow() { this.rows.push(['', '', '']); },
                    removeRow(i) { this.rows.splice(i, 1); }
                }"
                class="space-y-3"
            >
                <div class="grid grid-cols-3 gap-2 text-[10px] font-black uppercase tracking-wide text-slate-400 px-1">
                    <span>Singkatan</span>
                    <span>Kepanjangan (ID)</span>
                    <span>Kepanjangan (EN)</span>
                </div>

                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <input type="text" :name="'abbreviations[' + index + '][0]'" x-model="row[0]"
                            placeholder="BPS"
                            class="text-sm rounded-lg border-slate-300 p-2 font-mono font-bold">
                        <input type="text" :name="'abbreviations[' + index + '][1]'" x-model="row[1]"
                            placeholder="Badan Pusat Statistik"
                            class="text-sm rounded-lg border-slate-300 p-2">
                        <div class="flex items-center space-x-1">
                            <input type="text" :name="'abbreviations[' + index + '][2]'" x-model="row[2]"
                                placeholder="Statistics Indonesia"
                                class="flex-1 text-sm rounded-lg border-slate-300 p-2 italic">
                            <button type="button" @click="removeRow(index)"
                                class="text-rose-400 hover:text-rose-600 font-bold text-xs px-1">✕</button>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addRow()"
                    class="w-full py-2 border-2 border-dashed border-slate-300 hover:border-slate-400 text-slate-500 hover:text-slate-700 text-xs font-bold rounded-lg transition-colors">
                    + Tambah Singkatan
                </button>

                <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                    💾 Simpan Daftar Singkatan
                </button>
            </div>
        </form>
    </div>
</div>
</div>

{{-- ════════════════════════════════════
     PANEL: BAB (CHAPTER) PER NARRATIVE
═════════════════════════════════════ --}}
@foreach($publication->narratives as $narrative)
<div x-show="activePage === 'chapter_{{ $narrative->chapter_number }}'" x-cloak>
<div class="bg-white border border-slate-200 rounded-xl shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm {{ $accentBg }} text-white shadow-sm">
                {{ $narrative->chapter_number }}
            </span>
            <div>
                <h2 class="text-base font-black text-bps-navy">{{ $narrative->title_id }}</h2>
                <p class="text-xs text-slate-400 italic">{{ $narrative->title_en }}</p>
            </div>
        </div>
        @if($narrative->updated_at)
        <span class="text-[10px] text-slate-400">Terakhir disimpan: {{ $narrative->updated_at->diffForHumans() }}</span>
        @endif
    </div>
    <div class="p-6">

        @if($publication->isLocked())
        <div class="mb-4 bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg text-xs text-amber-800 font-medium">
            🔒 Publikasi berstatus <strong>{{ $publication->status }}</strong> dan terkunci. Minta Approver membuka kunci untuk mengedit narasi.
        </div>
        @endif

        <form action="{{ route('pub.page.chapter.update', [$publication, $narrative]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Ulasan <span class="text-slate-400 font-normal">(Bahasa Indonesia)</span>
                        <span class="text-rose-500 ml-0.5">*</span>
                    </label>
                    <textarea name="narrative_id" rows="8" {{ $publication->isLocked() ? 'disabled' : '' }}
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 resize-y leading-relaxed {{ $publication->isLocked() ? 'bg-slate-100 text-slate-400' : '' }}"
                        >{{ old('narrative_id_'.$narrative->id, $narrative->narrative_id) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1 italic">
                        Description <span class="text-slate-400 font-normal not-italic">(English)</span>
                        <span class="text-rose-500 ml-0.5">*</span>
                    </label>
                    <textarea name="narrative_en" rows="8" {{ $publication->isLocked() ? 'disabled' : '' }}
                        class="w-full text-sm rounded-lg border-slate-300 p-2.5 resize-y leading-relaxed italic text-slate-600 {{ $publication->isLocked() ? 'bg-slate-100' : '' }}"
                        >{{ old('narrative_en_'.$narrative->id, $narrative->narrative_en) }}</textarea>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3">
                <p class="text-[10px] font-black uppercase tracking-wider text-amber-700">Indikator Kunci / Key Figure on Divider</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Label Indikator</label>
                        <input type="text" name="highlight_label" {{ $publication->isLocked() ? 'disabled' : '' }}
                            value="{{ old('highlight_label_'.$narrative->id, $narrative->highlight_label) }}"
                            placeholder="Luas Wilayah"
                            class="w-full text-sm rounded-lg border-amber-300 p-2 {{ $publication->isLocked() ? 'bg-slate-100' : 'bg-white' }}">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Indikator</label>
                        <input type="text" name="highlight_value" {{ $publication->isLocked() ? 'disabled' : '' }}
                            value="{{ old('highlight_value_'.$narrative->id, $narrative->highlight_value) }}"
                            placeholder="123,45 km²"
                            class="w-full text-sm rounded-lg border-amber-300 p-2 font-bold {{ $publication->isLocked() ? 'bg-slate-100' : 'bg-white' }}">
                    </div>
                </div>
                <p class="text-[10px] text-amber-600 italic">Nilai ini tampil sebagai angka besar pada lembar pembatas bab (Chapter Divider) di PDF.</p>
            </div>

            @unless($publication->isLocked())
            <button type="submit" class="w-full py-2.5 {{ $accentBg }} {{ $accentHover }} text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                💾 Simpan Bab {{ $narrative->chapter_number }}
            </button>
            @endunless
        </form>
    </div>
</div>
</div>
@endforeach

    </div>{{-- end panel kanan --}}
</div>{{-- end grid --}}
</div>{{-- end x-data --}}
@endsection
