@extends('layouts.app', ['title' => 'Redaksi Ulasan - SI-PENA'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-bps-navy">Fase 2: Editor Redaksi Ulasan Bilingual (ID/EN)</h1>
            <p class="text-xs text-slate-500 mt-1">
                Penyuntingan teks narasi pembuka bab dalam format dua kolom berdampingan resmi BPS disertai metrik sorotan (Key Figures).
            </p>
        </div>
        <form method="GET" action="{{ route('editorial.index') }}" class="flex items-center space-x-2">
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
    <!-- Selected Publication Chapters Card -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-bps-navy">{{ $selectedPub->title }}</h2>
                <span class="text-xs text-slate-500">Katalog: {{ $selectedPub->catalog_number }} | Status: <strong class="text-bps-orange">{{ $selectedPub->status }}</strong></span>
            </div>
            <a href="{{ route('covers.index', ['publication_id' => $selectedPub->id]) }}" class="text-xs font-bold text-bps-blue hover:underline">
                Pratinjau Pembatas Bab &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($selectedPub->narratives as $nar)
            <div class="p-5 hover:bg-slate-50/50 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5 flex-1">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-bps-orange flex items-center justify-center font-bold text-sm">
                            {{ $nar->chapter_number }}
                        </span>
                        <div>
                            <h3 class="font-bold text-sm text-bps-navy">{{ $nar->title_id }}</h3>
                            <p class="text-xs text-slate-400 italic">{{ $nar->title_en }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 bg-slate-50 p-3 rounded-lg border border-slate-200/60 text-xs">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">ULASAN (INDONESIA)</span>
                            <p class="text-slate-700 line-clamp-2">{{ $nar->narrative_id ?: 'Belum ada narasi ulasan.' }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">DESCRIPTION (ENGLISH)</span>
                            <p class="text-slate-500 italic line-clamp-2">{{ $nar->narrative_en ?: 'No english description available.' }}</p>
                        </div>
                    </div>

                    @if($nar->highlight_label && $nar->highlight_value)
                    <div class="flex items-center space-x-2 mt-1 text-xs">
                        <span class="text-slate-500 font-medium">Indikator Kunci:</span>
                        <span class="font-bold text-bps-darkorange">{{ $nar->highlight_value }}</span>
                        <span class="text-slate-400">({{ $nar->highlight_label }})</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center space-x-2">
                    <a href="{{ route('editorial.edit', $nar->id) }}" class="inline-flex items-center px-4 py-2 bg-bps-navy hover:bg-bps-darknavy text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Sunting Ulasan
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-xs">
                Belum ada data bab untuk publikasi ini.
            </div>
            @endforelse
        </div>
    </div>
    @endif

</div>
@endsection
