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
                Hak Akses: Approver / Koordinator
            </span>
        </div>
    </div>

    <!-- Publications List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-bps-navy">Daftar Publikasi Menunggu Persetujuan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau kelengkapan narasi dan tabel data sebelum dikunci permanen untuk kompilasi PDF.</p>
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
                        <th class="py-3 px-4 text-right">Aksi Approver</th>
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
