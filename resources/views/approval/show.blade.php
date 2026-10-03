@extends('layouts.app', ['title' => 'Tinjauan Approver: ' . $publication->title])

@section('content')
<div class="space-y-6" x-data="{ showRejectModal: false }">

    <!-- Top Breadcrumb & Action Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('approval.index') }}" class="text-xs font-semibold text-bps-blue hover:underline flex items-center">
                &larr; Kembali ke Meja Kerja
            </a>
            <h1 class="text-xl font-bold text-bps-navy mt-1">{{ $publication->title }}</h1>
            <div class="flex items-center space-x-3 text-xs text-slate-500 mt-1">
                <span>Tipe: <strong class="text-slate-800">{{ $publication->type }}</strong></span>
                <span>•</span>
                <span>Katalog: <strong class="text-slate-800">{{ $publication->catalog_number ?? '-' }}</strong></span>
                <span>•</span>
                <span>Status Saat Ini: <strong class="text-bps-orange">{{ $publication->status }}</strong></span>
            </div>
        </div>

        <!-- Approver Action Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('approval.audit-note', $publication->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-lg border border-slate-200 shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Catatan Audit &amp; Mutu Data
            </a>

            <button type="button" @click="showRejectModal = true" class="inline-flex items-center px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg border border-rose-200 shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Kembalikan / Tolak (Reject)
            </button>

            <form action="{{ route('approval.approve', $publication->id) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda yakin data bab dan angka statistik telah valid dan siap dikunci (APPROVED_LOCKED)?')" class="inline-flex items-center px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Approve & Lock
                </button>
            </form>
        </div>
    </div>

<!-- Kartu Checklist Evaluasi Mutu + Watermark Status -->
    @php
        $tablesCol = $publication->tables;
        $totalTables = $tablesCol->count();
        $verifiedTables = $tablesCol->where('is_verified', true)->count();
        $opdMatch = $publication->rawDataFiles->count() > 0 && $totalTables > 0;
        $villageIssueCount = $tablesCol->sum(function ($t) {
            $td = is_array($t->table_data) ? $t->table_data : [];
            return is_array($td['village_suggestions'] ?? null) ? count($td['village_suggestions']) : 0;
        });
        $narrativeFilled = $publication->narratives->filter(fn ($n) => trim((string) $n->narrative_id) !== '' && trim((string) $n->narrative_en) !== '')->count();
        $narrativeTotal = max($publication->narratives->count(), 1);
        $stampCfg = match($publication->status) {
            'FINAL_RELEASED', 'APPROVED_LOCKED' => ['text' => 'Disetujui BPS 3509', 'class' => 'text-emerald-700 border-emerald-600', 'qc' => 'QC Passed'],
            'REVISION_REQUIRED' => ['text' => 'Dikembalikan Revisi', 'class' => 'text-rose-700 border-rose-600', 'qc' => 'Rejected'],
            default => ['text' => 'Draf — Belum Sah', 'class' => 'text-slate-500 border-slate-400', 'qc' => 'Pending'],
        };
    @endphp
    <div class="relative bg-white rounded-xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        <!-- Watermark visual status -->
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
            <span class="rotate-[-18deg] text-4xl md:text-6xl font-black uppercase tracking-widest opacity-[0.08] {{ $stampCfg['class'] }}">{{ $stampCfg['text'] }}</span>
        </div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Checklist Evaluasi Mutu Approver</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tiga gerbang penilaian sebelum keputusan Approve &amp; Lock.</p>
            </div>
            <!-- Stempel digital status BPS 3509 -->
            <div class="{{ $stampCfg['class'] }} border-4 rounded-xl px-5 py-2 rotate-3 text-center shadow-sm bg-white/80">
                <p class="text-[11px] font-black uppercase tracking-widest">BPS 3509</p>
                <p class="text-sm font-black uppercase">{{ $stampCfg['text'] }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wide {{ $stampCfg['qc'] === 'QC Passed' ? 'text-emerald-700' : ($stampCfg['qc'] === 'Rejected' ? 'text-rose-700' : 'text-amber-700') }} mt-0.5">Stempel {{ $stampCfg['qc'] }}</p>
            </div>
        </div>

        <div class="relative grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="rounded-lg border {{ $opdMatch ? 'border-emerald-200 bg-emerald-50/60' : 'border-rose-200 bg-rose-50/60' }} p-4">
                <p class="font-bold text-slate-800 mb-1">1. Kesesuaian Tabel vs Berkas OPD</p>
                @if($opdMatch)
                    <p class="text-emerald-800 font-semibold">{{ $totalTables }} tabel &times; {{ $publication->rawDataFiles->count() }} berkas OPD memiliki pasangan sumber.</p>
                @else
                    <p class="text-rose-800 font-semibold">Belum ada pasangan tabel &amp; berkas OPD yang lengkap pada publikasi ini.</p>
                @endif
            </div>
            <div class="rounded-lg border {{ $villageIssueCount === 0 ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60' }} p-4">
                <p class="font-bold text-slate-800 mb-1">2. Keabsahan Nama Desa</p>
                @if($villageIssueCount === 0)
                    <p class="text-emerald-800 font-semibold">Tidak ada saran koreksi nama desa yang menggantung.</p>
                @else
                    <p class="text-amber-800 font-semibold">{{ $villageIssueCount }} saran koreksi nama desa perlu ditindaklanjuti pada daftar tabel di bawah.</p>
                @endif
            </div>
            <div class="rounded-lg border {{ $narrativeFilled === $narrativeTotal && $narrativeTotal > 0 ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60' }} p-4">
                <p class="font-bold text-slate-800 mb-1">3. Integritas Ulasan Narasi</p>
                <p class="{{ $narrativeFilled === $narrativeTotal && $narrativeTotal > 0 ? 'text-emerald-800' : 'text-amber-800' }} font-semibold">
                    {{ $narrativeFilled }} dari {{ $narrativeTotal }} bab memiliki ulasan bilingual lengkap.
                </p>
            </div>
        </div>
    </div>

    <!-- Chapter Narratives Review Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-bold text-bps-navy mb-4">Ulasan Narasi Bab Publikasi</h2>
        <div class="space-y-4">
            @foreach($publication->narratives as $nar)
            <div class="border border-slate-200 rounded-lg p-4 bg-slate-50/50">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-md bg-bps-navy text-white text-xs font-bold flex items-center justify-center">
                            {{ $nar->chapter_number }}
                        </span>
                        <h3 class="font-bold text-xs text-bps-navy">{{ $nar->title_id }} / <span class="italic text-slate-500 font-normal">{{ $nar->title_en }}</span></h3>
                    </div>
                    @if($nar->highlight_value)
                    <span class="text-xs bg-orange-100 text-bps-darkorange px-2 py-0.5 rounded font-bold">
                        {{ $nar->highlight_label }}: {{ $nar->highlight_value }}
                    </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs mt-3">
                    <div class="bg-white p-3 rounded border border-slate-200">
                        <strong class="text-slate-600 block text-[10px] uppercase mb-1">Narasi Bahasa Indonesia:</strong>
                        <p class="text-slate-800 leading-relaxed">{{ $nar->narrative_id }}</p>
                    </div>
                    <div class="bg-white p-3 rounded border border-slate-200">
                        <strong class="text-slate-400 block text-[10px] uppercase mb-1">English Translation:</strong>
                        <p class="text-slate-600 italic leading-relaxed">{{ $nar->narrative_en }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Ingested Table Data Preview: angka asli untuk verifikasi Approver -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-bold text-bps-navy mb-1">Pratinjau Data Tabel (Hasil Ingesti)</h2>
        <p class="text-xs text-slate-500 mb-4">Angka di bawah diambil langsung dari berkas OPD yang diunggah (bukan angka sistem) dan wajib dicocokkan sebelum Approve &amp; Lock.</p>

        <div class="space-y-6">
            @forelse($publication->tables as $table)
            @php
                $td = is_array($table->table_data) ? $table->table_data : [];
                $tdHeaders = array_values(array_filter((array) ($td['headers'] ?? []), 'is_scalar'));
                $tdRows = is_array($td['data'] ?? null) ? $td['data'] : [];
            @endphp
            <div id="table-preview-{{ $table->id }}" class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="flex items-center justify-between bg-slate-50 px-4 py-2 border-b border-slate-200">
                    <div class="text-xs font-bold text-bps-navy">
                        Tabel {{ $table->table_number }} &mdash; {{ $table->title_id }}
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-[10px] text-slate-500">
                            Bab {{ $table->chapter_number }} &middot;
                            @if(($td['status'] ?? 'error') === 'success')
                                <span class="text-emerald-700 font-bold">Data sah dari {{ $table->source_agency }}</span>
                            @else
                                <span class="text-rose-700 font-bold">Ekstraksi gagal</span>
                            @endif
                            &middot;
                            @if($table->is_verified)
                                <span class="text-emerald-700 font-bold">Terverifikasi</span>
                            @else
                                <span class="text-amber-700 font-bold">Belum diverifikasi</span>
                            @endif
                        </div>
                        <a href="{{ route('tables.export-csv', $table->id) }}" class="inline-flex items-center px-2.5 py-1 bg-white border border-slate-300 hover:border-bps-blue hover:text-bps-blue text-slate-600 text-[10px] font-bold rounded shadow-sm transition-colors" title="Unduh tabel ini sebagai berkas CSV">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh CSV
                        </a>
                    </div>
                </div>

                @if($tdHeaders !== [] && $tdRows !== [])
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-slate-200">
                        <thead class="bg-bps-navy text-white">
                            <tr>
                                @foreach($tdHeaders as $th)
                                <th class="px-3 py-2 text-left font-semibold whitespace-nowrap">{{ $th }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach(array_slice($tdRows, 0, 30) as $row)
                            <tr class="hover:bg-orange-50/40">
                                @foreach($tdHeaders as $th)
                                <td class="px-3 py-1.5 whitespace-nowrap text-slate-700">
                                    {{ is_array($row) ? ($row[$th] ?? '-') : '-' }}
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($tdRows) > 30)
                <div class="bg-slate-50 px-4 py-1.5 text-[10px] text-slate-500">
                    Menampilkan 30 dari {{ count($tdRows) }} baris; sisa baris tetap ikut tercetak pada PDF.
                </div>
                @endif
                @else
                <div class="px-4 py-3 text-xs text-slate-500">Berkas tidak menghasilkan baris data yang sah.</div>
                @endif

                @if(!empty($td['warnings']))
                <div class="border-t border-orange-200 bg-orange-50 px-4 py-3">
                    <p class="text-[11px] font-bold text-bps-darkorange uppercase tracking-wide mb-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Peringatan Anomali Data: Lonjakan signifikan dibanding tahun lalu
                    </p>
                    <ul class="text-[11px] text-orange-800 space-y-0.5 list-disc list-inside">
                        @foreach($td['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($td['village_suggestions']))
                <div class="border-t border-sky-200 bg-sky-50 px-4 py-3">
                    <p class="text-[11px] font-bold text-sky-800 uppercase tracking-wide mb-1.5">Saran Koreksi Nama Desa (Fuzzy Matching)</p>
                    <ul class="text-[11px] text-sky-900 space-y-0.5">
                        @foreach($td['village_suggestions'] as $suggestion)
                        <li>
                            Baris {{ $suggestion['row'] }}: &ldquo;{{ $suggestion['raw_name'] }}&rdquo; &rarr; &ldquo;{{ $suggestion['official_name'] }}&rdquo;
                            ({{ $suggestion['similarity'] }}% &middot; {{ $suggestion['reason'] }})
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @empty
            <p class="text-xs text-slate-400">Belum ada tabel teringesti untuk publikasi ini.</p>
            @endforelse
        </div>
    </div>

<!-- Mini-chart Anomali + Donat Kelengkapan Verifikasi Tabel -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Mini-chart visualisasi anomali data (lonjakan tahunan tak wajar) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Anomali Lonjakan Angka Tahunan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jumlah peringatan deteksi lonjakan per tabel (dihitung mesin anomali).</p>
                </div>
                <span class="text-[11px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">AnomalyDetectionService</span>
            </div>
            @php
                $anomalyBars = $tablesCol->map(function ($t) {
                    $td = is_array($t->table_data) ? $t->table_data : [];
                    return ['label' => (string) ($t->table_number ?: $t->id), 'count' => is_array($td['warnings'] ?? null) ? count($td['warnings']) : 0];
                })->values()->all();
                $anomalyMax = max(1, ...array_map(fn ($b) => $b['count'], $anomalyBars !== [] ? $anomalyBars : [['count' => 0]]));
            @endphp
            @if($tablesCol->isNotEmpty())
            <div class="space-y-2">
                @foreach($anomalyBars as $bar)
                @php $barPct = $bar['count'] > 0 ? round($bar['count'] * 100 / $anomalyMax, 1) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-[11px] mb-1">
                        <span class="font-mono font-bold text-slate-700">Tabel {{ $bar['label'] }}</span>
                        <span class="font-mono {{ $bar['count'] > 0 ? 'text-bps-darkorange font-bold' : 'text-slate-400' }}">{{ $bar['count'] }} peringatan</span>
                    </div>
                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $bar['count'] > 0 ? 'bg-bps-orange' : 'bg-slate-200' }}" style="width: {{ $barPct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-[11px] text-slate-500 mt-3">Lonjakan dijelaskan pada catatan audit sebagai temuan anomali; tanpa peringatan berarti selisih tahunan dalam batas wajar.</p>
            @else
            <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada tabel untuk divisualisasikan.</p>
            @endif
        </div>

        <!-- Grafik donat kelengkapan verifikasi tabel -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Kelengkapan Verifikasi Tabel</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Proporsi tabel terverifikasi dari seluruh tabel teringesti.</p>
                </div>
            </div>
            @php
                $verTotal = max($totalTables, 1);
                $verPct = (int) round($verifiedTables * 100 / $verTotal);
                $verCirc = 2 * M_PI * 52;
                $verDash = ($verPct / 100) * $verCirc;
            @endphp
            <div class="flex flex-col sm:flex-row items-center gap-5">
                <svg viewBox="0 0 140 140" class="w-40 h-40 shrink-0" role="img" aria-label="Donut kelengkapan verifikasi tabel">
                    <circle cx="70" cy="70" r="52" fill="none" stroke="#E2E8F0" stroke-width="20"/>
                    <g transform="rotate(-90 70 70)">
                        <circle cx="70" cy="70" r="52" fill="none" stroke="#16A085" stroke-width="20" stroke-linecap="round"
                                stroke-dasharray="{{ number_format($verDash, 2, '.', '') }} {{ number_format($verCirc - $verDash, 2, '.', '') }}"/>
                    </g>
                    <text x="70" y="66" text-anchor="middle" font-size="24" font-weight="900" fill="#0A3866">{{ $verPct }}%</text>
                    <text x="70" y="82" text-anchor="middle" font-size="9" fill="#64748B">TERVERIFIKASI</text>
                </svg>
                <div class="space-y-2 text-xs w-full">
                    <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                        <span class="font-semibold text-emerald-800">Terverifikasi</span>
                        <span class="font-mono font-bold text-emerald-800">{{ $verifiedTables }} tabel</span>
                    </div>
                    <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        <span class="font-semibold text-amber-800">Belum diverifikasi</span>
                        <span class="font-mono font-bold text-amber-800">{{ $totalTables - $verifiedTables }} tabel</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <span class="font-semibold text-slate-700">Total tabel</span>
                        <span class="font-mono font-bold text-slate-700">{{ $totalTables }} tabel</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Audit Logs -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-bold text-bps-navy mb-4">Jejak Audit Alur Kerja (Workflow Audit Trail)</h2>
        <div class="space-y-3">
            @forelse($publication->workflowLogs as $log)
            <div class="flex items-start space-x-3 text-xs border-l-2 border-bps-orange pl-3 py-1">
                <div class="text-slate-400 font-mono text-[11px] w-28 flex-shrink-0">
                    {{ $log->created_at->format('d/m/Y H:i') }}
                </div>
                <div>
                    <span class="font-bold text-slate-700">{{ $log->user?->name ?? 'Sistem' }}</span>:
                    <span class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] font-bold">{{ $log->from_status }} &rarr; {{ $log->to_status }}</span>
                    <p class="text-slate-600 mt-0.5">{{ $log->remarks }}</p>
                </div>
            </div>
            @empty
            <p class="text-xs text-slate-400">Belum ada riwayat perubahan status.</p>
            @endforelse
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showRejectModal = false">
            <h3 class="text-base font-bold text-rose-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Kembalikan Draf untuk Revisi
            </h3>
            <p class="text-xs text-slate-600">
                Berikan catatan revisi detail untuk operator atau editor agar perbaikan dapat dilakukan secara terarah.
            </p>

            <form action="{{ route('approval.reject', $publication->id) }}" method="POST" class="space-y-3">
                @csrf
                <textarea name="remarks" rows="4" required placeholder="Jelaskan alasan penolakan dan bagian angka/narasi yang perlu direvisi..." class="w-full text-xs rounded-lg border-slate-300 focus:border-rose-500 focus:ring focus:ring-rose-500/20 p-2.5 bg-slate-50"></textarea>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg shadow-sm">
                        Kirim Catatan & Kembalikan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
