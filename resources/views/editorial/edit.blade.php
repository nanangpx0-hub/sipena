@extends('layouts.app', ['title' => 'Sunting Ulasan Bab ' . $narrative->chapter_number . ' - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Top Breadcrumb & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('editorial.index', ['publication_id' => $narrative->publication_id]) }}" class="text-xs font-semibold text-bps-blue hover:underline flex items-center">
                &larr; Kembali ke Daftar Bab
            </a>
            <h1 class="text-xl font-bold text-bps-navy mt-1">
                Sunting Ulasan Bab {{ $narrative->chapter_number }}: {{ $narrative->title_id }}
            </h1>
            <p class="text-xs text-slate-500">{{ $narrative->publication->title }}</p>
        </div>
    </div>

    @if($narrative->publication->isLocked())
    <!-- Lock Banner: APPROVED_LOCKED / FINAL_RELEASED -->
    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-md p-4 flex items-start space-x-3">
        <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
        <div class="text-sm text-amber-800">
            <strong>Publikasi terkunci ({{ $narrative->publication->status }}).</strong>
            Penyuntingan narasi dan tabel dinonaktifkan. Minta Approver membuka kunci dengan catatan revisi terlebih dahulu.
        </div>
    </div>
    @endif

    <!-- Two-Column Bilingual Editor Form -->
    <form action="{{ route('editorial.update', $narrative->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Highlight Key Figures Card -->
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-5 border border-orange-200">
            <h2 class="text-xs font-bold text-bps-darkorange uppercase tracking-wider mb-3">
                Indikator Kunci Pembatas Bab (Key Figures on Divider)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Label Indikator Kunci</label>
                    <input type="text" name="highlight_label" @disabled($narrative->publication->isLocked()) value="{{ old('highlight_label', $narrative->highlight_label) }}" placeholder="Contoh: Jumlah Penduduk / Luas Wilayah" class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-orange focus:ring focus:ring-bps-orange/20 p-2.5 bg-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Angka Indikator</label>
                    <input type="text" name="highlight_value" @disabled($narrative->publication->isLocked()) value="{{ old('highlight_value', $narrative->highlight_value) }}" placeholder="Contoh: 72.450 Jiwa / 61,70 kmÂ²" class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-orange focus:ring focus:ring-bps-orange/20 p-2.5 bg-white font-bold text-bps-navy">
                </div>
            </div>
        </div>

        <!-- Bilingual Two-Column Editor -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-bps-navy mb-4 flex items-center justify-between">
                <span>Struktur Narasi Bilingual Berdampingan (Two-Column Layout)</span>
                <span class="text-[11px] font-normal text-slate-400">Layout Resmi BPS RI</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Bahasa Indonesia -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-bps-navy uppercase flex items-center">
                            <span class="w-2 h-2 rounded-full bg-bps-navy mr-1.5"></span>
                            Kolom Kiri: ULASAN (Bahasa Indonesia)
                        </label>
                        <span class="text-[10px] text-slate-400">Heading: ULASAN</span>
                    </div>
                    <textarea name="narrative_id" rows="12" @disabled($narrative->publication->isLocked()) required class="w-full text-xs rounded-lg border-slate-300 focus:border-bps-navy focus:ring focus:ring-bps-navy/20 p-3 bg-slate-50 leading-relaxed font-sans">{{ old('narrative_id', $narrative->narrative_id) }}</textarea>
                    <p class="text-[11px] text-slate-500 italic">
                        Tip: Gunakan bahasa baku yang lugas dan mengacu pada angka pada tabel-tabel bab ini.
                    </p>
                </div>

                <!-- Kolom Kanan: Bahasa Inggris -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-600 uppercase flex items-center">
                            <span class="w-2 h-2 rounded-full bg-slate-400 mr-1.5"></span>
                            Kolom Kanan: DESCRIPTION (English)
                        </label>
                        <span class="text-[10px] text-slate-400">Heading: DESCRIPTION</span>
                    </div>
                    <textarea name="narrative_en" rows="12" @disabled($narrative->publication->isLocked()) required class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-500 focus:ring focus:ring-slate-500/20 p-3 bg-slate-50 leading-relaxed font-sans italic text-slate-700">{{ old('narrative_en', $narrative->narrative_en) }}</textarea>
                    <p class="text-[11px] text-slate-500 italic">
                        Tip: Translated mirror description for international readers and cataloging.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('editorial.index', ['publication_id' => $narrative->publication_id]) }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900">
                    Batal
                </a>
                @unless($narrative->publication->isLocked())
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Ulasan Redaksi
                </button>
                @endunless
            </div>
        </div>
    </form>

    @unless($narrative->publication->isLocked())
    <!-- Pengajuan Publikasi ke Meja Approver (form terpisah, di luar form penyuntingan) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-bps-navy">Selesai menyunting seluruh bab?</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Status saat ini: <strong class="text-bps-orange">{{ $narrative->publication->status }}</strong>.
                Pengajuan akan mengunci alur redaksi dan meneruskan publikasi ke Quality Control &amp; Approval.
            </p>
        </div>
        <form action="{{ route('editorial.submit', $narrative->id) }}" method="POST"
              onsubmit="return confirm('Ajukan publikasi ini ke meja Approver (PENDING_APPROVAL)? Pastikan seluruh bab dan tabel telah selesai disunting.')">
            @csrf
            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-bps-blue hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                Ajukan ke Approver
            </button>
        </form>
    </div>
    @endunless

</div>
@endsection
