@extends('layouts.app', ['title' => 'Ingesti Data OPD - SI-PENA'])

@section('content')
<div class="space-y-6" x-data="ingestionApp()">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase rounded-full bg-bps-navy text-white">Fase 1</span>
                <h1 class="text-xl font-bold text-bps-navy">Ingesti & Pembersihan Berkas Mentah OPD</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                Portal terpadu pengumpulan data angka resmi dari dinas/OPD Pemkab Jember. Engine Python secara otomatis membersihkan <em>merge cells</em>, menormalkan desimal koma/titik, mengagregasi data sekolah, dan memproses kuesioner VKD.
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-bps-navy border border-blue-200 shadow-sm">
                <svg class="w-4 h-4 mr-1.5 text-bps-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Audit Trail SHA-256 Aktif
            </span>
            <a href="#upload-card" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-bps-orange hover:bg-orange-600 text-white shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Unggah Berkas Baru
            </a>
        </div>
    </div>

    <!-- 3 Langkah Alur Kerja Simpel -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-white rounded-xl border border-blue-200/80 p-4 shadow-sm flex items-start space-x-3.5">
            <div class="w-8 h-8 rounded-lg bg-bps-navy text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                1
            </div>
            <div>
                <h3 class="text-xs font-bold text-bps-navy uppercase tracking-wide">Unduh Template Excel</h3>
                <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Pilih template resmi BPS di bawah (Tabel Wilayah, Dapodik/EMIS, atau Kuesioner VKD).
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-50 to-white rounded-xl border border-amber-200/80 p-4 shadow-sm flex items-start space-x-3.5">
            <div class="w-8 h-8 rounded-lg bg-bps-orange text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                2
            </div>
            <div>
                <h3 class="text-xs font-bold text-bps-orange uppercase tracking-wide">Isi Data Angka Resmi</h3>
                <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Bagikan berkas ke OPD atau isi data angka. Baris wilayah & petunjuk sudah terpasang baku.
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-white rounded-xl border border-emerald-200/80 p-4 shadow-sm flex items-start space-x-3.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                3
            </div>
            <div>
                <h3 class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Unggah & Ekstraksi Cepat</h3>
                <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Tarik dan lepas berkas ke form ingesti. Python worker mengekstrak angka ke database seketika.
                </p>
            </div>
        </div>
    </div>

