@extends('layouts.app', ['title' => 'Kompilasi PDF & Batch Queue - SI-PENA'])

@section('content')
<div class="space-y-6"
     x-data="{
        pendingJobs: {{ (int) $pendingJobs }},
        failedJobs: {{ (int) $failedJobs }},
        approvedKda: {{ (int) $approvedKda }},
        releasedKda: {{ (int) $releasedKda }},
        totalKda: {{ (int) $totalKda }},
        busy: false,
        highlightStatus: null,
        queue: @js($queueSnapshot ?? []),
        stateLabel(state) {
            return state === 'processing' ? 'Sedang dikompilasi' : 'Menunggu worker';
        },
        sinceLabel(iso) {
            if (!iso) return '-';
            // Tabel jobs Laravel menyimpan waktu dalam UTC tanpa zona waktu.
            const started = new Date(iso.replace(' ', 'T') + 'Z');
            if (isNaN(started.getTime())) return '-';
            const seconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
            if (seconds < 60) return seconds + ' detik lalu';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + ' menit lalu';
            return Math.floor(minutes / 60) + ' jam lalu';
        },
        releasedPercent() {
            return this.totalKda > 0 ? Math.round((this.releasedKda / this.totalKda) * 100) : 0;
        },
        refreshQueue() {
            if (this.busy) return;
            this.busy = true;
            fetch('{{ route('compilation.queue-status') }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then((response) => response.ok ? response.json() : null)
                .then((data) => {
                    if (!data) return;
                    this.pendingJobs = data.pendingJobs;
                    this.failedJobs = data.failedJobs;
                    this.approvedKda = data.approvedKda;
                    this.releasedKda = data.releasedKda;
                    this.totalKda = data.totalKda;
                    if (Array.isArray(data.queue)) this.queue = data.queue;
                })
                .finally(() => { this.busy = false; });
        },
        init() {
            this.refreshQueue();
            setInterval(() => this.refreshQueue(), 4000);
        }
     }">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Fase 4: Orkestrasi Kompilasi Typst & Antrean Masal (31 KDA)</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kompilasi dokumen PDF berstandar percetakan (300 DPI) menggunakan mesin Typst CLI standalone tanpa beban overhead Node atau DOMPDF.
            </p>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                {{-- Ikon cetak resolusi tinggi (vektor inline, tanpa CDN). --}}
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full border border-slate-200 bg-slate-50 text-slate-600" title="Ikon cetak vektor 24x24 px, diskalakan tanpa kehilangan ketajaman">
                    <svg class="w-3.5 h-3.5 text-bps-navy" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Ikon Cetak Vektor
                </span>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700" title="PDF/A-1b dengan font Roboto tertanam, siap dicetak pada 300 DPI">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    PDF/A-1b &middot; Font Embed
                </span>
            </div>
        </div>
        <form action="{{ route('compilation.batch-all') }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Mulai dispatch 31 antrean kompilasi KDA ke server database queue?')" class="inline-flex items-center px-5 py-2.5 bg-bps-orange hover:bg-bps-darkorange text-white text-xs font-bold rounded-lg shadow-md transition-all transform hover:-translate-y-0.5">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Kompilasi Seluruh 31 KDA (Batch Queue)
            </button>
        </form>
    </div>
