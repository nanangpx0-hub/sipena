@extends('layouts.app', ['title' => 'Ingesti Data OPD - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Fase 1: Ingesti & Pembersihan Berkas Mentah OPD</h1>
            <p class="text-xs text-slate-500 mt-1">
                Unggah berkas Excel kotor dari dinas/OPD. Engine Python akan membersihkan merge cells, menormalkan desimal, dan mengagregasi data sekolah atau VKD.
            </p>
        </div>
        <div class="text-right">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-bps-navy border border-blue-200">
                Audit Trail: Versioning SHA-256 Aktif
            </span>
        </div>
    </div>

    <!-- Upload Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-base font-bold text-bps-navy mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-bps-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Formulir Unggah Berkas Excel Mentah
        </h2>

        <form action="{{ route('ingestion.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Target Publikasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Publikasi <span class="text-rose-500">*</span></label>
                    <select name="publication_id" required class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50">
                        <option value="">-- Pilih Buku Publikasi --</option>
                        @foreach($publications as $pub)
                            <option value="{{ $pub->id }}">{{ $pub->title }} [{{ $pub->type }} - {{ $pub->year }}]</option>
                        @endforeach
                    </select>
                </div>

                <!-- Instansi Sumber (OPD) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Instansi / OPD Sumber <span class="text-rose-500">*</span></label>
                    <input type="text" name="opd_source_name" required placeholder="Contoh: Dinas Pendidikan, Dispendukcapil" class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50">
                </div>

                <!-- Mode Pemrosesan Python -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mode Ekstraksi Data <span class="text-rose-500">*</span></label>
                    <select name="data_mode" required class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50">
                        <option value="DIRECT">Pembersih Excel Standar (Direct Table)</option>
                        <option value="AGGREGATE_SCHOOL">Agregasi Dapodik/EMIS (Tingkat Sekolah)</option>
                        <option value="SKD_VKD">Kuesioner VKD (Survei Kebutuhan Data)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Nomor Bab -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Bab Terkait <span class="text-rose-500">*</span></label>
                    <select name="chapter_number" required class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50">
                        <option value="1">Bab 1: Geografi dan Iklim</option>
                        <option value="2">Bab 2: Pemerintahan</option>
                        <option value="3">Bab 3: Penduduk</option>
                        <option value="4" selected>Bab 4: Sosial dan Kesejahteraan Rakyat</option>
                        <option value="5">Bab 5: Pertanian</option>
                        <option value="6">Bab 6: Pariwisata & Transportasi</option>
                        <option value="7">Bab 7: Perbankan & Koperasi</option>
                    </select>
                </div>

                <!-- Nomor Tabel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Identitas / Nomor Tabel (Opsional)</label>
                    <input type="text" name="table_number" placeholder="Contoh: 4.1.1" class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50">
                </div>

                <!-- File Excel -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Berkas Excel Mentah (.xlsx, .xls, .csv) <span class="text-rose-500">*</span></label>
                    <input type="file" name="excel_file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-bps-navy file:text-white hover:file:bg-bps-darknavy cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan / Keterangan Sumber Data</label>
                <textarea name="notes" rows="2" placeholder="Catatan mengenai versi data, kontak narahubung OPD, atau kondisi anomali data..." class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-2.5 bg-slate-50"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Unggah & Ekstraksi Python
                </button>
            </div>
        </form>
    </div>

    <!-- Raw Files Audit History Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-bps-navy">Riwayat Berkas Mentah OPD & Audit Versioning</h2>
            <p class="text-xs text-slate-500 mt-0.5">Seluruh berkas asli diarsipkan secara permanen berdasarkan kalkulasi hash SHA-256 untuk mencegah sengketa revisi angka.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Waktu & Versi</th>
                        <th class="py-3 px-4">Instansi OPD</th>
                        <th class="py-3 px-4">Target Publikasi</th>
                        <th class="py-3 px-4">Nama Berkas Asli</th>
                        <th class="py-3 px-4">Checksum SHA-256</th>
                        <th class="py-3 px-4">Pengunggah</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($rawFiles as $file)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4">
                            <span class="font-bold text-bps-navy">v{{ $file->version_number }}</span>
                            <span class="text-slate-400 block text-[10px]">{{ $file->created_at->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-700">{{ $file->opd_source_name }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $file->publication?->title }}</td>
                        <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">{{ $file->original_filename }}</td>
                        <td class="py-3 px-4 font-mono text-[10px] text-slate-400" title="{{ $file->file_hash_sha256 }}">
                            {{ substr($file->file_hash_sha256, 0, 16) }}...
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $file->uploader?->name ?? 'Operator' }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $file->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            Belum ada berkas OPD yang diunggah. Silakan unggah berkas Excel melalui formulir di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
