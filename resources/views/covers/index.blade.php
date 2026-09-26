@extends('layouts.app', ['title' => 'Cover & Pembatas Bab - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Pratinjau Cover Depan & Kartu Pembatas Bab</h1>
            <p class="text-xs text-slate-500 mt-1">
                Visualisasi tata letak cover standar BPS (Navy `#0A3866`, barcode ISSN vektor) dan lembar pembatas bab beraksen oranye (`#E67E22`).
            </p>
        </div>
        <form method="GET" action="{{ route('covers.index') }}" class="flex items-center space-x-2">
            <select name="publication_id" onchange="this.form.submit()" class="text-xs rounded-lg border-slate-300 focus:border-bps-navy p-2 bg-slate-50 font-semibold text-slate-700">
                @foreach($publications as $pub)
                    <option value="{{ $pub->id }}" {{ $selectedPub?->id == $pub->id ? 'selected' : '' }}>
                        {{ $pub->title }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($selectedPub)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Front Cover Preview Box -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Cover Depan (Front Cover)</h2>
                    <span class="text-xs text-slate-400">Standar Format {{ $selectedPub->book_size }} BPS RI</span>
                </div>
                <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded">
                    Mode Otomatis Typst
                </span>
            </div>

            <!-- Scaled Preview of BPS Standard Cover -->
            <div class="w-full max-w-sm mx-auto aspect-[1/1.414] bg-[#0A3866] text-white rounded-lg shadow-xl overflow-hidden flex flex-col justify-between p-6 border-2 border-slate-300 relative">
                <!-- Top Header Section -->
                <div>
                    <div class="flex justify-between items-start text-[8px] text-slate-300 font-semibold">
                        <div>BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</div>
                        <div class="text-right">
                            No. Katalog: {{ $selectedPub->catalog_number ?? '1102001.3509' }}<br>
                            No. Publikasi: {{ $selectedPub->publication_number ?? '35090.2601' }}
                        </div>
                    </div>

                    <div class="mt-12">
                        <h3 class="text-lg font-black tracking-tight leading-tight uppercase text-white">
                            {{ $selectedPub->title }}
                        </h3>
                        <div class="text-bps-orange font-extrabold text-sm mt-2">
                            TAHUN {{ $selectedPub->year }}
                        </div>
                        <div class="text-[10px] text-slate-300">
                            VOLUME {{ $selectedPub->volume ?? '1' }}
                        </div>
                    </div>
                </div>

                <!-- Bottom Footer Section -->
                <div class="border-t border-white/20 pt-4 flex items-end justify-between">
                    <div>
                        <div class="text-[9px] font-bold text-white">BADAN PUSAT STATISTIK KABUPATEN JEMBER</div>
                        <div class="text-[7px] text-slate-300">Jl. Kalimantan No. 42 Jember 68121 - Jawa Timur</div>
                    </div>
                    @if($selectedPub->issn)
                    <div class="bg-white text-slate-900 px-2 py-1 rounded text-[8px] font-bold font-mono">
                        ISSN: {{ $selectedPub->issn }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chapter Divider Preview Box -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-bps-navy">Lembar Pembatas Bab (Chapter Divider)</h2>
                    <span class="text-xs text-slate-400">Contoh Bab 1: Geografi & Iklim</span>
                </div>
                <span class="text-xs bg-orange-100 text-bps-darkorange font-bold px-2 py-0.5 rounded">
                    Aksen Oranye #E67E22
                </span>
            </div>

            @php
                $firstNar = $selectedPub->narratives->first();
            @endphp
            <!-- Scaled Preview of Chapter Divider -->
            <div class="w-full max-w-sm mx-auto aspect-[1/1.414] bg-white text-slate-800 rounded-lg shadow-xl overflow-hidden flex flex-col justify-between p-8 border-2 border-slate-200 relative">
                <div>
                    <div class="text-6xl font-black text-bps-orange tracking-tight">
                        {{ $firstNar?->chapter_number ?? 1 }}
                    </div>
                    <h3 class="text-xl font-black text-bps-navy uppercase mt-1 leading-snug">
                        {{ $firstNar?->title_id ?? 'Geografi dan Iklim' }}
                    </h3>
                    <p class="text-xs text-slate-400 italic mt-0.5">
                        {{ $firstNar?->title_en ?? 'Geography and Climate' }}
                    </p>

                    <!-- Key Figures Highlight Card -->
                    <div class="mt-8 bg-amber-50/80 border border-amber-200 rounded-lg p-4 text-left">
                        <span class="text-[9px] font-bold text-amber-800 uppercase block tracking-wider">
                            INDIKATOR KUNCI / KEY FIGURES
                        </span>
                        <div class="text-2xl font-black text-bps-darkorange mt-1">
                            {{ $firstNar?->highlight_value ?? ($selectedPub->district?->total_area_sqkm . ' km²') }}
                        </div>
                        <div class="text-xs font-semibold text-amber-900 mt-0.5">
                            {{ $firstNar?->highlight_label ?? 'Luas Wilayah' }}
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-3 text-[9px] text-slate-400 flex justify-between">
                    <span>{{ $selectedPub->title }}</span>
                    <span>BPS Jember</span>
                </div>
            </div>
        </div>

    </div>
    @endif

    <!-- Suite 7: Manual Override — unggah cover kustom -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="{ manualMode: false }">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-bps-navy">Mode Manual Override — Cover Kustom</h2>
                <p class="text-xs text-slate-500 mt-0.5">Aktifkan sakelar lalu unggah gambar cover pengganti standar Typst.</p>
            </div>
            <label class="inline-flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                <span>Mode Otomatis Typst</span>
                <input type="checkbox" x-model="manualMode" name="manual_override" value="1"
                    class="w-5 h-5 rounded border-slate-300 text-bps-orange focus:ring-bps-orange">
                <span>Mode Manual Override</span>
            </label>
        </div>

        <div x-show="manualMode" x-cloak class="mt-4 pt-4 border-t border-slate-100">
            @if(isset($customCover))
            <div class="mb-3 text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                Cover kustom aktif: <strong>{{ basename($customCover->file_path) }}</strong>
                (mode MANUAL_OVERRIDE).
            </div>
            @endif
            <form action="{{ route('covers.upload') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <input type="hidden" name="publication_id" value="{{ $selectedPub?->id }}">
                <input type="file" name="cover_image" required accept=".jpg,.jpeg,.png,.webp,.svg,.pdf"
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-bps-orange file:text-white hover:file:bg-bps-darkorange cursor-pointer">
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm whitespace-nowrap">
                    Unggah Cover Kustom
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
