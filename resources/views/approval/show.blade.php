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
            </div>
            @empty
            <p class="text-xs text-slate-400">Belum ada tabel teringesti untuk publikasi ini.</p>
            @endforelse
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