<!-- INFOGRAFIS DIAGRAM ALIR KERJA PYTHON ENGINE -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
                <h2 class="text-base font-bold text-bps-navy flex items-center">
                    <svg class="w-5 h-5 mr-2 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"/></svg>
                    Diagram Alir Kerja Python Engine
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Rantai pengolahan otomatis dari berkas mentah OPD hingga tersimpan di basis data MySQL 8.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-semibold bg-sky-50 text-sky-700 border border-sky-200 font-mono">python_engine/parsers</span>
        </div>

        <div class="overflow-x-auto">
            <svg viewBox="0 0 900 120" class="w-full min-w-[720px] h-auto" role="img" aria-label="Diagram alir kerja Python Engine">
                <defs>
                    <marker id="pyArrow" markerWidth="9" markerHeight="9" refX="7" refY="4.5" orient="auto"><path d="M0,0 L9,4.5 L0,9 z" fill="#94A3B8"/></marker>
                </defs>
                <line x1="205" y1="60" x2="245" y2="60" stroke="#94A3B8" stroke-width="3" marker-end="url(#pyArrow)"/>
                <line x1="455" y1="60" x2="495" y2="60" stroke="#94A3B8" stroke-width="3" marker-end="url(#pyArrow)"/>
                <line x1="705" y1="60" x2="745" y2="60" stroke="#94A3B8" stroke-width="3" marker-end="url(#pyArrow)"/>

                <g>
                    <rect x="15" y="28" width="190" height="64" rx="12" fill="#F1F5F9" stroke="#0A3866" stroke-width="2.5"/>
                    <text x="110" y="54" text-anchor="middle" font-size="12" font-weight="800" fill="#0A3866">1. BERKAS MENTAH</text>
                    <text x="110" y="74" text-anchor="middle" font-size="10" fill="#64748B">Upload Berkas Mentah (.xlsx)</text>
                </g>
                <g>
                    <rect x="255" y="28" width="190" height="64" rx="12" fill="#F1F5F9" stroke="#0A3866" stroke-width="2.5"/>
                    <text x="350" y="54" text-anchor="middle" font-size="12" font-weight="800" fill="#0A3866">2. PEMBERSIHAN</text>
                    <text x="350" y="74" text-anchor="middle" font-size="10" fill="#64748B">Merge Cells &amp; Desimal</text>
                </g>
                <g>
                    <rect x="505" y="28" width="190" height="64" rx="12" fill="#F1F5F9" stroke="#0A3866" stroke-width="2.5"/>
                    <text x="600" y="54" text-anchor="middle" font-size="12" font-weight="800" fill="#0A3866">3. AGREGASI SATUAN</text>
                    <text x="600" y="74" text-anchor="middle" font-size="10" fill="#64748B">Agregasi Satuan / Sekolah</text>
                </g>
                <g>
                    <rect x="755" y="28" width="130" height="64" rx="12" fill="#E67E22" stroke="#0A3866" stroke-width="2.5"/>
                    <text x="820" y="54" text-anchor="middle" font-size="12" font-weight="800" fill="#FFFFFF">4. DATABASE</text>
                    <text x="820" y="74" text-anchor="middle" font-size="10" fill="#FFF7ED">MySQL 8</text>
                </g>
            </svg>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-4 text-[11px]">
            <p class="text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3"><strong class="text-bps-navy">1.</strong> Berkas dicatat dengan nomor versi dan hash SHA-256 sebelum diproses.</p>
            <p class="text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3"><strong class="text-bps-navy">2.</strong> <em>generic_cleaner.py</em> membuka gabungan sel, mengisi nilai turun, dan menormalkan angka desimal.</p>
            <p class="text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3"><strong class="text-bps-navy">3.</strong> <em>individual_aggregator.py</em> menjumlah data individu (sekolah/Dapodik) ke level desa/kecamatan.</p>
            <p class="text-slate-600 bg-slate-50 border border-slate-200 rounded-lg p-3"><strong class="text-bps-navy">4.</strong> Tabel hasil ekstraksi disimpan sebagai <code>PublicationTable</code> siap ditinjau editor.</p>
        </div>
    </div>

    <!-- PROGRESS BAR PENERIMAAN DATA DINAS -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <h2 class="text-base font-bold text-bps-navy">Persentase Penerimaan Data Dinas (Tahun {{ $activeYear ?? 'Aktif' }})</h2>
            <span class="text-xs font-bold {{ $opdAcceptance['percent'] > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                {{ $opdAcceptance['received'] }} / {{ $opdAcceptance['total'] }} OPD terkirim
            </span>
        </div>
        <div class="h-4 w-full bg-slate-100 border border-slate-200 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-sky-500 to-emerald-500 rounded-full transition-all" style="width: {{ $opdAcceptance['percent'] }}%"></div>
        </div>
        <p class="text-[11px] text-slate-500 mt-2">
            @if($opdAcceptance['percent'] === 0)
                Belum ada berkas dinas yang diterima untuk tahun ini. Unggah berkas OPD untuk memperbarui indikator.
            @else
                Tingkat penerimaan data sebesar <strong>{{ $opdAcceptance['percent'] }}%</strong> dari daftar OPD potensial Jember.
            @endif
        </p>
    </div>
<!-- PETUNJUK TEKNIS STRUKTUR KOLOM PER INSTANSI DINAS -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-5">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Petunjuk Teknis Struktur Kolom per Instansi Dinas</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pastikan nama kolom berkas OPD memuat kata kunci berikut agar dapat dikenali mesin ekstraksi.</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-semibold bg-bps-navy text-white">4 Instansi Kunci</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($opdGuides ?? [] as $guide)
            @php
                $tone = match($guide['color']) {
                    'sky' => ['bg-sky-50', 'border-sky-200', 'text-sky-800', 'bg-sky-600'],
                    'rose' => ['bg-rose-50', 'border-rose-200', 'text-rose-800', 'bg-rose-600'],
                    'emerald' => ['bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-emerald-600'],
                    default => ['bg-amber-50', 'border-amber-200', 'text-amber-800', 'bg-amber-600'],
                };
            @endphp
            <div class="rounded-xl border {{ $tone[1] }} {{ $tone[0] }} p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="inline-block {{ $tone[3] }} text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">{{ $guide['short'] }}</span>
                        <h3 class="text-sm font-bold text-slate-800 mt-2">{{ $guide['name'] }}</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $guide['chapter'] }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-500 mb-1.5">Kolom Wajib</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($guide['columns'] as $col)
                            <span class="inline-block bg-white border border-slate-200 text-slate-700 text-[10px] font-mono px-2 py-0.5 rounded">{{ $col }}</span>
                        @endforeach
                    </div>
                </div>
                <p class="text-[11px] {{ $tone[2] }} mt-3 leading-snug flex items-start">
                    <svg class="w-3.5 h-3.5 mr-1 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    {{ $guide['notes'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- CHECKLIST MANDIRI SEBELUM MENGUNGGAH -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="{ checks: [false,false,false,false,false] }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Checklist Mandiri Sebelum Mengunggah</h2>
                <p class="text-xs text-slate-500 mt-0.5">Centang seluruh butir untuk menekan risiko penolakan berkas oleh mesin ekstraksi.</p>
            </div>
            <span class="text-xs font-bold" :class="checks.every(Boolean) ? 'text-emerald-700' : 'text-slate-400'"
                  x-text="checks.filter(Boolean).length + ' / 5 siap'"></span>
        </div>
        @php
            $checklistItems = [
                'Berkas memakai template resmi BPS (.xlsx) dan tidak dilindungi kata sandi.',
                'Tidak ada merge cells pada kolom Kecamatan/Desa; setiap baris terisi penuh.',
                'Angka desimal konsisten (tidak campur koma dan titik dalam satu kolom).',
                'Nama wilayah mengikuti penulisan resmi BPS (hindari singkatan tidak baku).',
                'Nama OPD sumber dan nomor bab/tabel sudah sesuai publikasi target.',
            ];
        @endphp
        <ul class="space-y-2">
            @foreach($checklistItems as $i => $item)
            <li class="flex items-start space-x-3 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3">
                <input type="checkbox" x-model="checks[{{ $i }}]" class="mt-0.5 rounded border-slate-300 text-bps-orange focus:ring-bps-orange">
                <span>{{ $item }}</span>
            </li>
            @endforeach
        </ul>
        <div class="mt-3 text-[11px] rounded-lg p-3 transition-colors" :class="checks.every(Boolean) ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-amber-50 border border-amber-200 text-amber-800'">
            <span x-show="checks.every(Boolean)" x-cloak>&check; Seluruh prasyarat terpenuhi &mdash; berkas siap diunggah.</span>
            <span x-show="!checks.every(Boolean)">&bull; Lengkapi seluruh checklist sebelum mengunggah untuk menghindari kegagalan pembersihan data.</span>
        </div>
    </div>
