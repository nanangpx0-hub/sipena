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
                    <span class="text-xs text-slate-500">Katalog: {{ $dda?->catalog_number ?? '1102001.3509' }}</span>
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
                    <span class="text-xs text-slate-500">ISSN: {{ $skd?->issn ?? '2548-8120' }}</span>
                </div>
                <h3 class="text-lg font-bold text-bps-navy">{{ $skd?->title ?? 'Analisis Hasil Survei Kebutuhan Data '.($activeYear ?? date('Y')) }}</h3>
                <p class="text-xs text-slate-600 mt-1">Laporan analitis kepuasan pengguna PST, Indeks IKK & IPAK, serta Diagram Kartesius IPA.</p>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold
                    @if($skd?->status == 'FINAL_RELEASED') bg-emerald-100 text-emerald-800
                    @elseif($skd?->status == 'APPROVED_LOCKED') bg-purple-100 text-purple-800
                    @else bg-blue-100 text-blue-800 @endif">
                    IKK: 90.96 (Sangat Baik)
                </span>
                <a href="{{ route('skd.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Buka Hasil Analisis &rarr;
                </a>
            </div>
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

</div>
@endsection
