@extends('layouts.app', ['title' => 'Analisis Hasil Survei Kebutuhan Data (SKD) - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Publikasi Analisis Tahunan</span>
            <h1 class="text-xl font-bold text-bps-navy mt-1">Analisis Hasil Survei Kebutuhan Data (SKD) 2026</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kalkulasi matriks kuesioner VKD (Blok I, II, III), Indeks Kepuasan Konsumen (IKK), Indeks Persepsi Anti Korupsi (IPAK), dan Diagram Kartesius IPA.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('compilation.index') }}" class="inline-flex items-center px-4 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm">
                Kompilasi PDF SKD &rarr;
            </a>
        </div>
    </div>

    <!-- Integritas Data: tanpa angka tebakan -->
    @unless($metrics)
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-md p-4 flex items-start space-x-3">
        <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div class="text-sm text-amber-800">
            <strong>Belum ada hasil SKD yang sah.</strong>
            Sistem sengaja tidak menampilkan angka prediksi/hitungan sistem.
            <span class="block mt-1 text-xs">{{ $errorMessage ?? 'Unggah berkas kuesioner VKD (.xlsx) terlebih dahulu untuk memperoleh IKK, IPAK, dan diagram kartesius.' }}</span>
        </div>
    </div>
    @else
    <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded-r-md p-3 flex items-center justify-between gap-3">
        <div class="text-xs text-emerald-800 font-medium">
            Sumber perhitungan:
            <strong>{{ ($metrics['data_source'] ?? 'file') === 'sample' ? 'DATA CONTOH (bukan survei resmi)' : 'berkas kuesioner VKD yang diunggah' }}</strong>
            &middot; diperbarui {{ date('d M Y H:i') }}
        </div>
        @if(($metrics['data_source'] ?? '') === 'sample')
            <span class="text-[10px] font-bold uppercase bg-rose-600 text-white px-2 py-0.5 rounded">Bukan Angka Resmi</span>
        @endif
    </div>
    @endunless

    <!-- Upload VKD Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Unggah Berkas Kuesioner Mentah VKD Baru</h2>
        <form action="{{ route('skd.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3">
            @csrf
            <input type="file" name="vkd_file" required accept=".xlsx,.xls,.csv" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
            <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm whitespace-nowrap">
                Proses & Update Metrik
            </button>
        </form>
    </div>

    <!-- Headline Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- IKK Card -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
            <span class="text-xs font-bold text-blue-700 uppercase">Indeks Kepuasan Konsumen (IKK)</span>
            <div class="text-4xl font-black text-blue-900 mt-2">{{ $metrics['ikk_score'] ?? '—' }}</div>
            <div class="text-xs font-semibold text-blue-800 mt-1">Mutu: {{ $metrics['mutu_pelayanan'] ?? 'Belum ada data' }}</div>
            <p class="text-[11px] text-slate-500 mt-2">Sesuai Permenpan RB No. 14 Tahun 2017 (Skala 100).</p>
        </div>

        <!-- IPAK Card -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-6 shadow-sm">
            <span class="text-xs font-bold text-emerald-700 uppercase">Indeks Persepsi Anti Korupsi (IPAK)</span>
            <div class="text-4xl font-black text-emerald-900 mt-2">{{ $metrics['ipak_score'] ?? '—' }}</div>
            <div class="text-xs font-semibold text-emerald-800 mt-1">{{ $metrics ? 'Predikat: Sangat Bersih dari Pungli & Korupsi' : 'Belum ada data' }}</div>
            <p class="text-[11px] text-slate-500 mt-2">Berdasarkan evaluasi integritas pelayanan publik PST.</p>
        </div>

        <!-- Grand Averages Card -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
            <span class="text-xs font-bold text-amber-700 uppercase">Sumbu Sumbu Kartesius IPA</span>
            <div class="space-y-2 mt-2">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-600">Rata-rata Kepuasan ($\bar{X}$):</span>
                    <strong class="text-bps-navy">{{ $metrics['grand_mean_satisfaction'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-600">Rata-rata Kepentingan ($\bar{Y}$):</span>
                    <strong class="text-bps-orange">{{ $metrics['grand_mean_importance'] ?? '—' }}</strong>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-3">Garis potong kuadran A, B, C, dan D pada plot koordinat.</p>
        </div>
    </div>

    <!-- Cartesian Diagram Plot Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Diagram Kartesius Importance-Performance Analysis (IPA)</h2>
                <p class="text-xs text-slate-500">Render langsung dalam format vektor SVG tajam dari Python Worker Engine.</p>
            </div>
            <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600">matplotlib SVG</span>
        </div>

        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex items-center justify-center min-h-[380px]">
            @if(file_exists(base_path($svgPath)))
                <div class="w-full max-w-3xl overflow-hidden rounded-lg shadow-inner bg-white p-2">
                    {!! file_get_contents(base_path($svgPath)) !!}
                </div>
            @else
                <p class="text-xs text-slate-400">Grafik SVG belum di-generate.</p>
            @endif
        </div>
    </div>

    <!-- Table 12 Attributes Matrix -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h2 class="text-base font-bold text-bps-navy">Tabel Matriks 12 Unsur Pelayanan Publik BPS Jember</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kalkulasi skor kepuasan, kepentingan, gap analysis, kesesuaian, dan posisi kuadran.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Unsur Pelayanan</th>
                        <th class="py-3 px-4 text-center">Kepuasan ($\bar{X}$)</th>
                        <th class="py-3 px-4 text-center">Kepentingan ($\bar{Y}$)</th>
                        <th class="py-3 px-4 text-center">Kesenjangan (Gap)</th>
                        <th class="py-3 px-4 text-center">Tingkat Kesesuaian (TK)</th>
                        <th class="py-3 px-4 text-center">Kuadran IPA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @if(isset($metrics['attributes']))
                        @foreach($metrics['attributes'] as $attr)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-bps-navy">{{ $attr['code'] }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-800">{{ $attr['name'] }}</span>
                                <span class="text-slate-400 block text-[10px] italic">{{ $attr['name_en'] }}</span>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $attr['mean_satisfaction'] }}</td>
                            <td class="py-3 px-4 text-center font-bold text-slate-700">{{ $attr['mean_importance'] }}</td>
                            <td class="py-3 px-4 text-center font-mono {{ $attr['gap'] < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $attr['gap'] }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono font-bold">{{ $attr['conformity_rate'] }}%</td>
                            <td class="py-3 px-4 text-center">
                                @php
                                    $qColor = match($attr['quadrant']) {
                                        'A' => 'bg-rose-100 text-rose-800 border-rose-300',
                                        'B' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'C' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        'D' => 'bg-slate-100 text-slate-800 border-slate-300',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold border {{ $qColor }}">
                                    Kuadran {{ $attr['quadrant'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lampiran 14 & 15 Rekomendasi Perbaikan Layanan -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div>
            <h2 class="text-base font-bold text-bps-navy">Rekomendasi Tindak Lanjut Perbaikan Layanan (Lampiran 14 & 15 SKD)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Tindakan prioritas otomatis dirumuskan berdasarkan unsur-unsur yang jatuh pada Kuadran A dan Kuadran C.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-rose-50 border border-rose-200 rounded-lg p-4">
                <h3 class="text-xs font-bold text-rose-800 uppercase mb-2 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-600 mr-2"></span>
                    Prioritas Utama (Kuadran A - High Importance, Low Performance)
                </h3>
                <ul class="text-xs text-rose-900 space-y-2 list-disc list-inside">
                    <li><strong>U4 (Biaya Bebas Pungli / Nol Rupiah):</strong> Menegaskan maklumat pelayanan bahwa seluruh data dan konsultasi di PST BPS Jember tidak dipungut biaya.</li>
                    <li><strong>U12 (Kenyamanan Ruang PST):</strong> Peningkatan fasilitas ruang tunggu, kebersihan sarana AC, dan penyediaan welcome drink bagi pengunjung.</li>
                </ul>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <h3 class="text-xs font-bold text-amber-800 uppercase mb-2 flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600 mr-2"></span>
                    Prioritas Rendah (Kuadran C - Low Importance, Low Performance)
                </h3>
                <ul class="text-xs text-amber-900 space-y-2 list-disc list-inside">
                    <li><strong>U1 (Kesesuaian Persyaratan Layanan):</strong> Penyederhanaan formulir registrasi buku tamu PST berbasis digital/QR code.</li>
                    <li><strong>U5 (Kesesuaian Produk Layanan):</strong> Penyelarasan format tabel mikro bagi kalangan akademisi dan mahasiswa.</li>
                </ul>
            </div>
        </div>
    </div>

</div>
@endsection