<!-- GRAFIK DISTRIBUSI TABEL PER BAB PUBLIKASI (Bar + Donut) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar horizontal: jumlah tabel per bab -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Distribusi Tabel Ingesti per Bab Publikasi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jumlah tabel yang telah terekstrak pada tahun {{ $activeYear ?? 'aktif' }}.</p>
                </div>
                <span class="text-[11px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">PublicationTable</span>
            </div>

            @if(!empty($filesByChapter))
            @php $maxChapterCount = max(array_column($filesByChapter, 'count')) ?: 1; @endphp
            <div class="space-y-2.5">
                @foreach($filesByChapter as $row)
                <div>
                    <div class="flex items-center justify-between text-[11px] mb-1">
                        <span class="font-semibold text-slate-700">{{ $row['label'] }}</span>
                        <span class="font-mono font-bold text-bps-navy">{{ $row['count'] }} tabel</span>
                    </div>
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-bps-navy to-bps-blue rounded-full" style="width: {{ round($row['count'] * 100 / $maxChapterCount, 2) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada tabel teringesti untuk tahun aktif. Unggah berkas OPD untuk menampilkan distribusi.</p>
            @endif
        </div>
    </div>

    {{-- Distribusi berkas mentah per OPD (sumber: kolom opd_source_name). --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Distribusi Berkas Mentah per OPD</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Jumlah berkas yang sudah diunggah tiap instansi dinas pada tahun {{ $activeYear ?? 'aktif' }},
                    diurutkan dari pengirim terbanyak (maksimal 12 OPD ditampilkan).
                </p>
            </div>
            <span class="text-[11px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">RawDataFile.opd_source_name</span>
        </div>

        @if(!empty($filesByOpd))
            @php $maxOpdFiles = max(array_column($filesByOpd, 'count')) ?: 1; @endphp
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-2.5">
                @foreach($filesByOpd as $row)
                <div>
                    <div class="flex items-center justify-between text-[11px] mb-1 gap-2">
                        <span class="font-semibold text-slate-700 truncate" title="{{ $row['opd'] }}">{{ $row['opd'] }}</span>
                        <span class="font-mono font-bold text-bps-navy shrink-0">{{ $row['count'] }} berkas</span>
                    </div>
                    <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-bps-orange to-amber-400 rounded-full"
                             style="width: {{ round($row['count'] * 100 / $maxOpdFiles, 2) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="border-2 border-dashed border-slate-200 rounded-lg py-8 text-center">
                <p class="text-xs font-bold text-slate-400 italic">Belum ada data</p>
                <p class="text-[10px] text-slate-400 mt-1">
                    Distribusi berkas per OPD muncul setelah operator mengunggah berkas mentah
                    yang mencantumkan nama instansi pengirim.
                </p>
            </div>
        @endif
    </div>

        <!-- Donut: proporsi tabel per bab -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-base font-bold text-bps-navy mb-4">Proporsi per Bab</h2>
            @if(!empty($filesByChapter))
            @php
                $palette = ['#0A3866', '#2980B9', '#E67E22', '#16A085', '#8E44AD', '#C0392B', '#D35400', '#27AE60', '#2C3E50'];
                $totalTables = array_sum(array_column($filesByChapter, 'count')) ?: 1;
                $circumference = 2 * M_PI * 52;
                $offset = 0.0;
            @endphp
            <div class="flex justify-center">
                <svg viewBox="0 0 140 140" class="w-44 h-44" role="img" aria-label="Donut distribusi tabel per bab">
                    <g transform="rotate(-90 70 70)">
                        @foreach($filesByChapter as $i => $row)
                            @php
                                $frac = $row['count'] / $totalTables;
                                $dash = $frac * $circumference;
                                $color = $palette[$i % count($palette)];
                            @endphp
                            <circle cx="70" cy="70" r="52" fill="none" stroke="{{ $color }}" stroke-width="22"
                                    stroke-dasharray="{{ number_format($dash, 2, '.', '') }} {{ number_format($circumference - $dash, 2, '.', '') }}"
                                    stroke-dashoffset="{{ number_format(-$offset, 2, '.', '') }}"/>
                            @php $offset += $dash; @endphp
                        @endforeach
                    </g>
                    <text x="70" y="66" text-anchor="middle" font-size="20" font-weight="900" fill="#0A3866">{{ $totalTables }}</text>
                    <text x="70" y="82" text-anchor="middle" font-size="9" fill="#64748B">TABEL</text>
                </svg>
            </div>
            <div class="mt-3 space-y-1 max-h-32 overflow-y-auto">
                @foreach($filesByChapter as $i => $row)
                <div class="flex items-center justify-between text-[11px]">
                    <span class="inline-flex items-center text-slate-600 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-sm mr-1.5 shrink-0" style="background: {{ $palette[$i % count($palette)] }}"></span>
                        <span class="truncate">{{ $row['label'] }}</span>
                    </span>
                    <span class="font-mono font-bold text-slate-700 shrink-0 ml-2">{{ $row['count'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada data.</p>
            @endif
        </div>
    </div>
    <!-- PUSAT UNDUH TEMPLATE EXCEL RESMI BPS (TEMPLATE HUB) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
            <div>
                <h2 class="text-base font-bold text-bps-navy flex items-center">
                    <svg class="w-5 h-5 mr-2 text-bps-navy" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Pusat Unduh Template Excel Resmi BPS (Standar Ingesti)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Gunakan template ini untuk menjamin data OPD dapat diproses 100% tanpa kendala format atau penolakan sistem.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Kompatibel dengan Python Engine
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
            <!-- Template 1: Tabel Standar Wilayah (Direct Table) -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 flex flex-col justify-between hover:border-bps-navy hover:shadow-md transition-all group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-bps-navy border border-blue-200">
                            Mode: DIRECT TABLE
                        </span>
                        <span class="text-xs text-slate-400 font-mono">.xlsx</span>
                    </div>
                    <h3 class="text-sm font-bold text-bps-navy group-hover:text-blue-800 transition-colors flex items-center">
                        Template Tabel Standar Wilayah
                    </h3>
                    <p class="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                        Format resmi tabel sektoral OPD (pertanian, kependudukan, sarana, dll.). Sudah memuat nama <strong>31 Kecamatan se-Jember</strong> atau nama Desa, rumus total otomatis, serta sheet petunjuk teknis.
                    </p>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <a href="{{ route('ingestion.template.download', 'standard') }}" class="w-full inline-flex items-center justify-center px-3 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh DDA (31 Kecamatan)
                        </a>
                    </div>

                    <!-- Unduh Template Spesifik KDA Berdasarkan Pilihan Publikasi -->
                    <div class="pt-1">
                        <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1">Unduh Khusus KDA (Daftar Desa Terisi):</label>
                        <div class="flex gap-1.5">
                            <select x-model="kdaDownloadPubId" class="text-[11px] rounded-lg border-slate-300 py-1.5 px-2 bg-white text-slate-700 flex-1 focus:ring-1 focus:ring-bps-navy">
                                <option value="">-- Pilih Kecamatan KDA --</option>
                                @foreach($publications->where('type', 'KDA') as $kdaPub)
                                    <option value="{{ $kdaPub->id }}">{{ $kdaPub->district?->name ?? $kdaPub->title }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="downloadKdaTemplate()" :disabled="!kdaDownloadPubId" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 disabled:opacity-40 text-slate-800 text-[11px] font-bold rounded-lg transition-colors shrink-0">
                                Unduh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template 2: Data Sekolah (Dapodik / EMIS) -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 flex flex-col justify-between hover:border-bps-orange hover:shadow-md transition-all group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-100 text-bps-orange border border-orange-200">
                            Mode: AGGREGATE SCHOOL
                        </span>
                        <span class="text-xs text-slate-400 font-mono">.xlsx</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-bps-orange transition-colors flex items-center">
                        Template Data Pendidikan (Sekolah)
                    </h3>
                    <p class="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                        Format data individu per sekolah (Dapodik/EMIS/Kemenag) untuk Bab 4. Memuat kolom nama sekolah, kecamatan, desa, jenjang (SD/SMP/SMA/SMK), status (Negeri/Swasta), jumlah guru, dan murid.
                    </p>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200/80">
                    <a href="{{ route('ingestion.template.download', 'schools') }}" class="w-full inline-flex items-center justify-center px-3 py-2 bg-bps-orange hover:bg-orange-600 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Template Dapodik/EMIS
                    </a>
                    <span class="block text-center text-[10px] text-slate-400 mt-1.5">Dilengkapi validasi dropdown jenjang & status</span>
                </div>
            </div>

            <!-- Template 3: Kuesioner VKD (Survei Kebutuhan Data) -->
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-5 flex flex-col justify-between hover:border-emerald-600 hover:shadow-md transition-all group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Mode: SKD / VKD
                        </span>
                        <span class="text-xs text-slate-400 font-mono">.xlsx</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors flex items-center">
                        Template Kuesioner VKD (SKD)
                    </h3>
                    <p class="text-[11px] text-slate-600 mt-1.5 leading-relaxed">
                        Format respon kuesioner VKD lengkap dengan <strong>12 atribut pelayanan publik BPS</strong> (U1–U12) kepuasan (X) dan kepentingan (Y) skala 1–4 untuk kalkulasi IKK, IPAK, dan Diagram Kartesius IPA.
                    </p>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200/80">
                    <a href="{{ route('ingestion.template.download', 'skd') }}" class="w-full inline-flex items-center justify-center px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh Template Kuesioner VKD
                    </a>
                    <span class="block text-center text-[10px] text-slate-400 mt-1.5">Dilengkapi lembar panduan 12 atribut Permenpan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULIR UNGGAH SUPER SIMPEL & INTERAKTIF -->
    <div id="upload-card" class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h2 class="text-base font-bold text-bps-navy flex items-center">
                <svg class="w-5 h-5 mr-2 text-bps-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Formulir Unggah Berkas Excel Mentah
            </h2>
            <span class="text-xs text-slate-500">Mendukung format .xlsx, .xls, .csv hingga 20 MB</span>
        </div>

        <form action="{{ route('ingestion.upload') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-5">
            @csrf

            <!-- DRAG & DROP FILE ZONE -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    Berkas Excel Mentah OPD <span class="text-rose-500">*</span>
                </label>
                <div
                    class="border-2 border-dashed rounded-xl p-6 text-center transition-all cursor-pointer relative"
                    :class="isDragging ? 'border-bps-navy bg-blue-50/70 scale-[0.99]' : (fileName ? 'border-emerald-500 bg-emerald-50/30' : 'border-slate-300 hover:border-bps-navy bg-slate-50/60')"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleFileDrop($event)"
                    @click="$refs.fileInput.click()"
                >
                    <input
                        type="file"
                        name="excel_file"
                        x-ref="fileInput"
                        required
                        accept=".xlsx,.xls,.csv"
                        class="hidden"
                        @change="handleFileSelect($event)"
                    >

                    <!-- State 1: Belum Ada File -->
                    <template x-if="!fileName">
                        <div class="space-y-2">
                            <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 text-bps-navy flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div class="text-xs text-slate-700">
                                <span class="font-bold text-bps-navy">Klik untuk memilih berkas</span> atau tarik dan lepas (drag & drop) berkas ke sini
                            </div>
                            <p class="text-[11px] text-slate-400">Berkas .xlsx, .xls, atau .csv dari dinas/OPD terkait</p>
                        </div>
                    </template>

                    <!-- State 2: Berkas Terpilih -->
                    <template x-if="fileName">
                        <div class="flex items-center justify-center space-x-3 py-1">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="text-xs font-bold text-slate-800" x-text="fileName"></p>
                                <div class="flex items-center space-x-2 mt-0.5">
                                    <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full" x-text="fileSize"></span>
                                    <span class="text-[10px] text-slate-500">Klik area ini jika ingin mengganti berkas</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Target Publikasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Target Publikasi <span class="text-rose-500">*</span>
                    </label>
                    <select
                        name="publication_id"
                        x-model="selectedPublicationId"
                        @change="onPublicationChange()"
                        required
                        class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50"
                    >
                        <option value="">-- Pilih Buku Publikasi Target --</option>
                        @foreach($publications as $pub)
                            <option
                                value="{{ $pub->id }}"
                                data-type="{{ $pub->type }}"
                                data-district="{{ $pub->district?->name ?? '' }}"
                                {{ old('publication_id') == $pub->id ? 'selected' : '' }}
                            >
                                {{ $pub->title }} [{{ $pub->type }} - {{ $pub->year }}]{{ $pub->isLocked() ? ' - TERKUNCI (' . $pub->status . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Petunjuk Spesifik KDA -->
                    <p x-show="selectedPublicationType === 'KDA'" class="text-[11px] text-bps-navy mt-1.5 flex items-center bg-blue-50/70 p-2 rounded border border-blue-200">
                        <svg class="w-3.5 h-3.5 mr-1 text-bps-orange shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                        <span>Publikasi KDA <strong>Kecamatan <span x-text="selectedDistrictName"></span></strong> terpilih. Disarankan memakai template dengan daftar desa terisi.</span>
                    </p>
                </div>

                <!-- Instansi / OPD Sumber dengan Datalist Autocomplete -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nama Instansi / OPD Sumber Data <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="opd_source_name"
                        list="opd-suggestions"
                        required
                        value="{{ old('opd_source_name') }}"
                        placeholder="Ketik atau pilih OPD (mis. Dinas Pendidikan, Dispendukcapil)"
                        class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50"
                    >
                    <datalist id="opd-suggestions">
                        @foreach($commonOpds as $opd)
                            <option value="{{ $opd }}"></option>
                        @endforeach
                    </datalist>
                    <span class="text-[10px] text-slate-400 mt-1 block">Tersedia saran autocomplete instansi resmi Pemkab Jember</span>
                </div>
            </div>

            <!-- MODE EKSTRAKSI DATA (VISUAL PICKER) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">
                    Mode Ekstraksi Data (Engine Python) <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Option 1: DIRECT -->
                    <label
                        class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                        :class="dataMode === 'DIRECT' ? 'border-bps-navy bg-blue-50/50 shadow-sm ring-1 ring-bps-navy' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-bps-navy">Pembersih Tabel Standar</span>
                            <input type="radio" name="data_mode" value="DIRECT" x-model="dataMode" class="text-bps-navy focus:ring-bps-navy">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Membersihkan merge cells, baris kosong, dan desimal koma secara langsung. Cocok untuk tabel dinas reguler.
                        </p>
                        <div class="mt-2 text-[10px] text-blue-700 font-medium">
                            Cocok dengan: <strong>Template Tabel Wilayah</strong>
                        </div>
                    </label>

                    <!-- Option 2: AGGREGATE_SCHOOL -->
                    <label
                        class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                        :class="dataMode === 'AGGREGATE_SCHOOL' ? 'border-bps-orange bg-orange-50/50 shadow-sm ring-1 ring-bps-orange' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-bps-orange">Agregasi Dapodik/EMIS</span>
                            <input type="radio" name="data_mode" value="AGGREGATE_SCHOOL" x-model="dataMode" class="text-bps-orange focus:ring-bps-orange">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Mengagregasi data sekolah individual menjadi matriks rekap guru, murid, dan unit sekolah per wilayah (Bab 4).
                        </p>
                        <div class="mt-2 text-[10px] text-orange-700 font-medium">
                            Cocok dengan: <strong>Template Data Pendidikan</strong>
                        </div>
                    </label>

                    <!-- Option 3: SKD_VKD -->
                    <label
                        class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition-all"
                        :class="dataMode === 'SKD_VKD' ? 'border-emerald-600 bg-emerald-50/50 shadow-sm ring-1 ring-emerald-600' : 'border-slate-200 bg-white hover:bg-slate-50'"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold text-emerald-800">Kuesioner VKD (SKD)</span>
                            <input type="radio" name="data_mode" value="SKD_VKD" x-model="dataMode" class="text-emerald-700 focus:ring-emerald-600">
                        </div>
                        <p class="text-[11px] text-slate-500 leading-snug">
                            Kalkulasi formula SKD resmi (IKK, IPAK, Gap Analysis, dan Diagram Kuadran Kartesius IPA).
                        </p>
                        <div class="mt-2 text-[10px] text-emerald-700 font-medium">
                            Cocok dengan: <strong>Template Kuesioner VKD</strong>
                        </div>
                    </label>
                </div>
            </div>

            <!-- DETAIL BAB & TABEL (PENGATURAN LANJUTAN) -->
            <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">Pengaturan Bab & Metadata Tabel</span>
                    <span class="text-[11px] text-slate-400">Nomor tabel dapat dikosongkan untuk penomoran otomatis</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nomor Bab -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Bab Terkait <span class="text-rose-500">*</span></label>
                        <select name="chapter_number" required class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2 bg-white">
                            <option value="1">Bab 1: Geografi dan Iklim</option>
                            <option value="2">Bab 2: Pemerintahan</option>
                            <option value="3">Bab 3: Penduduk</option>
                            <option value="4" selected>Bab 4: Sosial dan Kesejahteraan Rakyat</option>
                            <option value="5">Bab 5: Pertanian, Kehutanan, Peternakan, dan Perikanan</option>
                            <option value="6">Bab 6: Pertambangan, Energi, dan Konstruksi</option>
                            <option value="7">Bab 7: Perdagangan, Hotel, Pariwisata, dan Transportasi</option>
                            <option value="8">Bab 8: Keuangan Daerah dan Harga</option>
                            <option value="9">Bab 9: Pengeluaran Penduduk</option>
                            <option value="10">Bab 10: Pendapatan Regional</option>
                        </select>
                    </div>

                    <!-- Nomor Tabel -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor / Identitas Tabel (Opsional)</label>
                        <input type="text" name="table_number" placeholder="Contoh: 4.1.1 (kosongkan jika otomatis)" class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan / Kontak Narahubung OPD (Opsional)</label>
                    <textarea name="notes" rows="2" placeholder="Catatan versi revisi data, nama narahubung OPD, atau kondisi anomali data..." class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2 bg-white"></textarea>
                </div>
            </div>

            <!-- Action Submit -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                <div class="text-[11px] text-slate-500">
                    Sistem akan menyimpan salinan asli di <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-700 font-mono">storage/app/private/raw_excel</code> dengan verifikasi hash SHA-256.
                </div>
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm transition-all disabled:opacity-50"
                >
                    <template x-if="!isSubmitting">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Unggah & Ekstraksi Python
                        </span>
                    </template>
                    <template x-if="isSubmitting">
                        <span class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses Ekstraksi Data...
                        </span>
                    </template>
                </button>
            </div>
        </form>
    </div>

    <!-- RIWAYAT BERKAS MENTAH OPD & AUDIT TRAIL VERSIONING -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-bps-navy flex items-center">
                    <svg class="w-4 h-4 mr-2 text-bps-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Riwayat Berkas Mentah OPD & Audit Versioning
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Seluruh berkas mentah diarsipkan permanen untuk audit kepatuhan. Anda dapat mengunduh berkas asli atau melihat pratinjau hasil ekstraksi.
                </p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-500 font-medium">Total: {{ $rawFiles->total() }} berkas terdaftar</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Versi & Waktu</th>
                        <th class="py-3 px-4">Instansi OPD</th>
                        <th class="py-3 px-4">Target Publikasi</th>
                        <th class="py-3 px-4">Nama Berkas</th>
                        <th class="py-3 px-4">Checksum SHA-256</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi Berkas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($rawFiles as $file)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-bold text-bps-navy bg-blue-50 px-2 py-0.5 rounded text-[11px] border border-blue-200">
                                v{{ $file->version_number }}
                            </span>
                            <span class="text-slate-400 block text-[10px] mt-1">{{ $file->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $file->opd_source_name }}</td>
                        <td class="py-3 px-4 text-slate-600 max-w-[200px] truncate" title="{{ $file->publication?->title }}">
                            {{ $file->publication?->title ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-700 font-mono text-[11px]">
                            <span class="truncate max-w-[180px] inline-block" title="{{ $file->original_filename }}">
                                {{ $file->original_filename }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-mono text-[10px] text-slate-400" title="{{ $file->file_hash_sha256 }}">
                            {{ substr($file->file_hash_sha256, 0, 14) }}...
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($file->status === 'INGESTED')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <svg class="w-3 h-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    INGESTED
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $file->notes }}">
                                    <svg class="w-3 h-3 mr-1 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                    FAILED
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center space-x-1.5">
                                <!-- Tombol Unduh Berkas Asli -->
                                <a
                                    href="{{ route('ingestion.raw-file.download', $file->id) }}"
                                    title="Unduh Berkas Asli OPD"
                                    class="p-1.5 text-slate-600 hover:text-bps-navy hover:bg-blue-50 rounded-lg transition-colors border border-slate-200"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>

                                <!-- Tombol Prapinjau Data -->
                                <button
                                    type="button"
                                    @click="openPreview({{ $file->id }})"
                                    title="Lihat Data Terekstraksi"
                                    class="p-1.5 text-slate-600 hover:text-bps-orange hover:bg-orange-50 rounded-lg transition-colors border border-slate-200"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-slate-400">
                            <div class="max-w-xs mx-auto space-y-2">
                                <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-xs font-semibold text-slate-600">Belum ada berkas OPD yang diunggah</p>
                                <p class="text-[11px] text-slate-400">Unduh salah satu template Excel di atas dan mulai unggah berkas data resmi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rawFiles->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $rawFiles->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL PRATINJAU DATA TEREKSTRAKSI (ALPINE.JS MODAL) -->
    <div
        x-show="isPreviewOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @keydown.escape.window="isPreviewOpen = false"
    >
        <div
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-4xl w-full max-h-[85vh] flex flex-col overflow-hidden"
            @click.away="isPreviewOpen = false"
        >
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-bps-navy flex items-center">
                        <svg class="w-4 h-4 mr-2 text-bps-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Prapinjau Data Hasil Ekstraksi Python
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5" x-text="previewData?.raw_file ? (previewData.raw_file.filename + ' (' + previewData.raw_file.opd_source_name + ' - v' + previewData.raw_file.version + ')') : 'Memuat data...'"></p>
                </div>
                <button
                    type="button"
                    @click="isPreviewOpen = false"
                    class="text-slate-400 hover:text-slate-600 rounded-lg p-1.5 hover:bg-slate-200/60 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-4 text-xs">
                <!-- Loading State -->
                <div x-show="isPreviewLoading" class="py-12 text-center text-slate-400">
                    <svg class="animate-spin h-8 w-8 mx-auto text-bps-navy mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p>Memuat data terekstraksi...</p>
                </div>

                <!-- Content State -->
                <div x-show="!isPreviewLoading && previewData">
                    <template x-if="previewData?.tables && previewData.tables.length > 0">
                        <div class="space-y-4">
                            <template x-for="tbl in previewData.tables" :key="tbl.id">
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <div class="bg-slate-100/80 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                                        <div class="font-bold text-bps-navy">
                                            <span x-text="'Tabel ' + tbl.table_number + ': '"></span>
                                            <span x-text="tbl.title_id" class="font-semibold text-slate-700"></span>
                                        </div>
                                        <span class="text-[10px] bg-blue-100 text-bps-navy px-2 py-0.5 rounded font-mono" x-text="'Bab ' + tbl.chapter_number"></span>
                                    </div>

                                    <!-- Table preview -->
                                    <div class="overflow-x-auto max-h-72">
                                        <table class="w-full text-left text-[11px]">
                                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold sticky top-0">
                                                <tr>
                                                    <template x-for="header in (tbl.data?.headers || (tbl.data?.data?.[0] ? Object.keys(tbl.data.data[0]) : []))" :key="header">
                                                        <th class="py-2 px-3 border-r border-slate-200 last:border-r-0 whitespace-nowrap" x-text="header"></th>
                                                    </template>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                <template x-for="(row, idx) in (tbl.data?.data || []).slice(0, 15)" :key="idx">
                                                    <tr class="hover:bg-slate-50">
                                                        <template x-for="header in (tbl.data?.headers || (tbl.data?.data?.[0] ? Object.keys(tbl.data.data[0]) : []))" :key="header">
                                                            <td class="py-2 px-3 border-r border-slate-100 last:border-r-0 whitespace-nowrap font-mono text-[10px]" x-text="row[header] !== undefined ? row[header] : '-'"></td>
                                                        </template>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="!previewData?.tables || previewData.tables.length === 0">
                        <div class="p-8 text-center text-slate-400 bg-slate-50 rounded-xl">
                            <p class="font-semibold text-slate-600">Belum ada tabel yang diekstrak untuk berkas ini.</p>
                            <p class="text-[11px] mt-1" x-text="previewData?.raw_file?.notes || 'Periksa status berkas pada riwayat audit.'"></p>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button
                    type="button"
                    @click="isPreviewOpen = false"
                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function ingestionApp() {
    return {
        isDragging: false,
        fileName: '',
        fileSize: '',
        dataMode: 'DIRECT',
        selectedPublicationId: '',
        selectedPublicationType: '',
        selectedDistrictName: '',
        kdaDownloadPubId: '',
        isSubmitting: false,
        isPreviewOpen: false,
        isPreviewLoading: false,
        previewData: null,

        handleFileSelect(event) {
            const files = event.target.files;
            if (files && files[0]) {
                this.processFile(files[0]);
            }
        },

        handleFileDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files && files[0]) {
                this.$refs.fileInput.files = files;
                this.processFile(files[0]);
            }
        },

        processFile(file) {
            this.fileName = file.name;
            this.fileSize = this.formatBytes(file.size);
            this.smartDetectMode(file.name);
        },

        smartDetectMode(filename) {
            const low = filename.toLowerCase();
            if (low.includes('dapodik') || low.includes('sekolah') || low.includes('emis') || low.includes('guru') || low.includes('murid')) {
                this.dataMode = 'AGGREGATE_SCHOOL';
            } else if (low.includes('vkd') || low.includes('skd') || low.includes('survei') || low.includes('kuesioner')) {
                this.dataMode = 'SKD_VKD';
            }
        },

        onPublicationChange() {
            const sel = this.$el.querySelector('select[name="publication_id"]');
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            if (opt) {
                this.selectedPublicationType = opt.getAttribute('data-type') || '';
                this.selectedDistrictName = opt.getAttribute('data-district') || '';
                if (this.selectedPublicationType === 'KDA') {
                    this.kdaDownloadPubId = opt.value;
                }
            }
        },

        downloadKdaTemplate() {
            if (!this.kdaDownloadPubId) return;
            window.location.href = "{{ url('/ingestion/template/standard') }}?publication_id=" + this.kdaDownloadPubId;
        },

        openPreview(rawFileId) {
            this.isPreviewOpen = true;
            this.isPreviewLoading = true;
            this.previewData = null;

            fetch("{{ url('/ingestion/raw-files') }}/" + rawFileId + "/preview", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.previewData = data;
                this.isPreviewLoading = false;
            })
            .catch(err => {
                console.error(err);
                this.isPreviewLoading = false;
            });
        },

        formatBytes(bytes, decimals = 1) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    };
}
</script>
@endpush
@endsection
