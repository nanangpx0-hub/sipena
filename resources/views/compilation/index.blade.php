@extends('layouts.app', ['title' => 'Kompilasi PDF & Batch Queue - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Fase 4: Orkestrasi Kompilasi Typst & Antrean Masal (31 KDA)</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kompilasi dokumen PDF berstandar percetakan (300 DPI) menggunakan mesin Typst CLI standalone tanpa beban overhead Node atau DOMPDF.
            </p>
        </div>
        <form action="{{ route('compilation.batch-all') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Mulai dispatch 31 antrean kompilasi KDA ke server database queue?')" class="inline-flex items-center px-5 py-2.5 bg-bps-orange hover:bg-bps-darkorange text-white text-xs font-bold rounded-lg shadow-md transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Kompilasi Seluruh 31 KDA (Batch Queue)
            </button>
        </form>
    </div>

    <!-- Realtime Progress Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Target KDA</span>
            <div class="text-2xl font-bold text-bps-navy mt-1">31 Kecamatan</div>
            <div class="text-[11px] text-slate-400 mt-1">Publikasi A5 Resmi BPS</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Terkunci (Approved)</span>
            <div class="text-2xl font-bold text-purple-700 mt-1">{{ $approvedKda }} Buku</div>
            <div class="text-[11px] text-purple-600 mt-1">Siap Eksekusi Typst</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Selesai (Released PDF)</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $releasedKda }} Buku</div>
            <div class="text-[11px] text-emerald-600 mt-1">{{ round(($releasedKda / max($totalKda, 1)) * 100, 1) }}% Progres Rilis</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Antrean Queue Aktif</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingJobs }} Job</div>
            @if(($failedJobs ?? 0) > 0)
                <div class="text-[11px] text-rose-600 font-semibold mt-1">{{ $failedJobs }} job GAGAL (lihat tabel failed_jobs)</div>
            @else
                <div class="text-[11px] text-emerald-600 mt-1">0 job gagal &middot; worker: {{ $pendingJobs > 0 ? 'aktif memproses' : 'siaga' }}</div>
            @endif
        </div>
    </div>

    @if($pendingJobs > 0)
    <!-- Antrean Tertunda -->
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-md p-4 text-sm text-amber-800 flex items-start space-x-3">
        <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
        <div>
            <strong>{{ $pendingJobs }} job masih menunggu worker.</strong>
            Pastikan proses <code class="bg-amber-100 px-1 rounded">php artisan queue:work</code> berjalan
            (jalankan <code class="bg-amber-100 px-1 rounded">start-queue-worker.bat</code> atau layanan NSSM).
            Tanpa worker, tombol batch hanya mengantre tanpa memproduksi PDF.
        </div>
    </div>
    @endif

    <!-- Realtime Progress Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
            <span>Progres Penyelesaian PDF Publikasi Daerah</span>
            <span>{{ $releasedKda }} dari {{ $totalKda }} KDA Rilis ({{ round(($releasedKda / max($totalKda, 1)) * 100) }}%)</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-bps-blue to-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ round(($releasedKda / max($totalKda, 1)) * 100) }}%"></div>
        </div>
    </div>

    <!-- Publications Compilation Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-bps-navy">Daftar Status Kompilasi Dokumen Typst</h2>
            <p class="text-xs text-slate-500 mt-0.5">Eksekusi kompilasi secara langsung (sync) untuk draf preview atau gunakan dispatch antrean background.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Judul Publikasi</th>
                        <th class="py-3 px-4">Ukuran Buku</th>
                        <th class="py-3 px-4 text-center">Status Terkini</th>
                        <th class="py-3 px-4 text-right">Aksi Kompilasi Typst</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($publications as $pub)
                    @php
                        $safeTitle = \Illuminate\Support\Str::slug($pub->title);
                        $pdfPath = storage_path('output_pdf' . DIRECTORY_SEPARATOR . $pub->year . DIRECTORY_SEPARATOR . "{$safeTitle}.pdf");
                        $pdfExists = file_exists($pdfPath);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $pub->type == 'KDA' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $pub->type }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-bold text-bps-navy">
                            {{ $pub->title }}
                            @if($pdfExists)
                                <span class="text-[10px] text-emerald-600 font-normal block">PDF Siap ({{ round(filesize($pdfPath)/1024, 1) }} KB)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-mono">{{ $pub->book_size }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold
                                @if($pub->status == 'FINAL_RELEASED') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($pub->status == 'APPROVED_LOCKED') bg-purple-100 text-purple-800 border border-purple-300
                                @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                {{ $pub->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            @if($pdfExists)
                                <a href="{{ route('compilation.download', $pub->id) }}" class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold rounded-lg shadow-sm transition-colors">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Unduh PDF
                                </a>
                            @endif

                            <form action="{{ route('compilation.compile', $pub->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="sync" value="1">
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-bps-navy hover:bg-bps-darknavy text-white text-[11px] font-bold rounded-lg shadow-sm transition-colors" title="Kompilasi langsung sekarang">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Kompilasi Instan
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
