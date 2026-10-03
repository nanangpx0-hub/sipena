@extends('layouts.app', ['title' => 'Analisis Hasil Survei Kebutuhan Data (SKD) - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Publikasi Analisis Tahunan</span>
            <h1 class="text-xl font-bold text-bps-navy mt-1">Analisis Hasil Survei Kebutuhan Data (SKD) {{ $selectedYear ?? date('Y') }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kalkulasi matriks kuesioner VKD (Blok I, II, III), Indeks Kepuasan Konsumen (IKK), Indeks Persepsi Anti Korupsi (IPAK), dan Diagram Kartesius IPA.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            @if(count($skdYears ?? []) > 1)
            <form method="GET" action="{{ route('skd.index') }}" class="flex items-center space-x-1.5">
                <label for="skd-year" class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun</label>
                <select name="year" id="skd-year" onchange="this.form.submit()"
                        class="text-xs font-bold rounded-lg border-slate-300 focus:border-bps-navy p-2 bg-slate-50 text-slate-700">
                    @foreach($skdYears as $skdYear)
                        <option value="{{ $skdYear }}" @selected((int) $selectedYear === (int) $skdYear)>{{ $skdYear }}</option>
                    @endforeach
                </select>
            </form>
            @endif
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

    @php
        // Sudut jarum speedometer: nilai 0 -> -90 derajat (kiri), 100 -> +90 (kanan).
        $gaugeAngle = static function ($score) {
            if (! is_numeric($score)) {
                return null;
            }
            $value = max(0.0, min(100.0, (float) $score));

            return deg2rad(-90 + ($value * 1.8));
        };
    @endphp

    <!-- Headline Metric Cards: IKK & IPAK sebagai Speedometer 0-100 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- IKK Speedometer -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-bold text-blue-700 uppercase">Indeks Kepuasan Konsumen (IKK)</span>
                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-amber-700 bg-amber-100 border border-amber-300 px-1.5 py-0.5 rounded shrink-0"
                      title="Piala mutu indeks pelayanan">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.17 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69L9.05 2.93z"/></svg>
                    Piala Mutu
                </span>
            </div>

            <div class="flex items-center justify-center mt-2">
                @if(isset($metrics['ikk_score']) && is_numeric($metrics['ikk_score']))
                    <svg viewBox="0 0 140 100" class="w-full max-w-[190px]" role="img"
                         aria-label="Speedometer IKK, nilai {{ $metrics['ikk_score'] }} dari 100">
                        {{-- Zona busur mengikuti ambang mutu pelayanan (A >= 88,31; B >= 76,61; C >= 65) --}}
                        <path d="M20 88 A50 50 0 0 1 40.5 44.4" fill="none" stroke="#F87171" stroke-width="11"/>
                        <path d="M40.5 44.4 A50 50 0 0 1 70 38" fill="none" stroke="#FBBF24" stroke-width="11"/>
                        <path d="M70 38 A50 50 0 0 1 99.5 44.4" fill="none" stroke="#34D399" stroke-width="11"/>
                        <path d="M99.5 44.4 A50 50 0 0 1 120 88" fill="none" stroke="#10B981" stroke-width="11"/>
                        <text x="18" y="99" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">0</text>
                        <text x="70" y="33" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">50</text>
                        <text x="122" y="99" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">100</text>
                        @php $ikkAngle = $gaugeAngle($metrics['ikk_score']); @endphp
                        <g transform="rotate({{ rad2deg((float) $ikkAngle) }} 70 88)">
                            <line x1="70" y1="88" x2="70" y2="44" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
                        </g>
                        <circle cx="70" cy="88" r="5" fill="#1E293B"/>
                        <circle cx="70" cy="88" r="2" fill="#FFFFFF"/>
                    </svg>
                @else
                    <div class="w-full max-w-[190px] aspect-[1.4/1] rounded-lg border-2 border-dashed border-blue-200 flex items-center justify-center text-center px-3">
                        <span class="text-xs font-bold text-blue-400 italic">Belum ada data</span>
                    </div>
                @endif
            </div>

            <div class="text-center -mt-2">
                <div class="text-3xl font-black text-blue-900">{{ $metrics['ikk_score'] ?? '—' }}</div>
                <div class="text-xs font-semibold text-blue-800 mt-0.5">Mutu: {{ $metrics['mutu_pelayanan'] ?? 'Belum ada data' }}</div>
                <p class="text-[10px] text-slate-500 mt-1">Skala 0–100 &middot; Permenpan RB No. 14 Tahun 2017</p>
            </div>
        </div>

        <!-- IPAK Speedometer -->
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-bold text-emerald-700 uppercase">Indeks Persepsi Anti Korupsi (IPAK)</span>
                <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase text-emerald-700 bg-emerald-100 border border-emerald-300 px-1.5 py-0.5 rounded shrink-0"
                      title="Indeks persepsi anti korupsi atas unsur integritas pelayanan">
                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 111.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    Integritas
                </span>
            </div>

            <div class="flex items-center justify-center mt-2">
                @if(isset($metrics['ipak_score']) && is_numeric($metrics['ipak_score']))
                    <svg viewBox="0 0 140 100" class="w-full max-w-[190px]" role="img"
                         aria-label="Speedometer IPAK, nilai {{ $metrics['ipak_score'] }} dari 100">
                        <path d="M20 88 A50 50 0 0 1 40.5 44.4" fill="none" stroke="#F87171" stroke-width="11"/>
                        <path d="M40.5 44.4 A50 50 0 0 1 70 38" fill="none" stroke="#FBBF24" stroke-width="11"/>
                        <path d="M70 38 A50 50 0 0 1 99.5 44.4" fill="none" stroke="#34D399" stroke-width="11"/>
                        <path d="M99.5 44.4 A50 50 0 0 1 120 88" fill="none" stroke="#10B981" stroke-width="11"/>
                        <text x="18" y="99" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">0</text>
                        <text x="70" y="33" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">50</text>
                        <text x="122" y="99" font-size="7" font-weight="700" fill="#64748B" text-anchor="middle">100</text>
                        @php $ipakAngle = $gaugeAngle($metrics['ipak_score']); @endphp
                        <g transform="rotate({{ rad2deg((float) $ipakAngle) }} 70 88)">
                            <line x1="70" y1="88" x2="70" y2="44" stroke="#1E293B" stroke-width="2.4" stroke-linecap="round"/>
                        </g>
                        <circle cx="70" cy="88" r="5" fill="#1E293B"/>
                        <circle cx="70" cy="88" r="2" fill="#FFFFFF"/>
                    </svg>
                @else
                    <div class="w-full max-w-[190px] aspect-[1.4/1] rounded-lg border-2 border-dashed border-emerald-200 flex items-center justify-center text-center px-3">
                        <span class="text-xs font-bold text-emerald-400 italic">Belum ada data</span>
                    </div>
                @endif
            </div>

            <div class="text-center -mt-2">
                <div class="text-3xl font-black text-emerald-900">{{ $metrics['ipak_score'] ?? '—' }}</div>
                {{-- INTEGRITAS ANGKA: predikat resmi hanya sah bila sumbernya hasil
                     survei nyata. Bila data_source = sample, angka metrik boleh tampil
                     namun predikat resmi TIDAK boleh dicetak. --}}
                @php $isOfficialSkd = ($metrics['data_source'] ?? '') !== 'sample'; @endphp
                <div class="text-xs font-semibold mt-0.5 {{ $isOfficialSkd ? 'text-emerald-800' : 'text-amber-800' }}">
                    @if(! $metrics)
                        Belum ada data
                    @elseif($isOfficialSkd)
                        Predikat: Sangat Bersih dari Pungli &amp; Korupsi
                    @else
                        Data contoh &mdash; predikat resmi belum dapat ditetapkan
                    @endif
                </div>
                <p class="text-[10px] text-slate-500 mt-1">Skala 0–100 &middot; Unsur U4, U6, U7</p>
            </div>
        </div>

        <!-- Grand Averages Card -->
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 shadow-sm">
            <span class="text-xs font-bold text-amber-700 uppercase">Sumbu Diagram Kartesius IPA</span>
            <div class="space-y-2 mt-2">
                <div class="flex justify-between text-xs items-center">
                    <span class="text-slate-600">Rata-rata Kepuasan (X&#772;):</span>
                    <strong class="text-bps-navy font-mono">{{ $metrics['grand_mean_satisfaction'] ?? '—' }}</strong>
                </div>
                <div class="flex justify-between text-xs items-center">
                    <span class="text-slate-600">Rata-rata Kepentingan (Y&#772;):</span>
                    <strong class="text-bps-orange font-mono">{{ $metrics['grand_mean_importance'] ?? '—' }}</strong>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-amber-200/60">
                <p class="text-[10px] text-slate-500 leading-relaxed">
                    Garis potong kuadran A, B, C, dan D pada plot koordinat. Setiap unsur
                    diposisikan menurut perbandingan kepuasan terhadap kepentingan responden.
                </p>
            </div>
            <div class="mt-2 flex flex-wrap gap-1.5 text-[9px] font-bold">
                <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-800">A &middot; Prioritas Utama</span>
                <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">B &middot; Pertahankan</span>
                <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800">C &middot; Prioritas Rendah</span>
                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">D &middot; Berlebihan</span>
            </div>
        </div>
    </div>

    <!-- Narasi Interpretasi 4 Kuadran IPA -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-2 mb-1">
            <span class="w-2.5 h-2.5 rounded-full bg-bps-orange"></span>
            <h2 class="text-base font-bold text-bps-navy">Narasi Interpretasi Empat Kuadran IPA</h2>
        </div>
        <p class="text-xs text-slate-500 mb-4">
            Pembagian kuadran ditentukan oleh posisi masing-masing unsur pelayanan terhadap
            rata-rata kepuasan (X&#772;) dan rata-rata kepentingan (Y&#772;) seluruh responden.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach([
                [
                    'q' => 'A',
                    'title' => 'Prioritas Utama',
                    'subtitle' => 'High Importance, Low Satisfaction',
                    'border' => 'border-rose-300 bg-rose-50',
                    'dot' => 'bg-rose-600',
                    'text' => 'text-rose-900',
                    'codes' => $metrics['quadrants']['A'] ?? [],
                    'narasi' => 'Unsur dengan kepentingan tinggi namun kepuasan masih di bawah rata-rata. Unsur ini menjadi fokus utama perbaikan pelayanan PST. Tindakan yang diperlukan: penyediaan sumber daya, penambahan petugas, dan perbaikan alur kerja.',
                ],
                [
                    'q' => 'B',
                    'title' => 'Pertahankan Prestasi',
                    'subtitle' => 'High Importance, High Satisfaction',
                    'border' => 'border-emerald-300 bg-emerald-50',
                    'dot' => 'bg-emerald-600',
                    'text' => 'text-emerald-900',
                    'codes' => $metrics['quadrants']['B'] ?? [],
                    'narasi' => 'Unsur yang dinilai penting oleh responden sekaligus sudah dilayani dengan baik. Prestasi ini wajib dipertahankan dan dimonitor agar tidak menurun pada periode survei berikutnya.',
                ],
                [
                    'q' => 'C',
                    'title' => 'Prioritas Rendah',
                    'subtitle' => 'Low Importance, Low Satisfaction',
                    'border' => 'border-amber-300 bg-amber-50',
                    'dot' => 'bg-amber-600',
                    'text' => 'text-amber-900',
                    'codes' => $metrics['quadrants']['C'] ?? [],
                    'narasi' => 'Unsur yang relatif kurang diperhatikan responden namun kinerjanya juga belum memuaskan. Perbaikan dilakukan setelah penanganan Kuadran A selesai, kecuali unsur ini berkaitan dengan ketentuan normatif yang bersifat wajib.',
                ],
                [
                    'q' => 'D',
                    'title' => 'Berlebihan',
                    'subtitle' => 'Low Importance, High Satisfaction',
                    'border' => 'border-slate-300 bg-slate-50',
                    'dot' => 'bg-slate-500',
                    'text' => 'text-slate-800',
                    'codes' => $metrics['quadrants']['D'] ?? [],
                    'narasi' => 'Unsur yang kinerjanya di atas rata-rata namun tidak terlalu diprioritaskan responden. Alokasi sumber daya dapat diturunkan secara proporsional tanpa menurunkan kepuasan keseluruhan.',
                ],
            ] as $quad)
                <div class="border {{ $quad['border'] }} rounded-lg p-4">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg {{ $quad['dot'] }} text-white flex items-center justify-center text-sm font-black shrink-0">{{ $quad['q'] }}</span>
                            <div>
                                <h3 class="text-xs font-black {{ $quad['text'] }} uppercase">{{ $quad['title'] }}</h3>
                                <p class="text-[10px] italic {{ $quad['text'] }} opacity-70">{{ $quad['subtitle'] }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 text-[10px] font-black px-2 py-0.5 rounded bg-white/70 {{ $quad['text'] }} border border-slate-300/60">
                            {{ count($quad['codes']) }} unsur
                        </span>
                    </div>
                    <p class="text-[11px] {{ $quad['text'] }} opacity-90 leading-relaxed">{{ $quad['narasi'] }}</p>
                    @if(count($quad['codes']) > 0)
                        <div class="mt-2.5 pt-2.5 border-t border-slate-300/50 flex flex-wrap gap-1">
                            @foreach($quad['codes'] as $code)
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-white/80 border border-slate-300/60">{{ $code }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2.5 pt-2.5 border-t border-slate-300/50 text-[10px] italic opacity-60">
                            Belum ada unsur pada kuadran ini.
                        </p>
                    @endif
                </div>
            @endforeach
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

    <!-- Gap Analysis: selisih kepuasan vs kepentingan -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Diagram Batang Gap Analysis</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Selisih antara kepentingan (Y&#772;) dan kepuasan (X&#772;) responden per unsur pelayanan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-[10px] font-bold">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2.5 rounded-sm bg-rose-500 inline-block"></span> Gap Negatif &mdash; kepuasan di bawah kepentingan (prioritas perbaikan)</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2.5 rounded-sm bg-emerald-500 inline-block"></span> Gap Positif &mdash; kepuasan melampaui kepentingan</span>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 mb-4">Skala 1&ndash;4. Panjang batang dinormalisasi terhadap jarak maksimum dari titik nol.</p>

        @php
            // Skala batang dinormalisasi terhadap selisih absolut terbesar pada
            // sekumpulan data nyata; bila data kosong, bagan menampilkan
            // kondisi "belum ada data" (tidak pernah ada batang karangan).
            $gapAttributes = $metrics['attributes'] ?? [];
            $gapMax = 0.0;
            foreach ($gapAttributes as $attr) {
                if (isset($attr['gap']) && is_numeric($attr['gap'])) {
                    $gapMax = max($gapMax, abs((float) $attr['gap']));
                }
            }
        @endphp

        @if($gapMax <= 0)
            <div class="border-2 border-dashed border-slate-200 rounded-lg py-10 text-center">
                <p class="text-xs font-bold text-slate-400 italic">Belum ada data</p>
                <p class="text-[10px] text-slate-400 mt-1">Gap analysis memerlukan kuesioner VKD yang sudah diunggah.</p>
            </div>
        @else
            <div class="space-y-2.5" role="img" aria-label="Diagram batang horizontal gap analysis 12 unsur pelayanan">
                @foreach($gapAttributes as $attr)
                    @php
                        $gapValue = (float) $attr['gap'];
                        $widthPct = round(abs($gapValue) * 100 / $gapMax, 1);
                        $isNegative = $gapValue < 0;
                        $barColor = $isNegative ? 'bg-rose-500' : 'bg-emerald-500';
                        $valueColor = $isNegative ? 'text-rose-700' : 'text-emerald-700';
                    @endphp
                    <div class="grid grid-cols-[7rem_1fr_3.5rem] sm:grid-cols-[10rem_1fr_4rem] items-center gap-2">
                        <div class="text-right min-w-0">
                            <span class="text-[10px] font-black text-bps-navy font-mono">{{ $attr['code'] }}</span>
                            <span class="block text-[9px] text-slate-500 truncate" title="{{ $attr['name'] }}">{{ $attr['name'] }}</span>
                        </div>
                        {{-- Sumbu nol di tengah; batang tumbuh ke kiri (negatif) atau kanan (positif). --}}
                        <div class="relative h-6 bg-slate-50 rounded border border-slate-100">
                            <div class="absolute inset-y-0 left-1/2 w-px bg-slate-300"></div>
                            <div class="absolute inset-y-0 left-0 right-0 flex items-center">
                                @if($isNegative)
                                    <div class="h-3.5 {{ $barColor }} rounded-l ml-0 mr-auto" style="width: calc(50% - {{ $widthPct / 2 }}%); margin-left: auto; margin-right: 50%;"></div>
                                @else
                                    <div class="h-3.5 {{ $barColor }} rounded-r ml-1/2" style="width: calc({{ $widthPct / 2 }}%);"></div>
                                @endif
                            </div>
                        </div>
                        <div class="text-right font-mono text-[10px] font-bold {{ $valueColor }}">
                            {{ number_format($gapValue, 2, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Infografis 12 Unsur Pelayanan Publik -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Infografis 12 Unsur Pelayanan Publik</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Standar unsur pelayanan statistik yang dipakai pada Survei Kebutuhan Data
                    (Permenpan RB No. 14 Tahun 2017). Warna kartu mengikuti kuadran IPA masing-masing unsur.
                </p>
            </div>
            <span class="text-[10px] font-black bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded">U1 &ndash; U12</span>
        </div>

        @php
            $quadrantPalette = [
                'A' => ['label' => 'Prioritas Utama',  'card' => 'border-rose-300 bg-rose-50',      'chip' => 'bg-rose-600 text-white',       'text' => 'text-rose-900'],
                'B' => ['label' => 'Pertahankan',      'card' => 'border-emerald-300 bg-emerald-50','chip' => 'bg-emerald-600 text-white',   'text' => 'text-emerald-900'],
                'C' => ['label' => 'Prioritas Rendah', 'card' => 'border-amber-300 bg-amber-50',   'chip' => 'bg-amber-600 text-white',     'text' => 'text-amber-900'],
                'D' => ['label' => 'Berlebihan',       'card' => 'border-slate-300 bg-slate-50',    'chip' => 'bg-slate-600 text-white',     'text' => 'text-slate-800'],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @forelse($gapAttributes as $attr)
                @php $pal = $quadrantPalette[$attr['quadrant'] ?? 'D'] ?? $quadrantPalette['D']; @endphp
                <div class="border {{ $pal['card'] }} rounded-lg p-3 flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-base font-black {{ $pal['text'] }} font-mono">{{ $attr['code'] }}</span>
                        <span class="text-[8px] font-black uppercase px-1.5 py-0.5 rounded {{ $pal['chip'] }}">
                            {{ $attr['quadrant'] ?? '—' }}
                        </span>
                    </div>
                    <p class="text-[10px] font-bold {{ $pal['text'] }} leading-tight">{{ $attr['name'] }}</p>
                    <p class="text-[9px] italic text-slate-400 truncate">{{ $attr['name_en'] }}</p>
                    <div class="mt-2 pt-2 border-t border-slate-300/40 space-y-0.5 text-[9px] font-mono text-slate-600">
                        <div class="flex justify-between"><span>X&#772;</span><span class="font-bold">{{ $attr['mean_satisfaction'] }}</span></div>
                        <div class="flex justify-between"><span>Y&#772;</span><span class="font-bold">{{ $attr['mean_importance'] }}</span></div>
                    </div>
                    <p class="mt-1.5 text-[8px] font-bold uppercase tracking-wide text-slate-500">{{ $pal['label'] }}</p>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 rounded-lg py-10 text-center">
                    <p class="text-xs font-bold text-slate-400 italic">Belum ada data</p>
                    <p class="text-[10px] text-slate-400 mt-1">Unggah berkas kuesioner VKD untuk mengisi 12 unsur pelayanan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Profil Demografi Konsumen PST BPS Jember -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Profil Demografi Konsumen PST BPS Jember</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Sebaran responden survei kebutuhan data berdasarkan kolom demografis pada berkas kuesioner VKD.
                </p>
            </div>
            @if($profile = ($metrics['respondent_profile'] ?? null))
                <span class="text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-200 px-2.5 py-1 rounded">
                    Total Responden: {{ $profile['total_respondents'] }}
                </span>
            @endif
        </div>

        @php $profile = $metrics['respondent_profile'] ?? null; @endphp

        @if(! $profile || empty($profile['dimensions']))
            {{-- INTEGRITAS ANGKA: profil hanya tampil bila berkas kuesioner benar-benar
                 memuat kolom demografi. Sistem tidak pernah mengarang sebaran. --}}
            <div class="px-5 py-10 text-center border-b border-slate-100">
                <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm font-bold text-slate-600">Profil demografi belum ada data</p>
                <p class="text-xs text-slate-400 mt-1 max-w-lg mx-auto leading-relaxed">
                    Kolom demografi bersifat opsional pada kuesioner VKD. Profil ditampilkan
                    hanya bila berkas kuesioner yang diunggah memuat kolom
                    <code class="font-mono text-[11px]">jenis_kelamin</code>,
                    <code class="font-mono text-[11px]">kelompok_usia</code>,
                    <code class="font-mono text-[11px]">pendidikan</code>,
                    <code class="font-mono text-[11px]">profesi</code>, atau
                    <code class="font-mono text-[11px]">jenis_konsumen</code>.
                   Distribusi jumlah responden tidak dapat diperkirakan oleh sistem.
                </p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($profile['dimensions'] as $dimension)
                    <div class="p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <div>
                                <h3 class="text-xs font-black text-bps-navy uppercase">{{ $dimension['label'] }}</h3>
                                <p class="text-[10px] text-slate-400 italic">{{ $dimension['label_en'] }} &middot; kolom sumber: <code class="font-mono">{{ $dimension['column'] }}</code></p>
                            </div>
                            <span class="text-[9px] font-bold bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ count($dimension['breakdown']) }} kategori</span>
                        </div>

                        <div class="space-y-2">
                            @foreach($dimension['breakdown'] as $row)
                                <div class="grid grid-cols-[minmax(6rem,10rem)_1fr_5.5rem] sm:grid-cols-[minmax(8rem,14rem)_1fr_7rem] items-center gap-2">
                                    <div class="text-[11px] font-semibold text-slate-700 truncate text-right" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                                    <div class="relative h-5 bg-slate-50 rounded border border-slate-100 overflow-hidden">
                                        <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-bps-blue to-bps-navy rounded"
                                             style="width: {{ max(0.5, (float) $row['percent']) }}%;"></div>
                                    </div>
                                    <div class="text-[10px] font-mono font-bold text-bps-navy">
                                        {{ number_format((float) $row['percent'], 1, ',', '.') }}%
                                        <span class="text-slate-400 font-normal">({{ $row['count'] }})</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
