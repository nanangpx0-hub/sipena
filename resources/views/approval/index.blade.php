@extends('layouts.app', ['title' => 'Quality Control & Approval - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Fase 3: Meja Kerja Quality Control & Approval</h1>
            <p class="text-xs text-slate-500 mt-1">
                Wewenang khusus Ketua Tim / Koordinator Publikasi untuk memvalidasi konsistensi tabel, mengunci bab publikasi (State Locking), atau menolak revisi.
            </p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                Hak Akses: Admin / Koordinator
            </span>
        </div>
    </div>
<!-- Protokol Verifikasi Koordinator Publikasi -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Protokol Verifikasi Koordinator Publikasi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Alur baku penelaahan mutu sebelum sebuah publikasi dikunci secara permanen (State Locking).</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                SOP QC: 4 Gerbang
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="w-6 h-6 rounded-full bg-bps-navy text-white text-[11px] font-black flex items-center justify-center">1</span>
                    <p class="text-xs font-bold text-bps-navy">Periksa Tabel vs Berkas OPD</p>
                </div>
                <p class="text-[11px] text-slate-600 leading-snug">Cocokkan total angka tabel teringesti dengan berkas mentah asli; telusuri peringatan anomali data (lonjakan tahunan tak wajar).</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="w-6 h-6 rounded-full bg-bps-navy text-white text-[11px] font-black flex items-center justify-center">2</span>
                    <p class="text-xs font-bold text-bps-navy">Keabsahan Nama Desa</p>
                </div>
                <p class="text-[11px] text-slate-600 leading-snug">Tinjau saran koreksi nama desa hasil pencocokan fuzzy terhadap kode wilayah BPS; nama nonbaku wajib dikoreksi sebelum kunci.</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="w-6 h-6 rounded-full bg-bps-navy text-white text-[11px] font-black flex items-center justify-center">3</span>
                    <p class="text-xs font-bold text-bps-navy">Integritas Narasi &amp; Bilingual</p>
                </div>
                <p class="text-[11px] text-slate-600 leading-snug">Pastikan setiap bab memiliki ulasan bilingual 100&ndash;300 kata; angka indikator kunci pembatas bab harus konsisten dengan tabel.</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center space-x-2 mb-1.5">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-[11px] font-black flex items-center justify-center">4</span>
                    <p class="text-xs font-bold text-bps-navy">Kunci Status (State Locking)</p>
                </div>
                <p class="text-[11px] text-slate-600 leading-snug">Tandai Approve &amp; Lock hanya bila keempat gerbang lolos. Status terkunci bersifat permanen sampai dibuka kembali oleh Approver.</p>
            </div>
        </div>
    </div>

    <!-- Checklist Integritas Data sebelum State Locking -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="{ locks: { t: false, n: false, s: false, q: false } }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Checklist Integritas Data sebelum State Locking</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tandai setiap butir setelah diverifikasi pada tinjauan detail Approver.</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-full border"
                  :class="Object.values(locks).every(Boolean) ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200'"
                  x-text="Object.values(locks).filter(Boolean).length + ' / 4 terverifikasi'"></span>
        </div>
        @php
            $lockChecklist = [
                't' => 'Kesesuaian Tabel vs Berkas OPD: seluruh tabel memiliki padanan berkas sumber dan tidak ada peringatan anomali yang belum ditinjau.',
                'n' => 'Koreksi Nama Desa: semua saran koreksi nama desa telah ditindaklanjuti atau dicatat sebagai anomali resmi.',
                's' => 'Ulasan Narasi: tiap bab memiliki narasi Indonesia &amp; Inggris lengkap sesuai kaidah penulisan angka BPS.',
                'q' => 'Jejak Audit &amp; Catatan Mutu: riwayat workflow lengkap dan catatan audit &amp; mutu data siap diarsipkan.',
            ];
        @endphp
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach($lockChecklist as $key => $item)
            <li class="flex items-start space-x-3 text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-lg p-3">
                <input type="checkbox" x-model="locks.{{ $key }}" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                <span>{!! $item !!}</span>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Publications List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-bps-navy">Daftar Publikasi Menunggu Persetujuan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau kelengkapan narasi dan tabel data sebelum dikunci permanen untuk kompilasi PDF.</p>
        </div>
<!-- Horizontal pipeline tracker: draft menuju locked -->
        @php
            $pipeStages = [
                ['key' => 'PENDING_DATA', 'label' => 'Draft', 'class' => 'bg-slate-400', 'text' => 'text-slate-700'],
                ['key' => 'DATA_INGESTED', 'label' => 'Ingesti', 'class' => 'bg-sky-500', 'text' => 'text-sky-800'],
                ['key' => 'IN_EDITORIAL', 'label' => 'Editorial', 'class' => 'bg-amber-500', 'text' => 'text-amber-800'],
                ['key' => 'PENDING_APPROVAL', 'label' => 'QC', 'class' => 'bg-blue-500', 'text' => 'text-blue-800'],
                ['key' => 'APPROVED_LOCKED', 'label' => 'Locked', 'class' => 'bg-purple-500', 'text' => 'text-purple-800'],
                ['key' => 'FINAL_RELEASED', 'label' => 'Rilis', 'class' => 'bg-emerald-500', 'text' => 'text-emerald-800'],
                ['key' => 'REVISION_REQUIRED', 'label' => 'Revisi', 'class' => 'bg-rose-500', 'text' => 'text-rose-800'],
            ];
            $pipeCounts = $publications->getCollection()->groupBy('status')->map->count()->toArray();
            $pipeTotal = (int) $publications->getCollection()->count();
        @endphp
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/70">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                    Pipeline Status &mdash; Draf Menuju Terkunci
                </p>
                <p class="text-[10px] text-slate-400">
                    Cakupan: <strong class="text-slate-600">{{ $pipeTotal }}</strong> publikasi pada halaman ini
                    ({{ $publications->total() }} total di seluruh filter)
                </p>
            </div>

            @if($pipeTotal > 0)
                {{-- Rel batang proporsional: menandai posisi tiap tahap pada alur. --}}
                <div class="flex h-2.5 w-full rounded-full overflow-hidden bg-slate-200 mb-3" role="img"
                     aria-label="Distribusi status publikasi pada halaman ini">
                    @foreach($pipeStages as $stage)
                        @php $stageCount = (int) ($pipeCounts[$stage['key']] ?? 0); @endphp
                        @if($stageCount > 0)
                            <div class="{{ $stage['class'] }} h-full" style="width: {{ round($stageCount * 100 / $pipeTotal, 2) }}%;"
                                 title="{{ $stage['label'] }}: {{ $stageCount }} publikasi"></div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="h-2.5 w-full rounded-full bg-slate-200 mb-3"></div>
            @endif

            <div class="flex items-center overflow-x-auto pb-1">
                @foreach($pipeStages as $stage)
                    @php $stageCount = (int) ($pipeCounts[$stage['key']] ?? 0); @endphp
                    <div class="flex items-center shrink-0">
                        <div class="flex items-center space-x-2 px-3 py-2 rounded-lg border {{ $stageCount > 0 ? 'border-slate-300 bg-white shadow-sm' : 'border-slate-200 bg-slate-100 opacity-60' }}">
                            <span class="w-2.5 h-2.5 rounded-full {{ $stage['class'] }}"></span>
                            <span class="text-[11px] font-bold text-slate-700">{{ $stage['label'] }}</span>
                            <span class="text-[11px] font-mono font-bold {{ $stageCount > 0 ? $stage['text'] : 'text-slate-400' }}">{{ $stageCount }}</span>
                        </div>
                        @if(!$loop->last)
                            <div class="w-6 h-0.5 bg-slate-300 mx-1 shrink-0"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <p class="text-[10px] text-slate-400 mt-2">
                Status <strong>Revisi</strong> (REVISION_REQUIRED) ditampilkan pada tahap akhir
                karena merupakan pengembalian dari QC, bukan urutan maju alur.
            </p>
        </div>


        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Judul Publikasi</th>
                        <th class="py-3 px-4">Wilayah / Satker</th>
                        <th class="py-3 px-4 text-center">Tabel Terdata</th>
                        <th class="py-3 px-4 text-center">Bab Terisi</th>
                        <th class="py-3 px-4 text-center">Status Alur Kerja</th>
                        <th class="py-3 px-4 text-right">Aksi Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($publications as $pub)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pub->type == 'KDA' ? 'bg-blue-100 text-blue-800' : ($pub->type == 'DDA' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $pub->type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-bps-navy">{{ $pub->title }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $pub->district?->name ?? 'BPS Jember' }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $pub->tables->count() }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $pub->narratives->count() }}</td>
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
                            @php
                                // Stempel digital status QC sesuai peta status alur kerja.
                                $qcStamp = match($pub->status) {
                                    'FINAL_RELEASED', 'APPROVED_LOCKED' => ['QC Passed', 'bg-emerald-600', 'M9 12l2 2 4-4m7.835-4.057a11.959 11.959 0 01-8.644 3.729 11.959 11.959 0 01-8.644-3.729A11.954 11.954 0 012.5 12c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                                    'REVISION_REQUIRED' => ['Rejected', 'bg-rose-600', 'M6 18L18 6M6 6l12 12'],
                                    'IN_EDITORIAL' => ['Pending QC', 'bg-amber-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                    'DATA_INGESTED', 'PENDING_DATA' => ['Pending', 'bg-slate-500', 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                                    default => ['QC Review', 'bg-sky-600', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wide text-white {{ $qcStamp[1] }} mt-1" title="Stempel digital status QC BPS 3509">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="{{ $qcStamp[2] }}"/></svg>
                                {{ $qcStamp[0] }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('approval.show', $pub->id) }}" class="inline-flex items-center px-3 py-1.5 bg-bps-navy hover:bg-bps-darknavy text-white text-[11px] font-bold rounded-lg shadow-sm transition-colors">
                                Review & Putuskan &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada publikasi yang terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $publications->links() }}
        </div>
    </div>

</div>
@endsection
