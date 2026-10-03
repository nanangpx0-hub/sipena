@extends('layouts.app', ['title' => 'Cover & Pembatas Bab - SI-PENA'])

@section('content')
<div class="space-y-6">

    {{-- ==================================================================
         ATURAN INTEGRITAS ANGKA (AGENTS.md §1.4): halaman ini TIDAK PERNAH
         menulis nomor katalog, nomor publikasi, volume, ISSN, judul bab, atau
         angka indikator karangan. Semua metadata dicetak apa adanya dari
         database; bila kosong, sistem menampilkan "Belum ada data" dan
        tanpa mengarang nilai pengganti.
    ================================================================== --}}

    <!-- ================= HEADER ================= -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Cover, Mockup Buku &amp; Pembatas Bab</h1>
            <p class="text-xs text-slate-500 mt-1 max-w-3xl">
                Studio cover BPS Kabupaten Jember: mockup buku 3 dimensi, panduan resolusi cetak
                (300&nbsp;DPI &middot; bleed 3&nbsp;mm &middot; aman potong), penggaris <em>safe print area</em>,
                palet warna korporat, dan galeri template cover alternatif.
            </p>
        </div>
        <form method="GET" action="{{ route('covers.index') }}" class="flex items-center space-x-2">
            <label for="publication_id" class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Publikasi</label>
            <select name="publication_id" id="publication_id" onchange="this.form.submit()"
                    class="text-xs rounded-lg border-slate-300 focus:border-bps-navy p-2 bg-slate-50 font-semibold text-slate-700">
                @forelse($publications as $pub)
                    <option value="{{ $pub->id }}" @selected($selectedPub?->id == $pub->id)>
                        {{ $pub->title }}
                    </option>
                @empty
                    <option value="">— Belum ada publikasi —</option>
                @endforelse
            </select>
        </form>
    </div>

    @if(! $selectedPub)
        <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-lg p-6 text-sm text-amber-900">
            <strong class="block mb-1">Belum ada publikasi yang dapat dipilih.</strong>
            Studio cover hanya dapat menampilkan pratinjau bila minimal satu publikasi sudah terdaftar pada tahun terbit aktif.
            Jalankan <code class="font-mono">php artisan sipena:qa-reset</code> atau seed data contoh terlebih dahulu.
        </div>
    @else

    @php
        // Meta cover dibaca apa adanya dari database publikasi (tanpa nilai tebakan).
        $metaCatalog     = $selectedPub->catalog_number;
        $metaPublication = $selectedPub->publication_number;
        $metaVolume      = $selectedPub->volume;
        $metaIssn        = $selectedPub->issn;
        $metaBookSize    = $selectedPub->book_size ?: '—';

        // Bab pertama hanya dipakai bila benar-benar ada; bila belum ada naskah,
        // seluruh kartu pembatas menampilkan kondisi "belum ada data".
        $firstNar = $selectedPub->narratives->sortBy('chapter_number')->first();
        $hasDividerData = $firstNar && $firstNar->highlight_value;

        /* Estimasi struktur halaman untuk panduan tebal punggung.
           Angka ini BUKAN angka statistik resmi, melainkan turunan jumlah
           komponen yang benar-benar ada di database:
             2  halaman sampul (depan + belakang)
            + 4  halaman prakata (judul, tim, kata pengantar, daftar isi)
            + 1  halaman daftar tabel
            + 4 halaman per bab naskah (1 pembatas + 3 ulasan)
            + 1  halaman per tabel data. */
        $narrativeCount = $selectedPub->narratives->count();
        $tableCount     = $selectedPub->tables->count();
        $pageEstimate   = 2 + 4 + 1 + ($narrativeCount * 4) + $tableCount;

        // Fungsi pemetaan lebar punggung mengikuti tabel acuan di atas.
        $spineGuide = match (true) {
            $pageEstimate > 600 => ['> 36 mm', 'Buruang',       'bg-rose-50 text-rose-800'],
            $pageEstimate > 400 => ['~ 28 mm', 'Buku referensi', 'bg-amber-50 text-amber-800'],
            $pageEstimate > 256 => ['~ 20 mm', 'Buku standar',  'bg-sky-50 text-sky-800'],
            $pageEstimate > 128 => ['~ 12 mm', 'Buku saku',     'bg-emerald-50 text-emerald-800'],
            default             => ['< 10 mm', 'Saku / ringkas', 'bg-emerald-50 text-emerald-800'],
        };
    @endphp

    <!-- ================= 3D BOOK MOCKUP (tampak depan + punggung) ================= -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Mockup utama: tampak 3/4 dengan punggung buku terlihat -->
        <div class="xl:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Mockup Buku 3D &mdash; Cover Depan &amp; Cover Belakang</h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Simulasi rasio halaman <span class="font-mono font-bold">{{ $metaBookSize }}</span>,
                        Blok halaman disimulasikan sebagai tumpukan kertas cetak.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Mode Otomatis Typst</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600">Acen Oranye #E67E22</span>
                </div>
            </div>

            {{-- Panggung 3D: containernya memberi perspective, lalu buku diputar
                 sehingga sisi punggung (spine) ikut terlihat seperti buku sungguhan. --}}
            <div class="relative w-full rounded-xl bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 border border-slate-200 overflow-hidden py-10 px-4"
                 style="perspective: 1600px;">

                {{-- Bayangan jatuh di bawah buku --}}
                <div class="absolute left-1/2 -translate-x-1/2 bottom-5 w-[62%] h-6 rounded-[100%] bg-slate-400/40 blur-md"></div>

                <div class="relative mx-auto flex justify-center items-center"
                     style="transform: rotateY(-26deg) rotateX(6deg); transform-style: preserve-3d;">

                    {{-- Sisi punggung buku (spine) --}}
                    <div class="relative shrink-0 rounded-l-[3px]"
                         style="width: 42px; transform: translateZ(0);">
                        <div class="absolute inset-0 bg-[#062442] rounded-l-[3px] shadow-inner"></div>
                        <div class="relative h-full flex flex-col items-center justify-between py-5 bg-gradient-to-r from-[#062442] via-[#0A3866] to-[#124a80] rounded-l-[3px]">
                            <span class="text-[7px] font-black text-white/90 tracking-[0.2em] [writing-mode:vertical-rl] rotate-180">
                                BPS KABUPATEN JEMBER
                            </span>
                            <span class="text-[7px] font-black text-bps-orange tracking-[0.2em] [writing-mode:vertical-rl] rotate-180">
                                {{ \Illuminate\Support\Str::limit($selectedPub->title, 40) }}
                            </span>
                            <span class="text-[7px] font-bold text-white/70 [writing-mode:vertical-rl] rotate-180">
                                {{ $selectedPub->year }}
                            </span>
                        </div>
                        {{-- Garis emas pemisah panels punggung --}}
                        <div class="absolute inset-y-0 right-0 w-px bg-white/25"></div>
                    </div>

                    {{-- Blok halaman (paper edge) --}}
                    <div class="relative shrink-0 bg-slate-200" style="width: 16px;">
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-100 to-slate-300"></div>
                        <div class="absolute inset-y-0 left-0 w-px bg-slate-400/50"></div>
                    </div>

                    {{-- Sampul depan (front cover) --}}
                    <div class="relative w-[268px] sm:w-[300px] aspect-[1/1.414] bg-[#0A3866] text-white rounded-r-md overflow-hidden flex flex-col justify-between p-5 shadow-2xl border border-[#062442]">
                        {{-- Aksen oranye strip sisi kanan --}}
                        <div class="absolute top-0 bottom-0 right-0 w-1.5 bg-bps-orange"></div>
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-bps-orange via-bps-orange/60 to-transparent"></div>

                        <div>
                            <div class="flex justify-between items-start text-[7px] text-slate-300 font-semibold leading-tight">
                                <div>BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</div>
                                <div class="text-right">
                                    No. Katalog:<br>
                                    <span class="{{ $metaCatalog ? 'text-white' : 'text-amber-300/90 italic' }} font-bold">
                                        {{ $metaCatalog ?: 'Belum ada data' }}
                                    </span><br>
                                    No. Publikasi:<br>
                                    <span class="{{ $metaPublication ? 'text-white' : 'text-amber-300/90 italic' }} font-bold">
                                        {{ $metaPublication ?: 'Belum ada data' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-10">
                                <h3 class="text-base font-black tracking-tight leading-tight uppercase text-white">
                                    {{ $selectedPub->title }}
                                </h3>
                                <div class="text-bps-orange font-extrabold text-sm mt-1.5">
                                    TAHUN {{ $selectedPub->year }}
                                </div>
                                <div class="text-[9px] text-slate-300 mt-0.5">
                                    @if($metaVolume)
                                        VOLUME {{ $metaVolume }}
                                    @else
                                        <span class="italic text-amber-300/90">Volume belum ditetapkan</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-white/20 pt-3 flex items-end justify-between gap-2">
                            <div>
                                <div class="text-[8px] font-bold text-white leading-tight">BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</div>
                                <div class="text-[6px] text-slate-300 mt-0.5">Jl. Kalimantan No. 42 Jember 68121 - Jawa Timur</div>
                            </div>
                            <div class="shrink-0 bg-white text-slate-900 px-1.5 py-0.5 rounded text-[7px] font-bold font-mono">
                                @if($metaIssn)
                                    ISSN: {{ $metaIssn }}
                                @else
                                    ISSN<br><span class="italic font-sans font-normal">belum ada data</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mockup terpisah: tampak punggung (back cover) -->
            <div class="mt-5 flex flex-col sm:flex-row items-center gap-4 pt-5 border-t border-slate-100">
                <div class="shrink-0 w-[150px] aspect-[1/1.414] bg-white text-slate-800 rounded-md overflow-hidden flex flex-col justify-between p-4 border-2 border-slate-200 shadow-md">
                    <div class="text-[7px] font-bold text-slate-400 uppercase tracking-wider">Punggung Belakang</div>
                    <div>
                        <div class="h-px bg-bps-navy/30 mb-2"></div>
                        <p class="text-[6px] leading-relaxed text-slate-500">
                            Cover belakang memuat identitas penerbit, kode wilayah 3509, dan ruang kosong
                            Minimum 20&nbsp;mm untuk penempelan barcode ISSN ISBN pada cover belakang.
                        </p>
                    </div>
                    <div class="text-[6px] text-slate-400 font-bold">BPS KABUPATEN JEMBER &middot; 3509</div>
                </div>
                <ul class="text-[11px] text-slate-600 space-y-1.5 flex-1">
                    <li class="flex gap-2"><span class="text-bps-orange font-black">&bull;</span> Ukuran sampul mengikuti <code class="font-mono">book_size</code> publikasi; rasio A4 (210&times;297&nbsp;mm) untuk DDA, A5 (148&times;210&nbsp;mm) untuk KDA.</li>
                    <li class="flex gap-2"><span class="text-bps-orange font-black">&bull;</span> Sisi punggungLebar minimum 15&nbsp;mm agar stabil dijilid dan memuat judul vertikal.</li>
                    <li class="flex gap-2"><span class="text-bps-orange font-black">&bull;</span> Aksen oranye <code class="font-mono">#E67E22</code> hanya pada pita atas dan tepi kanan &mdash; penanda visual baku standar BPS.</li>
                    <li class="flex gap-2"><span class="text-bps-orange font-black">&bull;</span> Seluruh elemen dirender <strong>vektor</strong> oleh Typst CLI sehingga tetap tajam pada resolusi cetak apa pun.</li>
                </ul>
            </div>
        </div>

        <!-- ================= PANDUAN RESOLUSI CETAK ================= -->
        <div class="space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-bps-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h2m2 0h12a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 18v3a2 2 0 002 2h8a2 2 0 002-2v-3m-6 0h6"/></svg>
                    <h2 class="text-sm font-bold text-bps-navy">Spesifikasi Cetak Standar BPS</h2>
                </div>
                <dl class="space-y-2 text-[11px]">
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-semibold">Resolusi gambar</dt>
                        <dd class="font-bold text-bps-navy">300 DPI (min. 240 DPI)</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-semibold">Bleed (ambles)</dt>
                        <dd class="font-bold text-bps-navy">3 mm di keempat sisi</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-semibold">Margin aman (safe area)</dt>
                        <dd class="font-bold text-bps-navy">10 mm dari garis potong</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-semibold">Ruang warna</dt>
                        <dd class="font-bold text-bps-navy">CMYK (Adobe RGB &rarr; CMYK)</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                        <dt class="text-slate-500 font-semibold">Format berkas</dt>
                        <dd class="font-bold text-bps-navy">PDF/X-1a &middot; PDF/A-1b</dd>
                    </div>
                    <div class="flex items-center justify-between gap-2 py-1.5">
                        <dt class="text-slate-500 font-semibold">Font</dt>
                        <dd class="font-bold text-bps-navy">Roboto (embedded)</dd>
                    </div>
                </dl>
            </div>

            <!-- Panduan ketebalan punggung buku -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-bps-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <h2 class="text-sm font-bold text-bps-navy">Panduan Ketebalan Punggung</h2>
                </div>
                <p class="text-[10px] text-slate-400 mb-3 leading-relaxed">
                    Acuan industri percetakan: buku kertas 80&nbsp;gsm setebal &plusmn;0,1&nbsp;mm per lembar
                    (termasuk Solidarity), bergantung pada jumlah dan ketebalan kertas.
                </p>
                <table class="w-full text-[10px] border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600">
                            <th class="text-left px-2 py-1.5 font-bold">Jumlah Halaman</th>
                            <th class="text-left px-2 py-1.5 font-bold">Tebal Punggung</th>
                            <th class="text-left px-2 py-1.5 font-bold">Sifat Buku</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        @foreach([
                            [64,  '< 10 mm', 'Saku / ringkas', 'bg-emerald-50 text-emerald-800'],
                            [128, '~ 12 mm', 'Buku saku',    'bg-emerald-50 text-emerald-800'],
                            [256, '~ 20 mm', 'Buku standar',  'bg-sky-50 text-sky-800'],
                            [400, '~ 28 mm', 'Buku referensi','bg-amber-50 text-amber-800'],
                            [600, '> 36 mm', 'Buruang',       'bg-rose-50 text-rose-800'],
                        ] as $row)
                            <tr class="{{ $row[3] }} border-b border-white/60">
                                <td class="px-2 py-1.5 font-bold">s.d. {{ $row[0] }} hal</td>
                                <td class="px-2 py-1.5 font-mono font-bold">{{ $row[1] }}</td>
                                <td class="px-2 py-1.5">{{ $row[2] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-500 font-semibold">Estimasi struktur halaman</span>
                        @if($pageEstimate > 0)
                            <span class="font-black text-bps-navy">
                                &asymp; {{ $pageEstimate }} hal &rarr; {{ $spineGuide[0] }}
                            </span>
                        @else
                            <span class="italic text-amber-700 font-bold">Belum ada data</span>
                        @endif
                    </div>
                    <p class="text-[9px] text-slate-400 mt-1 leading-relaxed">
                        Estimasi dari komponen yang benar-benar ada di database:
                        {{ $narrativeCount }} naskah bab &times; 4 hal + {{ $tableCount }} tabel + 7 hal prakata/sampul.
                        Angka halaman final dihitung Typst saat kompilasi PDF, bukan dipratinjau.
                    </p>
                    @if($pageEstimate > 0)
                    <div class="mt-2 flex items-center gap-2 text-[10px {{ $spineGuide[2] }} border rounded px-2 py-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-bold">Kategori: {{ $spineGuide[1] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ================= PENGGARIS SAFE PRINT AREA ================= -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Penggaris &amp; Grid Batas Aman Potong</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Skala ilustrasi berdasar halaman A5 (148&times;210&nbsp;mm). Warna <span class="font-mono text-rose-600">merah</span> = garis potong,
                    <span class="font-mono text-amber-600">kuning</span> = bleed 3&nbsp;mm,
                    <span class="font-mono text-emerald-600">hijau</span> = area aman cetak.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 text-[10px] font-bold">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-rose-500 inline-block"></span> Trim Line</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-amber-400 inline-block"></span> Bleed 3 mm</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-emerald-500 inline-block"></span> Safe Area 10 mm</span>
            </div>
        </div>

        {{-- viewBox dalam satuan mm agar penggaris purely geometris dan bukan
             data statistik. --}}
        <div class="overflow-x-auto">
            <div class="min-w-[520px] mx-auto">
                <svg viewBox="-16 -16 180 242" class="w-full max-w-md mx-auto" role="img"
                     aria-label="Penggaris milimeter dengan garis potong, bleed, dan area aman cetak">
                    <defs>
                        <pattern id="rulerTicks" width="10" height="4" patternUnits="userSpaceOnUse">
                            <line x1="0" y1="0" x2="0" y2="4" stroke="#94A3B8" stroke-width="0.4"/>
                        </pattern>
                        <pattern id="rulerTicksMinor" width="2" height="2.5" patternUnits="userSpaceOnUse">
                            <line x1="0" y1="0" x2="0" y2="2.5" stroke="#CBD5E1" stroke-width="0.25"/>
                        </pattern>
                        <pattern id="gridCells" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M10 0H0V10" fill="none" stroke="#E2E8F0" stroke-width="0.3"/>
                        </pattern>
                    </defs>

                    {{-- Area bleed (amber) --}}
                    <rect x="-3" y="-3" width="154" height="216" fill="#FEF3C7" stroke="#F59E0B" stroke-width="0.6" stroke-dasharray="2 1"/>
                    {{-- Area trim (putih) --}}
                    <rect x="0" y="0" width="148" height="210" fill="#FFFFFF" stroke="#DC2626" stroke-width="0.8"/>
                    {{-- Grid helper 10 mm --}}
                    <rect x="0" y="0" width="148" height="210" fill="url(#gridCells)"/>
                    {{-- Safe area (hijau) --}}
                    <rect x="10" y="10" width="128" height="190" fill="none" stroke="#059669" stroke-width="0.7" stroke-dasharray="3 1.5"/>

                    {{-- Crop mark register --}}
                    <g stroke="#1E293B" stroke-width="0.5">
                        <line x1="-6" y1="0" x2="-1" y2="0"/><line x1="149" y1="0" x2="154" y2="0"/>
                        <line x1="-6" y1="210" x2="-1" y2="210"/><line x1="149" y1="210" x2="154" y2="210"/>
                        <line x1="0" y1="-6" x2="0" y2="-1"/><line x1="0" y1="211" x2="0" y2="216"/>
                        <line x1="148" y1="-6" x2="148" y2="-1"/><line x1="148" y1="211" x2="148" y2="216"/>
                    </g>

                    {{-- Penggaris atas & bawah --}}
                    <rect x="0" y="-14" width="148" height="4" fill="url(#rulerTicks)"/>
                    <rect x="0" y="210" width="148" height="4" fill="url(#rulerTicks)"/>
                    {{-- Penggaras kiri & kanan --}}
                    <rect x="-14" y="0" width="4" height="210" fill="url(#rulerTicks)"/>
                    <rect x="148" y="0" width="4" height="210" fill="url(#rulerTicks)"/>

                    {{-- Label dimensi --}}
                    <text x="74" y="-16" text-anchor="middle" font-size="5" font-weight="700" fill="#475569">148 mm</text>
                    <text x="-17" y="105" text-anchor="middle" font-size="5" font-weight="700" fill="#475569" transform="rotate(-90 -17 105)">210 mm</text>
                    <text x="74" y="222" text-anchor="middle" font-size="4" font-weight="700" fill="#94A3B8">0 &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; 50 &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; 100 &#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; 148 mm</text>

                    {{-- Zona elemen wajib di dalam safe area --}}
                    <rect x="14" y="14" width="120" height="26" fill="#0A3866" opacity="0.85" rx="1"/>
                    <text x="74" y="29" text-anchor="middle" font-size="6.5" font-weight="900" fill="#FFFFFF" font-family="Roboto, Arial, sans-serif">
                        {{ \Illuminate\Support\Str::limit($selectedPub->title, 42) }}
                    </text>
                    <text x="74" y="48" text-anchor="middle" font-size="8" font-weight="900" fill="#E67E22" font-family="Roboto, Arial, sans-serif">TAHUN {{ $selectedPub->year }}</text>
                    <rect x="14" y="160" width="60" height="18" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="0.4" rx="1"/>
                    <text x="44" y="168" text-anchor="middle" font-size="4.6" font-weight="700" fill="#64748B">Logo BPS (vektor)</text>
                    <rect x="100" y="160" width="34" height="18" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="0.4" rx="1"/>
                    <text x="117" y="170" text-anchor="middle" font-size="4" font-weight="700" fill="#64748B">Barcode ISSN</text>
                </svg>
            </div>
        </div>
    </div>

    <!-- ================= LEMBAR PEMBATAS BAB ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Lembar Pembatas Bab</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if($firstNar)
                            Bab {{ $firstNar->chapter_number }}: {{ $firstNar->title_id ?: 'Judul belum diisi' }}
                        @else
                            Belum ada naskah bab pada publikasi ini
                        @endif
                    </p>
                </div>
                <span class="text-xs bg-orange-100 text-bps-darkorange font-bold px-2 py-0.5 rounded">Aksen Oranye #E67E22</span>
            </div>

            <div class="w-full max-w-[260px] mx-auto aspect-[1/1.414] bg-white text-slate-800 rounded-lg shadow-xl overflow-hidden flex flex-col justify-between p-7 border-2 border-slate-200 relative">
                <div>
                    <div class="text-6xl font-black text-bps-orange tracking-tight">
                        {{ $firstNar?->chapter_number ?? '—' }}
                    </div>
                    <h3 class="text-lg font-black text-bps-navy uppercase mt-1 leading-snug">
                        {{ $firstNar?->title_id ?: 'Judul Bab Belum Ada Data' }}
                    </h3>
                    <p class="text-xs text-slate-400 italic mt-0.5">
                        {{ $firstNar?->title_en ?: 'Chapter title not yet available' }}
                    </p>

                    <div class="mt-7 bg-amber-50/80 border border-amber-200 rounded-lg p-4 text-left">
                        <span class="text-[9px] font-bold text-amber-800 uppercase block tracking-wider">
                            Indikator Kunci / Key Figures
                        </span>
                        {{-- INTEGRITAS: luas wilayah TIDAK boleh dipromosikan menjadi
                             indikator kunci. Nilai harus berasal dari naskah bab. --}}
                        @if($hasDividerData)
                            <div class="text-2xl font-black text-bps-darkorange mt-1">{{ $firstNar->highlight_value }}</div>
                            <div class="text-xs font-semibold text-amber-900 mt-0.5">{{ $firstNar->highlight_label ?: 'Tanpa label' }}</div>
                        @else
                            <div class="text-sm font-black text-amber-700 italic mt-2">Belum ada data</div>
                            <div class="text-[10px] font-semibold text-amber-800/80 mt-0.5 leading-relaxed">
                                Indikator kunci Bab {{ $firstNar?->chapter_number ?? '—' }} belum diisi di Meja Redaksi.
                                Angka tidak boleh dikarang oleh sistem.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-3 text-[9px] text-slate-400 flex justify-between">
                    <span>{{ \Illuminate\Support\Str::limit($selectedPub->title, 32) }}</span>
                    <span>BPS Jember</span>
                </div>
            </div>
        </div>

        <!-- ================= PALET WARGA BPS ================= -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-bps-navy">Palet Warna Korporat BPS</h2>
            <p class="text-xs text-slate-500 mt-0.5 mb-4">Klik kotak untuk menyalin kode warna ke papan klip.</p>

            <div class="space-y-2.5" x-data="{ copied: null }">
                @foreach([
                    ['Navy BPS',      '#0A3866', '10, 56, 102',   '100, 45, 0, 0',   'bg-[#0A3866]', 'Primer — judul bab, tabel, garis pembatas bab'],
                    ['Oranye Aksen',  '#E67E22', '230, 126, 34',  '0, 45, 85, 10',   'bg-[#E67E22]', 'Aksen strip, nomor bab, tautan aktif'],
                    ['Teal Pendukung','#16A085', '22, 160, 133',  '86, 0, 17, 37',   'bg-[#16A085]', 'Pendukung — highlight grafik & status QC'],
                ] as $swatch)
                    <button type="button" x-on:click="navigator.clipboard?.writeText(@js($swatch[1])); copied = @js($swatch[1]); setTimeout(() => copied = null, 1500)"
                            class="w-full text-left flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:border-bps-navy hover:shadow-md transition-all bg-slate-50 group">
                        <span class="w-12 h-12 shrink-0 rounded-lg {{ $swatch[4] }} shadow-inner border border-black/10"></span>
                        <span class="flex-1 min-w-0">
                            <span class="flex items-center gap-2">
                                <span class="text-xs font-black text-bps-navy">{{ $swatch[0] }}</span>
                                <code class="text-[11px] font-mono font-bold text-slate-600">{{ $swatch[1] }}</code>
                                <span x-show="copied === @js($swatch[1])" x-cloak
                                      class="text-[9px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Tersalin</span>
                            </span>
                            <span class="block text-[10px] text-slate-500 mt-0.5">
                                RGB {{ $swatch[2] }} &middot; CMYK {{ $swatch[3] }}
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5 italic">{{ $swatch[5] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Gradasi Aksen (Biru BPS)</p>
                <div class="flex overflow-hidden rounded-lg border border-slate-200">
                    @foreach(['#062442', '#0A3866', '#124a80', '#2980B9', '#7FB8DC'] as $hex)
                        <div class="flex-1 h-9 relative group cursor-pointer" style="background:{{ $hex }}"
                             title="{{ $hex }} — klik untuk menyalin" x-on:click="navigator.clipboard?.writeText('{{ $hex }}')">
                            <span class="absolute inset-x-0 bottom-0 text-[7px] font-mono text-white/90 text-center opacity-0 group-hover:opacity-100 transition-opacity py-0.5">{{ $hex }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ================= GALERI TEMPLATE COVER ALTERNATIF ================= -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-bold text-bps-navy">Galeri Template Cover Alternatif</h2>
        <p class="text-xs text-slate-500 mt-0.5 mb-4">
            Seluruh template dirender vektor oleh <code class="font-mono">typst_engine/templates/cover.typ</code>.
            Pilih satu untuk melihat pratinjau; cover aktif tetap mengikuti Cover Standar BPS.
        </p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['Standar BPS',      'Navy #0A3866 + aksen oranye', 'from-[#0A3866] to-[#062442]', 'bg-bps-orange',  'text-bps-orange',  'A'],
                ['Faksimili Arsip',  'Sepia #7A5C3E + aksen krem',  'from-[#7A5C3E] to-[#4A3826]', 'bg-[#D9C7A7]',   'text-[#D9C7A7]',   'B'],
                ['Statistik Teal',   'Navy + aksen teal #16A085',   'from-[#0A3866] to-[#0d3b52]', 'bg-[#16A085]',   'text-[#16A085]',   'C'],
                ['Ringkas A5',       'Abu #475569 + aksen oranye',  'from-[#475569] to-[#1E293B]', 'bg-bps-orange',  'text-bps-orange',  'D'],
            ] as $tpl)
                <div class="group cursor-pointer">
                    <div class="relative w-full aspect-[1/1.414] rounded-lg overflow-hidden shadow-md border-2 border-transparent group-hover:border-bps-orange group-hover:shadow-xl transition-all bg-gradient-to-br {{ $tpl[2] }} text-white p-3 flex flex-col justify-between">
                        <div class="absolute top-0 left-0 right-0 h-1 {{ $tpl[3] }}"></div>
                        <div class="text-[5px] font-bold text-white/70 leading-tight">BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</div>
                        <div>
                            <div class="text-[8px] font-black uppercase leading-tight line-clamp-3">{{ $selectedPub->title }}</div>
                            <div class="text-[9px] font-black {{ $tpl[5] }} mt-1">TAHUN {{ $selectedPub->year }}</div>
                        </div>
                        <div class="text-[5px] font-semibold text-white/60">BPS JEMBER &middot; 3509</div>
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-black/30 text-white text-[8px] font-black flex items-center justify-center">{{ $tpl[4] }}</span>
                    </div>
                    <p class="text-[10px] font-bold text-bps-navy mt-1.5 text-center">{{ $tpl[0] }}</p>
                    <p class="text-[9px] text-slate-400 text-center">{{ $tpl[1] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    @endif

    <!-- ================= MANUAL OVERRIDE ================= -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="{ manualMode: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Mode Manual Override &mdash; Cover Kustom</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Aktifkan sakelar lalu unggah gambar cover pengganti standar Typst.
                    Berkas wajib &ge; 300&nbsp;DPI pada ukuran cetak akhir dan menyertakan bleed 3&nbsp;mm.
                </p>
            </div>
            <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                <span>Mode Otomatis Typst</span>
                <input type="checkbox" x-model="manualMode" name="manual_override" value="1"
                       class="w-5 h-5 rounded border-slate-300 text-bps-orange focus:ring-bps-orange">
                <span>Mode Manual Override</span>
            </label>
        </div>

        <div x-show="manualMode" x-cloak class="mt-4 pt-4 border-t border-slate-100">
            @if(isset($customCover))
            <div class="mb-3 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                Cover kustom aktif: <strong>{{ basename($customCover->file_path) }}</strong> (mode MANUAL_OVERRIDE).
            </div>
            @endif
            <form action="{{ route('covers.upload') }}" method="POST" enctype="multipart/form-data"
                  class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <input type="hidden" name="publication_id" value="{{ $selectedPub?->id }}">
                <input type="file" name="cover_image" required accept=".jpg,.jpeg,.png,.webp,.svg,.pdf"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-bps-orange file:text-white hover:file:bg-bps-darkorange cursor-pointer">
                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm whitespace-nowrap">
                    Unggah Cover Kustom
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