<!-- Kartu Performa Typst + Spesifikasi Percetakan + Ilustrasi Compiler -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Detail performa rendering Typst (kecepatan milidetik per halaman) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-2 mb-2">
                <span class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Performa Rendering Typst</h3>
                    <p class="text-[11px] text-slate-400">Kecepatan milidetik per halaman (terukur)</p>
                </div>
            </div>
            @if(!empty($typstPerf) && ($typstPerf['duration_ms'] ?? null) !== null)
            <div class="grid grid-cols-3 gap-2 text-center my-3">
                <div class="bg-slate-50 border border-slate-200 rounded-lg py-2">
                    <p class="text-lg font-black text-bps-navy">{{ number_format((float) $typstPerf['duration_ms'], 0, ',', '.') }}</p>
                    <p class="text-[10px] text-slate-500 uppercase">ms render</p>
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-lg py-2">
                    <p class="text-lg font-black text-bps-navy">{{ $typstPerf['pages'] ?? '—' }}</p>
                    <p class="text-[10px] text-slate-500 uppercase">halaman PDF</p>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg py-2">
                    <p class="text-lg font-black text-emerald-700">{{ $typstPerf['ms_per_page'] !== null ? number_format((float) $typstPerf['ms_per_page'], 1, ',', '.') : '—' }}</p>
                    <p class="text-[10px] text-emerald-700 uppercase">ms / halaman</p>
                </div>
            </div>
            <p class="text-[11px] text-slate-500">Terakhir: <strong class="text-slate-700">{{ $typstPerf['title'] ?? '—' }}</strong> &middot; {{ $typstPerf['when'] ?? '—' }}.</p>
            @else
            <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada pengukuran performa. Jalankan satu kompilasi instan untuk menampilkan kecepatan render Typst.</p>
            @endif
        </div>

        <!-- Kartu spesifikasi percetakan -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center space-x-2 mb-3">
                <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-bps-navy">Spesifikasi Percetakan BPS</h3>
                    <p class="text-[11px] text-slate-400">Standar keluaran mesin Typst</p>
                </div>
            </div>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-600">Resolusi render grafis</span>
                    <span class="font-mono font-bold text-bps-navy">300 DPI</span>
                </div>
                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-600">Format arsip</span>
                    <span class="font-mono font-bold text-bps-navy">PDF/A 1.7</span>
                </div>
                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                    <span class="font-semibold text-slate-600">Font embed</span>
                    <span class="font-mono font-bold text-bps-navy">Roboto</span>
                </div>
            </div>
        </div>

        <!-- Ilustrasi Typst High-Speed Compiler -->
        <div class="bg-gradient-to-br from-bps-navy to-bps-darknavy text-white rounded-xl shadow-sm border border-slate-200 p-5 relative overflow-hidden">
            <svg class="absolute -right-8 -bottom-8 w-44 h-44 opacity-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <h3 class="text-sm font-bold mb-1">Typst High-Speed Compiler</h3>
            <p class="text-[11px] text-slate-300 leading-relaxed">Markup <code class="text-amber-300">storage/temp/*.typ</code> dikompilasi menjadi PDF via satu biner <code class="text-amber-300">typst_engine/bin/typst.exe</code> &mdash; tanpa server Node, tanpa DOMPDF/FPDF.</p>
            <div class="mt-4 space-y-1.5 font-mono text-[11px] bg-black/30 rounded-lg p-3 text-emerald-300 overflow-x-auto">
                <p><span class="text-slate-500">$</span> typst compile buku.typ output.pdf</p>
                <p><span class="text-slate-500">$</span> --font-path typst_engine/fonts</p>
            </div>
        </div>
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
            <div class="text-2xl font-bold text-purple-700 mt-1" x-text="approvedKda + ' Buku'">{{ $approvedKda }} Buku</div>
            <div class="text-[11px] text-purple-600 mt-1">Siap Eksekusi Typst</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Selesai (Released PDF)</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1" x-text="releasedKda + ' Buku'">{{ $releasedKda }} Buku</div>
            <div class="text-[11px] text-emerald-600 mt-1" x-text="releasedPercent() + '% Progres Rilis'">{{ round(($releasedKda / max($totalKda, 1)) * 100, 1) }}% Progres Rilis</div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase">Antrean Queue Aktif</span>
            <div class="text-2xl font-bold text-amber-600 mt-1" x-text="pendingJobs + ' Job'">{{ $pendingJobs }} Job</div>
            <div class="text-[11px] text-rose-600 font-semibold mt-1" x-show="failedJobs > 0" x-cloak>
                <span x-text="failedJobs + ' job GAGAL (lihat tabel failed_jobs)'">{{ $failedJobs }} job GAGAL (lihat tabel failed_jobs)</span>
            </div>
            <div class="text-[11px] text-emerald-600 mt-1" x-show="failedJobs <= 0">
                <span x-text="'0 job gagal \u00b7 worker: ' + (pendingJobs > 0 ? 'aktif memproses' : 'siaga')">0 job gagal &middot; worker: {{ $pendingJobs > 0 ? 'aktif memproses' : 'siaga' }}</span>
            </div>
        </div>
    </div>

    <!-- Kartu Job Antrean Real-Time -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-bps-navy">Antrean Job Kompilasi &mdash; Real-Time</h3>
                <p class="text-[11px] text-slate-400">
                    Kartu job yang sedang diproses worker Typst dan yang masih mengantre,
                    disegarkan otomatis setiap 4 detik.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 text-slate-600">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span x-text="queue.length + ' job ditampilkan'">{{ count($queueSnapshot ?? []) }} job ditampilkan</span>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
            <template x-for="job in queue" :key="job.id">
                <div class="border rounded-lg p-3 relative overflow-hidden"
                     :class="job.state === 'processing' ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200 bg-slate-50'">
                    {{-- Progress bergerak pada job yang sedang diproses. --}}
                    <template x-if="job.state === 'processing'">
                        <div class="absolute inset-x-0 top-0 h-0.5 bg-emerald-200 overflow-hidden">
                            <div class="h-full w-1/3 bg-emerald-500 animate-pulse"></div>
                        </div>
                    </template>

                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-slate-800 truncate" x-text="job.label">-</p>
                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">
                                Antrean: <span x-text="job.queue">-</span>
                                &middot; Percobaan: <span x-text="job.attempts">0</span>
                            </p>
                        </div>
                        <span class="shrink-0 text-[9px] font-black uppercase px-1.5 py-0.5 rounded"
                              :class="job.state === 'processing' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'"
                              x-text="stateLabel(job.state)">-</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-2">
                        Mulai: <span x-text="sinceLabel(job.since)">-</span>
                    </p>
                </div>
            </template>
        </div>

        <div x-show="queue.length === 0" x-cloak class="border-2 border-dashed border-slate-200 rounded-lg py-8 text-center">
            <svg class="w-8 h-8 mx-auto text-slate-300 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-xs font-bold text-slate-500">Antrean kompilasi kosong</p>
            <p class="text-[10px] text-slate-400 mt-1">
                Belum ada job di tabel antrean. Gunakan tombol batch di atas untuk mengantre 31 KDA terkunci.
            </p>
        </div>
    </div>

    <!-- Antrean Tertunda (menyembunyikan dirinya sendiri saat antrean kosong) -->
    <div x-show="pendingJobs > 0" x-cloak class="bg-amber-50 border-l-4 border-amber-500 rounded-r-md p-4 text-sm text-amber-800 flex items-start space-x-3">
        <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
        <div>
            <strong x-text="pendingJobs + ' job masih menunggu worker.'">{{ $pendingJobs }} job masih menunggu worker.</strong>
            Pastikan proses <code class="bg-amber-100 px-1 rounded">php artisan queue:work</code> berjalan
            (jalankan <code class="bg-amber-100 px-1 rounded">start-queue-worker.bat</code> atau layanan NSSM).
            Tanpa worker, tombol batch hanya mengantre tanpa memproduksi PDF.
        </div>
    </div>

    <!-- Realtime Progress Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm space-y-2">
        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
            <span>Progres Penyelesaian PDF Publikasi Daerah</span>
            <span x-text="releasedKda + ' dari ' + totalKda + ' KDA Rilis (' + releasedPercent() + '%)'">{{ $releasedKda }} dari {{ $totalKda }} KDA Rilis ({{ round(($releasedKda / max($totalKda, 1)) * 100) }}%)</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-bps-blue to-emerald-500 h-3 rounded-full transition-all duration-500" style="width: {{ round(($releasedKda / max($totalKda, 1)) * 100) }}%" :style="'width: ' + releasedPercent() + '%'"></div>
        </div>
    </div>
<!-- Bar Multi-Status 31 KDA Interaktif -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-bps-navy">Visual Multi-Status 31 KDA</h3>
                <p class="text-[11px] text-slate-400">Segmen interaktif; arahkan kursor untuk melihat jumlah per status.</p>
            </div>
            <span class="text-[11px] font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-600">Alpine live</span>
        </div>
        @php
            $compSegments = [
                ['key' => 'FINAL_RELEASED', 'label' => 'Rilis PDF', 'class' => 'bg-emerald-500'],
                ['key' => 'APPROVED_LOCKED', 'label' => 'Terkunci', 'class' => 'bg-purple-500'],
                ['key' => 'PENDING_APPROVAL', 'label' => 'Menunggu QC', 'class' => 'bg-blue-500'],
                ['key' => 'IN_EDITORIAL', 'label' => 'Redaksi', 'class' => 'bg-amber-500'],
                ['key' => 'DATA_INGESTED', 'label' => 'Teringesti', 'class' => 'bg-sky-500'],
                ['key' => 'PENDING_DATA', 'label' => 'Menunggu Data', 'class' => 'bg-slate-400'],
            ];
            $compTotal = max(array_sum($kdaStatusCounts ?? []), 1);
        @endphp
        <div class="flex h-5 w-full rounded-full overflow-hidden bg-slate-100 border border-slate-200">
            @foreach($compSegments as $seg)
                @php $segCount = (int) ($kdaStatusCounts[$seg['key']] ?? 0); @endphp
                @if($segCount > 0)
                    {{-- Segmen dapat diklik untuk menyorot (state Alpine highlightStatus)
                         sehingga benar-benar interaktif, bukan sekadar atribut title. --}}
                    <button type="button"
                            class="{{ $seg['class'] }} h-5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white/80"
                            style="width: {{ round($segCount * 100 / $compTotal, 2) }}%"
                            title="{{ $seg['label'] }}: {{ $segCount }} buku"
                            x-on:click="highlightStatus = (highlightStatus === @js($seg['key']) ? null : @js($seg['key']))"
                            x-bind:class="highlightStatus !== null && highlightStatus !== @js($seg['key']) ? 'opacity-30' : ''"
                            x-bind:aria-pressed="highlightStatus === @js($seg['key']) ? 'true' : 'false'">
                    </button>
                @endif
            @endforeach
        </div>
        <p class="text-[10px] text-slate-400">
            Klik sebuah segmen untuk menyorot status tersebut; klik lagi untuk membatalkan.
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px]">
            @foreach($compSegments as $seg)
            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5">
                <span class="inline-flex items-center font-semibold text-slate-600">
                    <span class="w-2 h-2 rounded-full {{ $seg['class'] }} mr-1.5"></span>{{ $seg['label'] }}
                </span>
                <span class="font-mono font-bold text-bps-navy">{{ (int) ($kdaStatusCounts[$seg['key']] ?? 0) }}</span>
            </div>
            @endforeach
        </div>
    </div>


<!-- Pie Chart Status Rilis + Console Drawer Log Worker -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm" x-data="{ openLog: false }">
        <div class="flex flex-col sm:flex-row items-center gap-4">
            @php
                $pieCirc = 2 * M_PI * 52;
                $pieOffset = 0.0;
                $pieColors = ['FINAL_RELEASED' => '#10B981', 'APPROVED_LOCKED' => '#9333EA', 'PENDING_APPROVAL' => '#3B82F6', 'IN_EDITORIAL' => '#F59E0B', 'DATA_INGESTED' => '#0EA5E9', 'PENDING_DATA' => '#94A3B8'];
            @endphp
            <svg viewBox="0 0 140 140" class="w-36 h-36 shrink-0" role="img" aria-label="Pie chart status rilis 31 KDA">
                <g transform="rotate(-90 70 70)">
                    @foreach($compSegments as $seg)
                        @php
                            $segCount = (int) ($kdaStatusCounts[$seg['key']] ?? 0);
                            $frac = $segCount / $compTotal;
                            $dash = $frac * $pieCirc;
                        @endphp
                        @if($segCount > 0)
                        <circle cx="70" cy="70" r="52" fill="none" stroke="{{ $pieColors[$seg['key']] }}" stroke-width="22"
                                stroke-dasharray="{{ number_format($dash, 2, '.', '') }} {{ number_format($pieCirc - $dash, 2, '.', '') }}"
                                stroke-dashoffset="{{ number_format(-$pieOffset, 2, '.', '') }}"/>
                        @endif
                        @php $pieOffset += $dash; @endphp
                    @endforeach
                </g>
                <text x="70" y="66" text-anchor="middle" font-size="22" font-weight="900" fill="#0A3866">{{ $compTotal }}</text>
                <text x="70" y="82" text-anchor="middle" font-size="9" fill="#64748B">BUKU KDA</text>
            </svg>
            <div class="w-full">
                <h3 class="text-sm font-bold text-bps-navy">Pie Chart Status Rilis</h3>
                <p class="text-[11px] text-slate-400 mb-2">Proporsi 31 KDA menurut status alur kerja &mdash; diperbarui realtime.</p>
                <button type="button" @click="openLog = !openLog" class="inline-flex items-center px-3 py-1.5 bg-slate-950 hover:bg-slate-800 text-slate-200 text-[11px] font-bold rounded-lg transition-colors font-mono">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span x-text="openLog ? 'Tutup Console' : 'Buka Console Log Worker'"></span>
                </button>
            </div>
        </div>

        <!-- Console drawer riwayat log worker -->
        <div x-show="openLog" x-cloak class="mt-4 bg-slate-950 text-slate-200 rounded-lg border border-slate-800 overflow-hidden">
            <div class="px-4 py-2 border-b border-white/10 flex items-center justify-between">
                <span class="text-[10px] font-mono font-bold text-emerald-400">worker-console &mdash; {{ count($workerLogTail ?? []) }} baris terakhir</span>
                <span class="text-[10px] font-mono text-slate-500">storage/logs/laravel.log</span>
            </div>
            <div class="p-4 space-y-1 font-mono text-[11px] leading-relaxed max-h-52 overflow-y-auto">
                @forelse($workerLogTail ?? [] as $logLine)
                    <p class="break-all {{ str_contains($logLine, 'ERROR') || str_contains($logLine, 'error') ? 'text-rose-400' : (str_contains($logLine, 'queue') || str_contains($logLine, 'Queue') ? 'text-sky-300' : 'text-slate-400') }}">{{ $logLine }}</p>
                @empty
                    <p class="text-slate-500 italic">Belum ada riwayat log worker yang terbaca.</p>
                @endforelse
            </div>
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
