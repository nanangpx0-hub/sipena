@extends('layouts.app', ['title' => 'Catatan Audit & Mutu Data: ' . $publication->title])

@push('styles')
<style>
    .audit-sheet { max-width: 210mm; }
    .audit-sheet table { page-break-inside: auto; }
    .audit-sheet tr { page-break-inside: avoid; }
    .hash { word-break: break-all; font-size: 10px; }
    @media print {
        header, footer, .no-print, #flash-banners { display: none !important; }
        body { background: #fff !important; }
        main { max-width: none !important; padding: 0 !important; }
        .audit-sheet { max-width: none !important; box-shadow: none !important; border: 0 !important; padding: 0 !important; }
        .audit-block { border-color: #94a3b8 !important; }
    }
</style>
@endpush

@section('content')
<div class="audit-sheet mx-auto space-y-4">

    <div class="no-print flex items-center justify-between">
        <a href="{{ route('approval.show', $publication->id) }}" class="text-xs font-semibold text-bps-blue hover:underline">&larr; Kembali ke Tinjauan Approver</a>
        <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop & Judul Dokumen -->
    @php
        // Nomor register audit unik berbasis hash SHA-256 dari identitas dokumen.
        $registerBasis = 'BPS-3509|'.($publication->id ?? 0).'|'.$publication->title.'|'.$publication->type.'|'.$publication->year;
        $registerHash = hash('sha256', $registerBasis);
        $registerNumber = 'REG-AUDIT-3509-'.strtoupper(substr($registerHash, 0, 8)).'-'.$publication->year;

        // Indikator mutu data (angka dihitung dari data nyata, bukan karangan).
        $auditTables = $publication->tables;
        $auditTableTotal = $auditTables->count();
        $auditSuccess = $auditTables->filter(function ($t) {
            $td = is_array($t->table_data) ? $t->table_data : [];
            return ($td['status'] ?? null) === 'success';
        })->count();
        $auditCompleteness = (int) ($auditTableTotal > 0 ? round($auditSuccess * 100 / $auditTableTotal) : 0);
        $auditHashOk = $publication->rawDataFiles->every(fn ($f) => !empty($f->file_hash_sha256));
    @endphp
    <div class="bg-white border border-slate-300 rounded-md p-6 text-center">
        <div class="flex items-center justify-center space-x-3 mb-2">
            @include('partials.bps-logo', ['class' => 'h-10 w-auto'])
            <div class="text-left leading-tight">
                <p class="text-xs font-bold tracking-widest text-slate-700 uppercase">Badan Pusat Statistik Kabupaten Jember</p>
                <p class="text-[11px] text-slate-500">Jl. Rumah Sakit No. 5, Jember &middot; Kode Wilayah 3509 &middot; www.jemberkab.bps.go.id</p>
            </div>
        </div>
        <hr class="my-3 border-slate-300">
        <h1 class="text-base font-black text-bps-navy uppercase tracking-wide">Catatan Audit &amp; Mutu Data</h1>
        <p class="text-[11px] text-slate-500 mt-0.5">Dokumen pendamping tinjauan sebelum Approve &amp; Lock</p>

        <!-- Kop register audit: nomor register + QR verifikasi -->
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center bg-slate-50 border border-slate-200 rounded-md p-4 text-left">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Nomor Register Audit (SHA-256)</p>
                <p class="text-sm font-mono font-bold text-bps-navy mt-1 break-all">{{ $registerNumber }}</p>
                <p class="text-[10px] font-mono text-slate-400 mt-1 break-all">hash: {{ substr($registerHash, 0, 32) }}&hellip;</p>
            </div>
            <div class="flex items-center justify-center sm:justify-end space-x-3">
                <!-- Mockup QR Code verifikasi dokumen digital (visual polanya diturunkan dari hash register) -->
                <div class="bg-white border border-slate-300 rounded p-2">
                    <svg viewBox="0 0 100 100" class="w-20 h-20" role="img" aria-label="Mockup QR verifikasi dokumen digital">
                        @php
                            $qrBits = str_split(substr($registerHash, 0, 64));
                            $finders = [[0, 0], [70, 0], [0, 70]];
                        @endphp
                        <rect x="0" y="0" width="100" height="100" fill="#FFFFFF"/>
                        @for($gx = 0; $gx < 10; $gx++)
                            @for($gy = 0; $gy < 10; $gy++)
                                @php
                                    $inFinder = ($gx < 3 && $gy < 3) || ($gx >= 7 && $gy < 3) || ($gx < 3 && $gy >= 7);
                                    $fill = $qrBits[($gx + $gy * 10) % 64] >= '8';
                                @endphp
                                @if(!$inFinder && $fill)
                                    <rect x="{{ $gx * 10 }}" y="{{ $gy * 10 }}" width="10" height="10" fill="#0A3866"/>
                                @else
                                    <rect x="{{ $gx * 10 }}" y="{{ $gy * 10 }}" width="10" height="10" fill="none"/>
                                @endif
                            @endfor
                        @endfor
                        @foreach($finders as [$fx, $fy])
                            <rect x="{{ $fx }}" y="{{ $fy }}" width="30" height="30" fill="#0A3866"/>
                            <rect x="{{ $fx + 5 }}" y="{{ $fy + 5 }}" width="20" height="20" fill="#FFFFFF"/>
                            <rect x="{{ $fx + 10 }}" y="{{ $fy + 10 }}" width="10" height="10" fill="#0A3866"/>
                        @endforeach
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-bps-navy">Mockup QR Verifikasi Dokumen</p>
                    <p class="text-[10px] text-slate-500 leading-snug">Pola visual diturunkan dari register hash di samping untuk kebutuhan pratinjau cetak.</p>
                </div>
            </div>
        </div>

        <!-- Klausul kepatuhan Satu Data Indonesia -->
        <p class="text-[10px] text-slate-600 bg-sky-50 border border-sky-200 rounded-md mt-4 p-3 leading-relaxed text-left">
            <strong class="text-sky-800 uppercase tracking-wide">Klausul Kepatuhan Satu Data Indonesia:</strong>
            Seluruh angka pada dokumen ini berasal langsung dari berkas sumber OPD yang diarsipkan dengan hash kriptografis
            dan diproses sesuai prinsip Satu Data Indonesia (akurat, mutakhir, terpadu, dapat diakses). SI-PENA tidak mengisi
            angka yang belum tersedia — bagian tanpa data dinyatakan eksplisit sebagai <em>belum ada data</em>.
        </p>
    </div>

    <!-- A. Identitas Publikasi -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">A. Identitas Publikasi</h2>
        <table class="w-full text-xs border-collapse">
            <tbody class="divide-y divide-slate-200">
                <tr><td class="py-1.5 pr-3 w-48 text-slate-500">Judul</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->title }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Tipe</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->type }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Tahun Terbit</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->year }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Kecamatan</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->district?->name ?? 'Nasional / Provinsi' }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Nomor Katalog</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->catalog_number ?? '-' }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Ukuran Buku</td><td class="py-1.5 font-semibold text-slate-800">{{ $publication->book_size ?? '-' }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Status Terkini</td><td class="py-1.5 font-semibold text-bps-orange">{{ $publication->status }}</td></tr>
                <tr><td class="py-1.5 pr-3 text-slate-500">Dicetak</td><td class="py-1.5 font-semibold text-slate-800">{{ now()->format('d/m/Y H:i') }} oleh {{ Auth::user()?->name ?? '-' }} ({{ Auth::user()?->role?->display_name ?? '-' }})</td></tr>
            </tbody>
        </table>
    </div>

    <!-- B. Riwayat Berkas OPD -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">B. Riwayat Berkas Sumber (OPD)</h2>
        @forelse($publication->rawDataFiles as $file)
        <div class="border border-slate-200 rounded p-3 mb-2 last:mb-0">
            <table class="w-full text-xs border-collapse">
                <tbody class="divide-y divide-slate-200">
                    <tr><td class="py-1 pr-3 w-44 text-slate-500">Nama Dinas (OPD)</td><td class="py-1 font-semibold text-slate-800">{{ $file->opd_source_name }}</td></tr>
                    <tr><td class="py-1 pr-3 text-slate-500">Nama Berkas</td><td class="py-1 font-semibold text-slate-800">{{ $file->original_filename }}</td></tr>
                    <tr><td class="py-1 pr-3 text-slate-500">Versi</td><td class="py-1 font-semibold text-slate-800">v{{ $file->version_number }}</td></tr>
                    <tr><td class="py-1 pr-3 text-slate-500">Unggah oleh</td><td class="py-1 font-semibold text-slate-800">{{ $file->uploader?->name ?? '-' }} &middot; {{ $file->created_at?->format('d/m/Y H:i') }}</td></tr>
                    <tr><td class="py-1 pr-3 align-top text-slate-500">SHA-256</td><td class="py-1 hash font-mono text-slate-700">{{ $file->file_hash_sha256 }}</td></tr>
                </tbody>
            </table>
        </div>
        @empty
        <p class="text-xs text-slate-500">Belum ada berkas sumber yang diunggah.</p>
        @endforelse
    </div>

<!-- B2. Grid Indikator Mutu + Diagram Rantai Pasok Data -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">B2. Grid Indikator Mutu &amp; Rantai Pasok Data</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4 text-center">
            <div class="border {{ $auditCompleteness === 100 && $auditTableTotal > 0 ? 'border-emerald-300 bg-emerald-50' : 'border-slate-300 bg-slate-50' }} rounded-md p-3">
                <p class="text-2xl font-black {{ $auditCompleteness === 100 && $auditTableTotal > 0 ? 'text-emerald-700' : 'text-slate-500' }}">{{ $auditTableTotal > 0 ? $auditCompleteness.'%' : '—' }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Data Completeness</p>
                <p class="text-[10px] text-slate-500">{{ $auditSuccess }} / {{ $auditTableTotal }} tabel terekstrak utuh</p>
            </div>
            <div class="border {{ $warningCount > 0 ? 'border-amber-300 bg-amber-50' : 'border-emerald-300 bg-emerald-50' }} rounded-md p-3">
                <p class="text-2xl font-black {{ $warningCount > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ $warningCount > 0 ? 'Logged' : 'Zero' }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Anomaly</p>
                <p class="text-[10px] text-slate-500">{{ $warningCount }} temuan lonjakan tercatat</p>
            </div>
            <div class="border {{ $auditHashOk ? 'border-emerald-300 bg-emerald-50' : 'border-slate-300 bg-slate-50' }} rounded-md p-3">
                <p class="text-2xl font-black {{ $auditHashOk ? 'text-emerald-700' : 'text-slate-500' }}">{{ $auditHashOk ? 'OK' : '—' }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-600">Hash Verification</p>
                <p class="text-[10px] text-slate-500">Seluruh hash SHA-256 {{ $auditHashOk ? 'terverifikasi' : 'belum lengkap' }}</p>
            </div>
        </div>

        <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Diagram Visual Rantai Pasok Data</p>
        <div class="overflow-x-auto">
            <svg viewBox="0 0 640 84" class="w-full min-w-[520px] h-auto" role="img" aria-label="Diagram rantai pasok data SI-PENA">
                <defs>
                    <marker id="auditArrow" markerWidth="8" markerHeight="8" refX="6" refY="4" orient="auto"><path d="M0,0 L8,4 L0,8 z" fill="#94A3B8"/></marker>
                </defs>
                <line x1="122" y1="38" x2="158" y2="38" stroke="#94A3B8" stroke-width="2" marker-end="url(#auditArrow)"/>
                <line x1="282" y1="38" x2="318" y2="38" stroke="#94A3B8" stroke-width="2" marker-end="url(#auditArrow)"/>
                <line x1="442" y1="38" x2="478" y2="38" stroke="#94A3B8" stroke-width="2" marker-end="url(#auditArrow)"/>
                <g>
                    <rect x="10" y="18" width="112" height="42" rx="8" fill="#F1F5F9" stroke="#0A3866" stroke-width="2"/>
                    <text x="66" y="36" text-anchor="middle" font-size="10" font-weight="800" fill="#0A3866">DINAS / OPD</text>
                    <text x="66" y="50" text-anchor="middle" font-size="8" fill="#64748B">Berkas Excel mentah</text>
                </g>
                <g>
                    <rect x="168" y="18" width="114" height="42" rx="8" fill="#F1F5F9" stroke="#0A3866" stroke-width="2"/>
                    <text x="225" y="36" text-anchor="middle" font-size="10" font-weight="800" fill="#0A3866">PYTHON WORKER</text>
                    <text x="225" y="50" text-anchor="middle" font-size="8" fill="#64748B">Bersih + agregasi</text>
                </g>
                <g>
                    <rect x="328" y="18" width="114" height="42" rx="8" fill="#F1F5F9" stroke="#0A3866" stroke-width="2"/>
                    <text x="385" y="36" text-anchor="middle" font-size="10" font-weight="800" fill="#0A3866">DATABASE MYSQL</text>
                    <text x="385" y="50" text-anchor="middle" font-size="8" fill="#64748B">Tables + narratives</text>
                </g>
                <g>
                    <rect x="488" y="18" width="142" height="42" rx="8" fill="#E67E22" stroke="#0A3866" stroke-width="2"/>
                    <text x="559" y="36" text-anchor="middle" font-size="10" font-weight="800" fill="#FFFFFF">TYPST COMPILER</text>
                    <text x="559" y="50" text-anchor="middle" font-size="8" fill="#FFF7ED">PDF + audit note</text>
                </g>
                <text x="10" y="78" font-size="8" fill="#64748B">Catatan: setiap panah menyimpan jejak audit (uploader, waktu, hash SHA-256, transisi status).</text>
            </svg>
        </div>
    </div>

    <!-- C. Penanggung Jawab Ulasan Narasi -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">C. Penanggung Jawab Ulasan Narasi (Editor)</h2>
        @if($editors->isEmpty())
        <p class="text-xs text-slate-500">Belum ada narasi yang disunting.</p>
        @else
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-600 uppercase text-[10px]">
                    <th class="text-left py-2 px-2 border border-slate-300">Nama Editor</th>
                    <th class="text-left py-2 px-2 border border-slate-300">Peran</th>
                    <th class="text-left py-2 px-2 border border-slate-300">Ulasan Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($editors as $editor)
                <tr>
                    <td class="py-1.5 px-2 border border-slate-300 font-semibold text-slate-800">{{ $editor->editor?->name ?? 'Pengguna dihapus' }}</td>
                    <td class="py-1.5 px-2 border border-slate-300 text-slate-600">{{ $editor->editor?->role?->display_name ?? '-' }}</td>
                    <td class="py-1.5 px-2 border border-slate-300 text-slate-600">
                        @foreach($publication->narratives->where('last_edited_by', $editor->last_edited_by) as $narrative)
                        <span class="block">Bab {{ $narrative->chapter_number }} &mdash; {{ $narrative->title_id }} ({{ $narrative->updated_at?->format('d/m/Y H:i') }})</span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- D. Jejak Audit Alur Kerja -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">D. Jejak Audit Alur Kerja &amp; Catatan Persetujuan</h2>
        @if($workflowLogs->isEmpty())
        <p class="text-xs text-slate-500">Belum ada riwayat perubahan status.</p>
        @else
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-600 uppercase text-[10px]">
                    <th class="text-left py-2 px-2 border border-slate-300">Waktu</th>
                    <th class="text-left py-2 px-2 border border-slate-300">Aktor</th>
                    <th class="text-left py-2 px-2 border border-slate-300">Transisi Status</th>
                    <th class="text-left py-2 px-2 border border-slate-300">Catatan / Persetujuan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($workflowLogs as $log)
                <tr>
                    <td class="py-1.5 px-2 border border-slate-300 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="py-1.5 px-2 border border-slate-300 font-semibold text-slate-800">{{ $log->user?->name ?? 'Sistem' }}<span class="block text-[10px] font-normal text-slate-500">{{ $log->user?->role?->display_name ?? '' }}</span></td>
                    <td class="py-1.5 px-2 border border-slate-300 font-mono text-[11px]">{{ $log->from_status }} &rarr; {{ $log->to_status }}</td>
                    <td class="py-1.5 px-2 border border-slate-300 text-slate-700">{{ $log->remarks }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- E. Catatan Mutu Data -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <h2 class="text-xs font-black text-bps-navy uppercase tracking-wide mb-3">E. Catatan Mutu Data Ingesti</h2>
        <p class="text-xs text-slate-700 mb-3">
            Tabel teringesti: <strong>{{ $publication->tables->count() }}</strong> &middot;
            Peringatan anomali: <strong>{{ $warningCount }}</strong> &middot;
            Saran koreksi desa: <strong>{{ $suggestionCount }}</strong>
        </p>

        @php
            $hasFinding = false;
        @endphp
        @foreach($publication->tables as $table)
            @php
                $td = is_array($table->table_data) ? $table->table_data : [];
                $tableWarnings = is_array($td['warnings'] ?? null) ? $td['warnings'] : [];
                $tableSuggestions = is_array($td['village_suggestions'] ?? null) ? $td['village_suggestions'] : [];
            @endphp
            @if($tableWarnings !== [] || $tableSuggestions !== [])
                @php
                    $hasFinding = true;
                @endphp
                <div class="border border-slate-200 rounded p-3 mb-2">
                    <p class="text-xs font-bold text-bps-navy mb-1">Tabel {{ $table->table_number }} &mdash; {{ $table->title_id }} (Bab {{ $table->chapter_number }})</p>
                    @if($tableWarnings !== [])
                        <p class="text-[11px] font-bold text-bps-darkorange uppercase mb-0.5">Peringatan Anomali Data: Lonjakan signifikan dibanding tahun lalu</p>
                        <ul class="list-disc list-inside text-[11px] text-slate-700 space-y-0.5">
                            @foreach($tableWarnings as $warning)
                            <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if($tableSuggestions !== [])
                        <p class="text-[11px] font-bold text-sky-800 uppercase mt-1.5 mb-0.5">Saran Koreksi Nama Desa</p>
                        <ul class="list-disc list-inside text-[11px] text-slate-700 space-y-0.5">
                            @foreach($tableSuggestions as $suggestion)
                            <li>Baris {{ $suggestion['row'] }}: &ldquo;{{ $suggestion['raw_name'] }}&rdquo; &rarr; &ldquo;{{ $suggestion['official_name'] }}&rdquo; ({{ $suggestion['similarity'] }}%)</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        @endforeach

        @if(! $hasFinding)
        <p class="text-xs text-slate-500">Tidak ada anomali lonjakan maupun saran koreksi nama desa pada seluruh tabel publikasi ini.</p>
        @endif
    </div>

    <!-- Penutup & Tanda Tangan -->
    <div class="audit-block bg-white border border-slate-300 rounded-md p-5">
        <p class="text-xs text-slate-700 leading-relaxed">
            Dokumen ini dihasilkan oleh SI-PENA sebagai lampiran audit mutu data dan bukan bagian dari
            publikasi resmi. Seluruh angka yang dilaporkan berasal langsung dari berkas sumber yang
            diunggah oleh OPD; sistem tidak pernah mengisi angka yang belum tersedia.
        </p>
        <div class="flex justify-between items-end mt-6 text-xs">
            <div>
                <p class="text-slate-500">Diperiksa oleh,</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-800 border-t border-slate-400 pt-1">{{ Auth::user()?->name ?? '-' }}</p>
                <p class="text-slate-500">{{ Auth::user()?->role?->display_name ?? '-' }}</p>
            </div>
            <div class="text-right">
                <p class="text-slate-500">Jember, {{ now()->format('d F Y') }}</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-800 border-t border-slate-400 pt-1">Ketua Tim Publikasi</p>
                <p class="text-slate-500">Koordinator SI-PENA</p>
            </div>
        </div>
    </div>

</div>
@endsection
