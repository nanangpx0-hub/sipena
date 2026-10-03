@extends('layouts.app', ['title' => 'Dashboard Utama - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Top Headline & Countdown Widget -->
    <div class="bg-gradient-to-r from-bps-navy to-bps-darknavy rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10">
            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <span class="inline-block bg-bps-orange/90 text-white text-[11px] font-bold px-2.5 py-0.5 rounded uppercase tracking-wider mb-2">
                    Sistem Penerbitan dan Penataan Angka Daerah
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Publikasi Daerah BPS Kabupaten Jember {{ $activeYear ?? date('Y') }}
                </h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Otomasi kompilasi 31 Kecamatan Dalam Angka (KDA), Kabupaten Jember Dalam Angka (DDA), dan Analisis SKD berbasis mesin Typst & Python.
                </p>
            </div>

            <!-- Countdown Widget -->
            @if($nearestDeadline)
            @php try { $deadlineIso = $nearestDeadline->toIso8601String(); $deadlineLabel = $nearestDeadline->format('d F Y'); } catch (\Throwable $e) { $deadlineIso = null; $deadlineLabel = null; } @endphp
            @if(!empty($deadlineIso))
            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20 text-center min-w-[220px]" 
                 x-data="{
                    target: new Date('{{ $deadlineIso }}').getTime(),
                    now: new Date().getTime(),
                    days: 0, hours: 0, minutes: 0,
                    init() {
                        this.update();
                        setInterval(() => { this.now = new Date().getTime(); this.update(); }, 1000);
                    },
                    update() {
                        let diff = Math.max(0, this.target - this.now);
                        this.days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        this.hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        this.minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    }
                 }">
                <div class="text-[11px] font-semibold tracking-wider text-slate-300 uppercase">Target Rilis Resmi</div>
                <div class="text-xs text-amber-300 font-medium mt-0.5">{{ $deadlineLabel }}</div>
                <div class="flex items-center justify-center space-x-2 mt-2">
                    <div class="bg-black/30 px-2 py-1 rounded">
                        <span class="text-xl font-bold text-white" x-text="days">0</span>
                        <span class="text-[9px] block text-slate-400">HARI</span>
                    </div>
                    <span class="font-bold">:</span>
                    <div class="bg-black/30 px-2 py-1 rounded">
                        <span class="text-xl font-bold text-white" x-text="hours">0</span>
                        <span class="text-[9px] block text-slate-400">JAM</span>
                    </div>
                    <span class="font-bold">:</span>
                    <div class="bg-black/30 px-2 py-1 rounded">
                        <span class="text-xl font-bold text-white" x-text="minutes">0</span>
                        <span class="text-[9px] block text-slate-400">MENIT</span>
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>

    <!-- Quick Stats Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Publikasi KDA</p>
                <h3 class="text-2xl font-bold text-bps-navy mt-1">31 Kecamatan</h3>
                <span class="text-xs text-emerald-600 font-medium">100% Terdaftar di BPS</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-bps-navy flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Berkas Mentah OPD</p>
                <h3 class="text-2xl font-bold text-bps-navy mt-1">{{ $totalRawFiles }} Berkas</h3>
                <span class="text-xs text-blue-600 font-medium">Audit Versi SHA-256</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siap Rilis (Released)</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $statusCounts['FINAL_RELEASED'] ?? 0 }} Buku</h3>
                <span class="text-xs text-slate-500 font-medium">PDF 300 DPI Tersedia</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-200 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terkunci (Approved)</p>
                <h3 class="text-2xl font-bold text-purple-700 mt-1">{{ $statusCounts['APPROVED_LOCKED'] ?? 0 }} Buku</h3>
                <span class="text-xs text-purple-600 font-medium">Siap Antrean Typst</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
        </div>
    </div>

    <!-- Publikasi Utama Induk (DDA & SKD) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- DDA Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded">DDA (Buku Induk)</span>
                    <span class="text-xs text-slate-500">Katalog: {{ $dda?->catalog_number ?: 'Belum ada data' }}</span>
                </div>
                <h3 class="text-lg font-bold text-bps-navy">{{ $dda?->title ?? 'Kabupaten Jember Dalam Angka '.($activeYear ?? date('Y')) }}</h3>
                <p class="text-xs text-slate-600 mt-1">Buku induk kompilasi 13 bab statistik sektoral dan regional Kabupaten Jember (Format B5).</p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold
                    @if($dda?->status == 'FINAL_RELEASED') bg-emerald-100 text-emerald-800
                    @elseif($dda?->status == 'APPROVED_LOCKED') bg-purple-100 text-purple-800
                    @elseif($dda?->status == 'IN_EDITORIAL') bg-amber-100 text-amber-800
                    @else bg-slate-100 text-slate-800 @endif">
                    {{ $dda?->status ?? 'PENDING_DATA' }}
                </span>
                <a href="{{ route('compilation.index') }}" class="text-xs font-bold text-bps-blue hover:underline">
                    Kelola Kompilasi &rarr;
                </a>
            </div>
        </div>

        <!-- SKD Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded">SKD (Analisis)</span>
                    <span class="text-xs text-slate-500">ISSN: {{ $skd?->issn ?: 'Belum ada data' }}</span>
                </div>
                <h3 class="text-lg font-bold text-bps-navy">{{ $skd?->title ?? 'Analisis Hasil Survei Kebutuhan Data '.($activeYear ?? date('Y')) }}</h3>
                <p class="text-xs text-slate-600 mt-1">Laporan analitis kepuasan pengguna PST, Indeks IKK & IPAK, serta Diagram Kartesius IPA.</p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                {{-- INTEGRITAS ANGKA: nilai IKK/IPAK TIDAK PERNAH ditulis di sini.
                     Skor hanya tampil bila bersumber dari mesin SKD (cache JSON resmi);
                     selain itu sistem menampilkan status, bukan angka tebakan. --}}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold
                    @if($skd?->status == 'FINAL_RELEASED') bg-emerald-100 text-emerald-800
                    @elseif($skd?->status == 'APPROVED_LOCKED') bg-purple-100 text-purple-800
                    @else bg-blue-100 text-blue-800 @endif">
                    @if($skd)
                        IKK &amp; IPAK: Tersedia di Hasil Analisis
                    @else
                        IKK &amp; IPAK: Belum ada data
                    @endif
                </span>
                <a href="{{ route('skd.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Buka Hasil Analisis &rarr;
                </a>
            </div>
        </div>
    </div>

<!-- Executive Summary Penerbitan Tahun Berjalan -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-bps-orange"></span>
                    <h2 class="text-base font-bold text-bps-navy uppercase tracking-wide">Executive Summary &mdash; Penerbitan {{ $activeYear ?? date('Y') }}</h2>
                </div>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed max-w-3xl">
                    Sepanjang tahun terbit <strong>{{ $activeYear ?? date('Y') }}</strong>, SI-PENA mengorkestrasi
                    penerbitan <strong>31 Kecamatan Dalam Angka (KDA)</strong>, satu <strong>Kabupaten Jember Dalam Angka (DDA)</strong>,
                    dan <strong>Analisis Survei Kebutuhan Data (SKD)</strong>. Seluruh angka berasal langsung dari berkas OPD
                    yang diarsipkan berdasar hash SHA-256; sistem tidak pernah mengisi angka yang belum tersedia.
                </p>
            </div>
            <div class="shrink-0 grid grid-cols-2 gap-3 text-center">
                <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3 min-w-[110px]">
                    <p class="text-2xl font-black text-bps-navy">{{ array_sum($kdaStatusCounts) }}</p>
                    <p class="text-[10px] uppercase tracking-wide text-slate-500 font-bold">Total KDA</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 min-w-[110px]">
                    <p class="text-2xl font-black text-emerald-700">{{ $kdaStatusCounts['FINAL_RELEASED'] ?? 0 }}</p>
                    <p class="text-[10px] uppercase tracking-wide text-emerald-700 font-bold">KDA Rilis</p>
                </div>
            </div>
        </div>

        <!-- Bar progres penyelesaian 31 KDA per status bab -->
        @php
            $kdaTotal = array_sum($kdaStatusCounts) ?: 1;
            $progressSegments = [
                ['key' => 'FINAL_RELEASED', 'label' => 'Final Rilis', 'class' => 'bg-emerald-500'],
                ['key' => 'APPROVED_LOCKED', 'label' => 'Terkunci', 'class' => 'bg-purple-500'],
                ['key' => 'PENDING_APPROVAL', 'label' => 'Menunggu QC', 'class' => 'bg-blue-500'],
                ['key' => 'IN_EDITORIAL', 'label' => 'Redaksi', 'class' => 'bg-amber-500'],
                ['key' => 'DATA_INGESTED', 'label' => 'Teringesti', 'class' => 'bg-sky-500'],
                ['key' => 'PENDING_DATA', 'label' => 'Menunggu Data', 'class' => 'bg-slate-400'],
            ];
        @endphp
        <div class="mt-4">
            <div class="flex h-3 w-full rounded-full overflow-hidden bg-slate-100 border border-slate-200">
                @foreach($progressSegments as $seg)
                    @php $segCount = (int) ($kdaStatusCounts[$seg['key']] ?? 0); @endphp
                    @if($segCount > 0)
                        <div class="{{ $seg['class'] }}" style="width: {{ round($segCount * 100 / $kdaTotal, 2) }}%" title="{{ $seg['label'] }}: {{ $segCount }}"></div>
                    @endif
                @endforeach
            </div>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[10px] font-semibold text-slate-500">
                @foreach($progressSegments as $seg)
                    <span class="inline-flex items-center"><span class="w-2 h-2 rounded-full {{ $seg['class'] }} mr-1"></span>{{ $seg['label'] }} ({{ (int) ($kdaStatusCounts[$seg['key']] ?? 0) }})</span>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Kartu Highlight Wilayah (Kecamatan Terluas, Terbanyak Desa, Total Wilayah) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-2 text-bps-orange">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                <span class="text-[10px] font-black uppercase tracking-wide">Kecamatan Terluas</span>
            </div>
            @if($widestDistrict)
                <p class="text-lg font-black text-bps-navy mt-2">{{ $widestDistrict->name }}</p>
                <p class="text-sm text-slate-600">{{ number_format((float) $widestDistrict->total_area_sqkm, 2, ',', '.') }} km&sup2;</p>
                <p class="text-[11px] text-slate-400 mt-1">Ibukota: {{ $widestDistrict->capital_city }}</p>
            @else
                <p class="text-sm text-slate-400 mt-2 italic">Belum ada data wilayah.</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-2 text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0121 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="text-[10px] font-black uppercase tracking-wide">Kecamatan Terbanyak Desa</span>
            </div>
            @if($mostVillagesDistrict && (int) $mostVillagesDistrict->villages_count > 0)
                <p class="text-lg font-black text-bps-navy mt-2">{{ $mostVillagesDistrict->name }}</p>
                <p class="text-sm text-slate-600">{{ (int) $mostVillagesDistrict->villages_count }} desa/kelurahan</p>
                <p class="text-[11px] text-slate-400 mt-1">Struktur wilayah administratif terbanyak</p>
            @else
                <p class="text-sm text-slate-400 mt-2 italic">Belum ada data desa terpetakan.</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-2 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-[10px] font-black uppercase tracking-wide">Cakupan Wilayah</span>
            </div>
            @if($totalDistricts > 0)
                <p class="text-lg font-black text-bps-navy mt-2">{{ $totalDistricts }} Kecamatan</p>
                <p class="text-sm text-slate-600">Tercatat di basis wilayah BPS 3509</p>
                <p class="text-[11px] text-slate-400 mt-1">Standar Kode Wilayah Kerja Statistik BPS</p>
            @else
                <p class="text-sm text-slate-400 mt-2 italic">Belum ada data wilayah.</p>
            @endif
        </div>
    </div>

    <!-- Matriks Progres 31 Kecamatan (KDA) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-bps-navy">Matriks Progres 31 Kecamatan Dalam Angka (KDA)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau status penyusunan, verifikasi bab, dan rilis PDF per satuan kecamatan.</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('compilation.index') }}" class="inline-flex items-center px-3 py-1.5 bg-bps-orange hover:bg-bps-darkorange text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    Kompilasi Masal 31 KDA
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Kode BPS</th>
                        <th class="py-3 px-4">Nama Kecamatan</th>
                        <th class="py-3 px-4">Ibukota</th>
                        <th class="py-3 px-4">Luas Wilayah</th>
                        <th class="py-3 px-4 text-center">Status Bab</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($kdaPublications as $pub)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $pub->district?->bps_code }}</td>
                        <td class="py-3 px-4 font-bold text-bps-navy">{{ $pub->district?->name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $pub->district?->capital_city }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ number_format($pub->district?->total_area_sqkm, 2, ',', '.') }} km²</td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $badgeClass = match($pub->status) {
                                    'FINAL_RELEASED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                    'APPROVED_LOCKED' => 'bg-purple-100 text-purple-800 border-purple-300',
                                    'PENDING_APPROVAL' => 'bg-blue-100 text-blue-800 border-blue-300',
                                    'IN_EDITORIAL' => 'bg-amber-100 text-amber-800 border-amber-300',
                                    'DATA_INGESTED' => 'bg-sky-100 text-sky-800 border-sky-300',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeClass }}">
                                {{ $pub->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            <a href="{{ route('editorial.index', ['publication_id' => $pub->id]) }}" class="inline-flex items-center px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] rounded transition-colors" title="Sunting Ulasan">
                                Edit
                            </a>
                            <a href="{{ route('approval.show', $pub->id) }}" class="inline-flex items-center px-2 py-1 bg-bps-navy hover:bg-bps-darknavy text-white text-[11px] rounded transition-colors" title="Quality Control">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400">Belum ada data publikasi KDA. Jalankan seeder database.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Publikasi Unggulan (Thumbnail Cover DDA & SKD) + Siluet Vektor Wilayah -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Thumbnail Cover DDA -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col">
            <h3 class="text-sm font-bold text-bps-navy mb-3">Cover Publikasi Induk (DDA)</h3>
            <div class="mx-auto w-full max-w-[180px] aspect-[1/1.414] bg-bps-darknavy text-white rounded-lg shadow-lg overflow-hidden p-4 flex flex-col justify-between border border-slate-300">
                <div class="flex justify-between text-[7px] text-slate-300 font-semibold leading-tight">
                    <span>BADAN PUSAT<br>STATISTIK<br>KAB. JEMBER</span>
                    <span class="text-right">Katalog<br>{{ $dda?->catalog_number ?: 'Belum ada data' }}</span>
                </div>
                <div>
                    <div class="text-[11px] font-black uppercase leading-tight">{{ $dda?->title ?? 'Kabupaten Jember Dalam Angka' }}</div>
                    <div class="text-bps-orange font-extrabold text-[10px] mt-1">TAHUN {{ $dda?->year ?? ($activeYear ?? date('Y')) }}</div>
                    <div class="mt-2 h-1 w-10 bg-emerald-500 rounded"></div>
                </div>
                <div class="border-t border-white/20 pt-2 text-[7px] text-slate-400">
                    <span class="block">ISSN: {{ $dda?->issn ?? '—' }}</span>
                    <span class="block">Volume {{ $dda?->volume ?? '—' }}</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 text-center">13 bab komprehensif tingkat makro Kabupaten Jember.</p>
        </div>

        <!-- Thumbnail Cover SKD -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col">
            <h3 class="text-sm font-bold text-bps-navy mb-3">Cover Analisis (SKD)</h3>
            <div class="mx-auto w-full max-w-[180px] aspect-[1/1.414] bg-indigo-950 text-white rounded-lg shadow-lg overflow-hidden p-4 flex flex-col justify-between border border-slate-300 relative">
                <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 100 140" preserveAspectRatio="none"><circle cx="75" cy="40" r="22" fill="#E67E22"/><rect x="10" y="95" width="12" height="34" fill="#ffffff"/><rect x="26" y="82" width="12" height="47" fill="#ffffff"/><rect x="42" y="70" width="12" height="59" fill="#ffffff"/></svg>
                <div class="relative flex justify-between text-[7px] text-indigo-200 font-semibold leading-tight">
                    <span>BADAN PUSAT<br>STATISTIK<br>KAB. JEMBER</span>
                    <span class="text-right">ISSN<br>{{ $skd?->issn ?: 'Belum ada data' }}</span>
                </div>
                <div class="relative">
                    <div class="text-[11px] font-black uppercase leading-tight">{{ $skd?->title ?? 'Analisis Survei Kebutuhan Data' }}</div>
                    <div class="text-bps-orange font-extrabold text-[10px] mt-1">TAHUN {{ $skd?->year ?? ($activeYear ?? date('Y')) }}</div>
                    <div class="mt-2 h-1 w-10 bg-indigo-400 rounded"></div>
                </div>
                <div class="relative border-t border-white/20 pt-2 text-[7px] text-indigo-200">IKK &middot; IPAK &middot; Diagram Kartesius IPA</div>
            </div>
            <p class="text-[11px] text-slate-400 mt-3 text-center">Hasil analisis layanan PST dari berkas kuesioner VKD.</p>
        </div>

        <!-- Siluet Vektor Pembagian Wilayah 31 Kecamatan -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col">
            <h3 class="text-sm font-bold text-bps-navy mb-1">Pembagian Wilayah 31 Kecamatan</h3>
            <p class="text-[11px] text-slate-400 mb-3">Siluet skematik grid &mdash; bukan peta geografis resmi.</p>
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                <svg viewBox="0 0 310 150" class="w-full h-auto" role="img" aria-label="Siluet vektor 31 kecamatan Kabupaten Jember">
                    @php
                        $gridRows = [6, 7, 6, 6, 6]; // total 31 sel
                        $cell = 46; $gap = 4; $idx = 0;
                    @endphp
                    @foreach($gridRows as $rowIdx => $cols)
                        @for($colIdx = 0; $colIdx < $cols; $colIdx++)
                            @php
                                $x = $colIdx * ($cell + $gap) + 2;
                                $y = $rowIdx * 26 + 4;
                                $opacity = 0.25 + (($idx % 6) * 0.12);
                            @endphp
                            <rect x="{{ $x }}" y="{{ $y }}" width="{{ $cell }}" height="20" rx="4" fill="#0A3866" opacity="{{ number_format($opacity, 2) }}"/>
                            @php $idx++; @endphp
                        @endfor
                    @endforeach
                    <text x="2" y="148" font-size="9" fill="#64748B">{{ $totalDistricts }} kecamatan terdaftar &middot; Kode Wilayah 3509</text>
                </svg>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Setiap sel mewakili satu kecamatan; pemetaan kode BPS tersimpan di basis wilayah.</p>
        </div>
    </div>

    <!-- Pratinjau Grafik Python Engine: Piramida Penduduk & Iklim/Curah Hujan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Piramida Penduduk (Pratinjau)</h3>
                    <p class="text-[11px] text-slate-400">visualizers/population_pyramid.py</p>
                </div>
                <span class="text-[10px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">matplotlib SVG</span>
            </div>
            <div class="bg-slate-50 rounded-lg border border-slate-200 p-2 min-h-[220px] flex items-center justify-center overflow-hidden">
                @if($pyramidSvg)
                    <div class="w-full overflow-hidden">{!! file_get_contents(base_path($pyramidSvg)) !!}</div>
                @else
                    <p class="text-xs text-slate-400 text-center px-4">Grafik piramida penduduk belum dirender oleh Python Engine.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Ringkasan Iklim &amp; Curah Hujan (Pratinjau)</h3>
                    <p class="text-[11px] text-slate-400">visualizers/climate_chart.py</p>
                </div>
                <span class="text-[10px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">matplotlib SVG</span>
            </div>
            <div class="bg-slate-50 rounded-lg border border-slate-200 p-2 min-h-[220px] flex items-center justify-center overflow-hidden">
                @if($climateSvg)
                    <div class="w-full overflow-hidden">{!! file_get_contents(base_path($climateSvg)) !!}</div>
                @else
                    <p class="text-xs text-slate-400 text-center px-4">Grafik curah hujan bulanan belum dirender oleh Python Engine.</p>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
