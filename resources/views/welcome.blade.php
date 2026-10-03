<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Gerbang SI-PENA - BPS Kabupaten Jember (3509)</title>
    <!-- Aset lokal (Tailwind, Alpine, font self-host) — jalan penuh di intranet tanpa internet -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <!-- ═══════════ TOPBAR BRANDING ═══════════ -->
    <header class="bg-bps-navy border-b-4 border-bps-orange sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('portal') }}" class="flex items-center space-x-3 group">
                @include('partials.bps-logo', ['class' => 'h-10 w-auto drop-shadow-sm group-hover:scale-105 transition-transform'])
                <div class="leading-tight">
                    <div class="flex items-center space-x-2">
                        <span class="font-extrabold text-xl tracking-tight text-white">SI-PENA</span>
                        <span class="bg-bps-orange text-white text-[10px] font-bold px-1.5 py-0.5 rounded">BPS 3509</span>
                    </div>
                    <p class="text-[11px] text-slate-300 font-medium">Sistem Penerbitan Angka &mdash; BPS Kabupaten Jember</p>
                </div>
            </a>
            <nav class="hidden md:flex items-center space-x-1 text-xs font-semibold text-slate-200">
                <a href="#pilar" class="px-3 py-2 rounded-md hover:bg-white/10">3 Pilar Publikasi</a>
                <a href="#alur" class="px-3 py-2 rounded-md hover:bg-white/10">Alur Terbit</a>
                <a href="#kalender" class="px-3 py-2 rounded-md hover:bg-white/10">Kalender Rilis</a>
                <a href="#kontak" class="px-3 py-2 rounded-md hover:bg-white/10">Kontak PST</a>
                <a href="{{ route('login') }}" class="ml-2 bg-bps-orange hover:bg-bps-darkorange text-white px-4 py-2 rounded-md shadow-sm transition-colors">
                    MASUK SISTEM
                </a>
            </nav>
            <a href="{{ route('login') }}" class="md:hidden bg-bps-orange text-white text-xs font-bold px-3 py-2 rounded-md">MASUK</a>
        </div>
    </header>


    <!-- ═══════════ HERO + SAMBUTAN RESMI ═══════════ -->
    <section class="relative overflow-hidden bg-gradient-to-br from-bps-navy via-bps-navy to-bps-darknavy text-white">
        <!-- Ornamen vektor latar: grid statistik + batang & garis tren -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.07]" aria-hidden="true" viewBox="0 0 800 400" preserveAspectRatio="none">
            <defs>
                <pattern id="gridStats" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M40 0H0v40" fill="none" stroke="#ffffff" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="800" height="400" fill="url(#gridStats)"/>
            <g fill="#E67E22">
                <rect x="80" y="260" width="26" height="90" rx="3"/>
                <rect x="120" y="210" width="26" height="140" rx="3"/>
                <rect x="160" y="170" width="26" height="180" rx="3"/>
                <rect x="640" y="240" width="26" height="110" rx="3"/>
                <rect x="680" y="190" width="26" height="160" rx="3"/>
                <rect x="720" y="140" width="26" height="210" rx="3"/>
            </g>
            <polyline points="80,250 160,160 240,200 320,120" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" opacity="0.6"/>
        </svg>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-flex items-center space-x-2 bg-white/10 border border-white/20 text-amber-300 text-[11px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Portal Gerbang &middot; Server Intranet &middot; Kode Wilayah 3509</span>
                </span>
                <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                    Portal Gerbang <span class="text-bps-orange">SI-PENA</span><br>
                    <span class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-slate-200">BPS Kabupaten Jember</span>
                </h1>
                <p class="mt-4 text-sm sm:text-base text-slate-300 max-w-xl leading-relaxed">
                    Sistem Penerbitan dan Penataan Angka Daerah &mdash; otomasi penuh penyusunan
                    <strong class="text-white">31 Kecamatan Dalam Angka (KDA)</strong>,
                    <strong class="text-white">Kabupaten Jember Dalam Angka (DDA)</strong>, dan
                    <strong class="text-white">Analisis Survei Kebutuhan Data (SKD)</strong>
                    dengan arsitektur tri-bahasa: PHP &middot; Python &middot; Typst.
                </p>

                <!-- Sambutan Resmi -->
                <div class="mt-6 bg-white/10 backdrop-blur-sm border border-white/15 rounded-xl p-5 max-w-xl">
                    <div class="flex items-center space-x-3 mb-3">
                        @include('partials.bps-logo', ['class' => 'h-9 w-auto bg-white rounded p-1'])
                        <div class="text-[11px] leading-tight">
                            <p class="font-black uppercase tracking-wide text-amber-300">Sambutan Resmi</p>
                            <p class="text-slate-300">Kepala BPS Kabupaten Jember</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-200 leading-relaxed italic">
                        &ldquo;Selamat datang di Portal Gerbang SI-PENA. Sistem ini memastikan setiap angka
                        statistik yang diterbitkan di Kabupaten Jember tercatat, terverifikasi, dan
                        dikompilasi sesuai standar mutu Badan Pusat Statistik &mdash; dari ingesti data OPD
                        hingga buku siap cetak.&rdquo;
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="bg-bps-orange hover:bg-bps-darkorange text-white font-bold text-sm px-6 py-3 rounded-lg shadow-lg transition-all hover:-translate-y-0.5">
                        Masuk ke SI-PENA
                    </a>
                    <a href="#pilar" class="bg-white/10 hover:bg-white/20 border border-white/25 text-white font-bold text-sm px-6 py-3 rounded-lg transition-colors">

            <!-- Kartu Ilustrasi Alur Tri-Language (interaktif Alpine) -->
            <div class="space-y-3" x-data="{ open: 'laravel' }">
                <p class="text-[11px] font-black uppercase tracking-widest text-amber-300">Kartu Alur Tri-Language Engine</p>
                <div class="grid grid-cols-1 gap-3">
                    <button type="button" @click="open = 'laravel'" :class="open === 'laravel' ? 'border-bps-orange bg-white/15' : 'border-white/15 bg-white/5'"
                        class="text-left w-full rounded-xl border-2 p-4 flex items-start space-x-4 transition-all">
                        <span class="shrink-0 w-11 h-11 rounded-lg bg-rose-500/20 border border-rose-400/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-bold text-sm text-white">Laravel 11 Backend</span>
                            <span class="block text-[12px] text-slate-300 leading-snug">Routing HTTP, Blade + Tailwind, RBAC peran, manajemen status &amp; deadline bab, transaksi MySQL 8.</span>
                        </span>
                    </button>
                    <button type="button" @click="open = 'python'" :class="open === 'python' ? 'border-bps-orange bg-white/15' : 'border-white/15 bg-white/5'"
                        class="text-left w-full rounded-xl border-2 p-4 flex items-start space-x-4 transition-all">
                        <span class="shrink-0 w-11 h-11 rounded-lg bg-sky-500/20 border border-sky-400/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-bold text-sm text-white">Python Analytics Worker</span>
                            <span class="block text-[12px] text-slate-300 leading-snug">Pembersihan Excel dinas (merge cell, desimal), agregasi sekolah/Dapodik, matriks SKD (IKK, IPAK, Gap), render SVG matplotlib.</span>
                        </span>
                    </button>
                    <button type="button" @click="open = 'typst'" :class="open === 'typst' ? 'border-bps-orange bg-white/15' : 'border-white/15 bg-white/5'"
                        class="text-left w-full rounded-xl border-2 p-4 flex items-start space-x-4 transition-all">
                        <span class="shrink-0 w-11 h-11 rounded-lg bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-bold text-sm text-white">Typst 0.15 Typesetting Engine</span>
                            <span class="block text-[12px] text-slate-300 leading-snug">Perakitan 100% PDF cetak standar BPS (300 DPI, font Roboto embed) oleh <code class="text-amber-300">typst.exe</code> CLI &mdash; tanpa DOMPDF.</span>
                        </span>
                    </button>
                </div>
                <!-- Logika komunikasi antar-engine (stream JSON stdout) -->
                <div class="rounded-xl bg-black/30 border border-white/10 p-4 font-mono text-[11px] text-emerald-300 space-y-1 overflow-x-auto">
                    <p><span class="text-slate-500">$</span> Laravel <span class="text-amber-300">-&gt;</span> Process <span class="text-amber-300">-&gt;</span> python.exe worker.py --json</p>
                    <p><span class="text-slate-500">$</span> worker <span class="text-amber-300">-&gt;</span> stdout: { "status": "success", ... }</p>
                    <p><span class="text-slate-500">$</span> Laravel <span class="text-amber-300">-&gt;</span> typst.exe compile storage/temp/*.typ -&gt; output_pdf/</p>
                </div>
            </div>
        </div>
    </section>

                        Jelajahi 3 Pilar Publikasi
                    </a>
                </div>

    <!-- ═══════════ 3 PILAR PUBLIKASI ═══════════ -->
    <section id="pilar" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-[11px] font-black uppercase tracking-widest text-bps-orange">Pilar Publikasi Resmi</span>
            <h2 class="text-2xl sm:text-3xl font-black text-bps-navy mt-1">Tiga Pilar Penerbitan Angka BPS Jember</h2>
            <p class="text-sm text-slate-500 mt-2">Seluruh keluaran SI-PENA berdiri di atas tiga pilar publikasi yang dikerjakan dengan siklus mutu yang sama.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Pilar 1: 31 KDA -->
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                <div class="h-2 bg-bps-orange"></div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-12 h-12 rounded-xl bg-orange-50 text-bps-orange flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                        </span>
                        <span class="text-3xl font-black text-slate-200">31</span>
                    </div>
                    <h3 class="font-extrabold text-bps-navy text-lg">Kecamatan Dalam Angka (KDA)</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Satu buku per kecamatan &mdash; seluruhnya <strong>31 kecamatan</strong> se-Kabupaten Jember.
                        Memuat 7 bab standar: Geografi, Pemerintahan, Kependudukan, Sosial, Pertanian, Pariwisata, dan Perbankan.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1 border-t border-slate-100 pt-3">
                        <li class="flex justify-between"><span>Format cetak</span><strong class="text-slate-700">A5</strong></li>
                        <li class="flex justify-between"><span>Bahasa</span><strong class="text-slate-700">Indonesia + Inggris</strong></li>
                        <li class="flex justify-between"><span>Motor kompilasi</span><strong class="text-slate-700">Typst CLI</strong></li>
                    </ul>
                </div>
            </article>

            <!-- Pilar 2: DDA -->
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                <div class="h-2 bg-emerald-600"></div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span class="text-3xl font-black text-slate-200">DDA</span>
                    </div>
                    <h3 class="font-extrabold text-bps-navy text-lg">Kabupaten Dalam Angka</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Publikasi induk tingkat makro Kabupaten Jember dengan <strong>13 bab komprehensif</strong>
                        &mdash; agregasi seluruh data 31 kecamatan, tren runtun waktu, dan capaian strategis daerah.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1 border-t border-slate-100 pt-3">
                        <li class="flex justify-between"><span>Cakupan</span><strong class="text-slate-700">Kabupaten Jember</strong></li>
                        <li class="flex justify-between"><span>Kolom narasi</span><strong class="text-slate-700">Bilingual dua kolom</strong></li>
                        <li class="flex justify-between"><span>ISSN</span><strong class="text-slate-700">Terdaftar</strong></li>
                    </ul>
                </div>
            </article>


            <!-- Pilar 3: SKD -->
            <article class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                <div class="h-2 bg-indigo-600"></div>
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                        <span class="text-3xl font-black text-slate-200">SKD</span>
                    </div>
                    <h3 class="font-extrabold text-bps-navy text-lg">Analisis Survei Kebutuhan Data</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Evaluasi layanan Pelayanan Statistik Terpadu (PST): <strong>IKK</strong>, <strong>IPAK</strong>,
                        dan Diagram Kartesius IPA atas 12 unsur pelayanan publik &mdash; dihitung mesin Python dari kuesioner VKD.
                    </p>
                    <ul class="text-[11px] text-slate-500 space-y-1 border-t border-slate-100 pt-3">
                        <li class="flex justify-between"><span>Sumber data</span><strong class="text-slate-700">Berkas kuesioner VKD</strong></li>
                        <li class="flex justify-between"><span>Metrik</span><strong class="text-slate-700">IKK &middot; IPAK &middot; Gap</strong></li>
                        <li class="flex justify-between"><span>Integritas</span><strong class="text-slate-700">Tanpa angka tebakan</strong></li>
                    </ul>
                </div>
            </article>
        </div>
    </section>


    <!-- ═══════════ DIAGRAM INTERAKTIF ALUR PENERBITAN ═══════════ -->
    <section id="alur" class="bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14" x-data="{ step: 1 }">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-[11px] font-black uppercase tracking-widest text-bps-orange">Alur Penerbitan Angka Daerah</span>
                <h2 class="text-2xl sm:text-3xl font-black text-bps-navy mt-1">Dari Berkas OPD Mentah sampai Buku Siap Cetak</h2>
                <p class="text-sm text-slate-500 mt-2">Klik setiap simpul untuk melihat detail tahapan. Setiap angka melewati gerbang verifikasi sebelum dikunci.</p>
            </div>

            <!-- Diagram SVG interaktif -->
            <div class="overflow-x-auto">
                <svg viewBox="0 0 900 190" class="w-full min-w-[720px] h-auto" role="img" aria-label="Diagram alur penerbitan angka daerah SI-PENA">
                    <defs>
                        <marker id="arrowFlow" markerWidth="9" markerHeight="9" refX="7" refY="4.5" orient="auto">
                            <path d="M0,0 L9,4.5 L0,9 z" fill="#94A3B8"/>
                        </marker>
                    </defs>
                    <line x1="165" y1="70" x2="215" y2="70" stroke="#94A3B8" stroke-width="3" marker-end="url(#arrowFlow)"/>
                    <line x1="365" y1="70" x2="415" y2="70" stroke="#94A3B8" stroke-width="3" marker-end="url(#arrowFlow)"/>
                    <line x1="565" y1="70" x2="615" y2="70" stroke="#94A3B8" stroke-width="3" marker-end="url(#arrowFlow)"/>
                    <line x1="765" y1="70" x2="815" y2="70" stroke="#94A3B8" stroke-width="3" marker-end="url(#arrowFlow)"/>

                    <g class="cursor-pointer" @click="step = 1">
                        <rect x="20" y="35" width="145" height="70" rx="12" :fill="step === 1 ? '#0A3866' : '#F1F5F9'" :stroke="step === 1 ? '#E67E22' : '#E2E8F0'" stroke-width="3"/>
                        <text x="92" y="65" text-anchor="middle" font-size="13" font-weight="800" :fill="step === 1 ? '#FFFFFF' : '#0A3866'">1. INGESTI</text>
                        <text x="92" y="86" text-anchor="middle" font-size="10" :fill="step === 1 ? '#CBD5E1' : '#64748B'">Berkas Excel OPD</text>
                    </g>
                    <g class="cursor-pointer" @click="step = 2">
                        <rect x="220" y="35" width="145" height="70" rx="12" :fill="step === 2 ? '#0A3866' : '#F1F5F9'" :stroke="step === 2 ? '#E67E22' : '#E2E8F0'" stroke-width="3"/>
                        <text x="292" y="65" text-anchor="middle" font-size="13" font-weight="800" :fill="step === 2 ? '#FFFFFF' : '#0A3866'">2. PYTHON</text>
                        <text x="292" y="86" text-anchor="middle" font-size="10" :fill="step === 2 ? '#CBD5E1' : '#64748B'">Bersih + Agregasi</text>
                    </g>
                    <g class="cursor-pointer" @click="step = 3">
                        <rect x="420" y="35" width="145" height="70" rx="12" :fill="step === 3 ? '#0A3866' : '#F1F5F9'" :stroke="step === 3 ? '#E67E22' : '#E2E8F0'" stroke-width="3"/>
                        <text x="492" y="65" text-anchor="middle" font-size="13" font-weight="800" :fill="step === 3 ? '#FFFFFF' : '#0A3866'">3. REDAKSI + QC</text>
                        <text x="492" y="86" text-anchor="middle" font-size="10" :fill="step === 3 ? '#CBD5E1' : '#64748B'">Ulasan + Approval</text>
                    </g>
                    <g class="cursor-pointer" @click="step = 4">
                        <rect x="620" y="35" width="145" height="70" rx="12" :fill="step === 4 ? '#0A3866' : '#F1F5F9'" :stroke="step === 4 ? '#E67E22' : '#E2E8F0'" stroke-width="3"/>
                        <text x="692" y="65" text-anchor="middle" font-size="13" font-weight="800" :fill="step === 4 ? '#FFFFFF' : '#0A3866'">4. TYPST</text>
                        <text x="692" y="86" text-anchor="middle" font-size="10" :fill="step === 4 ? '#CBD5E1' : '#64748B'">Kompilasi PDF 300 DPI</text>
                    </g>
                    <g class="cursor-pointer" @click="step = 5">
                        <rect x="815" y="35" width="75" height="70" rx="12" :fill="step === 5 ? '#E67E22' : '#F1F5F9'" :stroke="step === 5 ? '#0A3866' : '#E2E8F0'" stroke-width="3"/>
                        <text x="852" y="65" text-anchor="middle" font-size="13" font-weight="800" :fill="step === 5 ? '#FFFFFF' : '#64748B'">RILIS</text>
                        <text x="852" y="86" text-anchor="middle" font-size="9" :fill="step === 5 ? '#FFF7ED' : '#94A3B8'">Final</text>
                    </g>

                    <line x1="20" y1="125" x2="890" y2="125" stroke="#E2E8F0" stroke-width="2" stroke-dasharray="6 6"/>
                    <text x="20" y="150" font-size="12" font-weight="700" fill="#0A3866">
                        <tspan x="20" dy="0">Status alur: PENDING_DATA &#8594; DATA_INGESTED &#8594; IN_EDITORIAL &#8594; PENDING_APPROVAL &#8594; APPROVED_LOCKED &#8594; FINAL_RELEASED</tspan>
                        <tspan x="20" dy="22" font-size="11" font-weight="500" fill="#64748B">Transisi di luar peta status DITOLAK state machine &#8212; jejak audit tersimpan pada workflow_logs (SHA-256 versi berkas OPD).</tspan>
                    </text>
                </svg>
            </div>


            <!-- Panel detail langkah terpilih -->
            <div class="mt-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border p-4 text-xs leading-relaxed" :class="step === 1 ? 'border-bps-orange bg-orange-50' : 'border-slate-200 bg-slate-50'">
                    <p class="font-black text-bps-navy uppercase text-[11px] mb-1">1 &middot; Ingesti Data OPD</p>
                    <p class="text-slate-600">Operator OPD mengunggah Excel mentah (Dispendukcapil, Dinas Pendidikan, Pertanian, Dinsos). Setiap berkas dicatat dengan <strong>hash SHA-256</strong> dan nomor versi.</p>
                </div>
                <div class="rounded-xl border p-4 text-xs leading-relaxed" :class="step === 2 ? 'border-bps-orange bg-orange-50' : 'border-slate-200 bg-slate-50'">
                    <p class="font-black text-bps-navy uppercase text-[11px] mb-1">2 &middot; Python Engine</p>
                    <p class="text-slate-600">Worker Python membersihkan <em>merge cells</em>, menormalkan desimal koma/titik, mengagregasi data sekolah, lalu menyiapkan tabel siap simpan ke MySQL 8.</p>
                </div>
                <div class="rounded-xl border p-4 text-xs leading-relaxed" :class="step === 3 ? 'border-bps-orange bg-orange-50' : 'border-slate-200 bg-slate-50'">
                    <p class="font-black text-bps-navy uppercase text-[11px] mb-1">3 &middot; Redaksi &amp; Quality Control</p>
                    <p class="text-slate-600">Editor menyunting ulasan bilingual (ID/EN); Koordinator Publikasi memeriksa integritas tabel vs berkas OPD sebelum <strong>State Locking</strong>.</p>
                </div>
                <div class="rounded-xl border p-4 text-xs leading-relaxed" :class="(step === 4 || step === 5) ? 'border-bps-orange bg-orange-50' : 'border-slate-200 bg-slate-50'">
                    <p class="font-black text-bps-navy uppercase text-[11px] mb-1">4 &middot; Kompilasi &amp; Rilis</p>
                    <p class="text-slate-600">Antrean queue memanggil <code>typst.exe compile</code> untuk 31 KDA secara masal; PDF 300 DPI berfont Roboto siap diunduh dan dirilis.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════ KALENDER RILIS + SHOWCASE COVER ═══════════ -->
    <section id="kalender" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid lg:grid-cols-5 gap-8">
            <!-- Timeline kalender rilis -->
            <div class="lg:col-span-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-bps-orange">Kalender Rilis</span>
                <h2 class="text-2xl font-black text-bps-navy mt-1 mb-1">Siklus Tahunan Penerbitan Angka Resmi</h2>
                <p class="text-xs text-slate-500 mb-6">Kerangka siklus kerja tahunan BPS Kabupaten Jember; tanggal pasti setiap rilis mengikuti Kalender Rilis Data BPS yang ditetapkan tiap tahun.</p>

                <ol class="relative border-l-2 border-bps-navy/20 ml-3 space-y-6">
                    <li class="ml-6 relative">
                        <span class="absolute -left-[31px] top-0 w-5 h-5 rounded-full bg-bps-orange ring-4 ring-orange-100"></span>
                        <p class="text-[11px] font-black uppercase tracking-wide text-bps-orange">Kuartal I</p>
                        <h3 class="font-bold text-sm text-bps-navy">Ingesti Data OPD &amp; Pemutakhiran Basis</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Pengumpulan berkas Excel dinas/OPD, pembersihan oleh Python Engine, dan penguncian basis data tahun terbit.</p>
                    </li>
                    <li class="ml-6 relative">
                        <span class="absolute -left-[31px] top-0 w-5 h-5 rounded-full bg-bps-blue ring-4 ring-sky-100"></span>
                        <p class="text-[11px] font-black uppercase tracking-wide text-bps-blue">Kuartal II</p>
                        <h3 class="font-bold text-sm text-bps-navy">Redaksi Ulasan Bilingual &amp; QC</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Penyuntingan narasi ID/EN seluruh bab, verifikasi integritas tabel, hingga Approve &amp; Lock oleh Koordinator Publikasi.</p>
                    </li>
                    <li class="ml-6 relative">
                        <span class="absolute -left-[31px] top-0 w-5 h-5 rounded-full bg-purple-600 ring-4 ring-purple-100"></span>
                        <p class="text-[11px] font-black uppercase tracking-wide text-purple-700">Kuartal III</p>
                        <h3 class="font-bold text-sm text-bps-navy">Kompilasi Masal 31 KDA + DDA</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Antrean batch Typst merakit buku A5 siap cetak; verifikasi proofing halaman cover, pembatas bab, dan tabel.</p>
                    </li>
                    <li class="ml-6 relative">
                        <span class="absolute -left-[31px] top-0 w-5 h-5 rounded-full bg-emerald-600 ring-4 ring-emerald-100"></span>
                        <p class="text-[11px] font-black uppercase tracking-wide text-emerald-700">Kuartal IV</p>
                        <h3 class="font-bold text-sm text-bps-navy">Rilis Resmi &amp; Analisis SKD</h3>
                        <p class="text-xs text-slate-600 mt-0.5">Publikasi final KDA/DDA diumumkan; Survei Kebutuhan Data diolah menjadi IKK, IPAK, dan diagram kartesius IPA.</p>
                    </li>
                </ol>
            </div>


            <!-- Showcase pratinjau mini cover publikasi unggulan -->
            <div class="lg:col-span-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-bps-orange">Showcase Cover</span>
                <h2 class="text-2xl font-black text-bps-navy mt-1 mb-6">Pratinjau Mini Publikasi Unggulan</h2>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Mini cover KDA -->
                    <div class="aspect-[1/1.414] bg-bps-navy rounded-lg shadow-lg overflow-hidden p-3 flex flex-col justify-between border border-slate-300 hover:shadow-xl hover:-translate-y-1 transition-all">
                        <div class="flex justify-between text-[6px] text-slate-300 font-semibold leading-tight">
                            <span>BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</span>
                            <span class="text-right">Katalog<br>1102001.3509</span>
                        </div>
                        <div>
                            <div class="text-[11px] font-black uppercase leading-tight text-white">Kecamatan Dalam Angka</div>
                            <div class="text-bps-orange font-extrabold text-[10px] mt-1">TAHUN {{ date('Y') }}</div>
                            <div class="mt-2 h-1 w-10 bg-bps-orange rounded"></div>
                        </div>
                        <div class="border-t border-white/20 pt-1.5 text-[6px] text-slate-400">31 Edisi Kecamatan &middot; A5</div>
                    </div>

                    <!-- Mini cover DDA -->
                    <div class="aspect-[1/1.414] bg-bps-darknavy rounded-lg shadow-lg overflow-hidden p-3 flex flex-col justify-between border border-slate-300 hover:shadow-xl hover:-translate-y-1 transition-all">
                        <div class="flex justify-between text-[6px] text-slate-300 font-semibold leading-tight">
                            <span>BADAN PUSAT STATISTIK<br>KABUPATEN JEMBER</span>
                            <span class="text-right">ISSN<br>2548-8120</span>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase leading-tight text-white">Kabupaten Jember Dalam Angka</div>
                            <div class="text-bps-orange font-extrabold text-[10px] mt-1">TAHUN {{ date('Y') }}</div>
                            <div class="mt-2 h-1 w-10 bg-emerald-500 rounded"></div>
                        </div>
                        <div class="border-t border-white/20 pt-1.5 text-[6px] text-slate-400">Publikasi Induk &middot; 13 Bab</div>
                    </div>

                    <!-- Mini cover SKD -->
                    <div class="col-span-2 rounded-xl border border-indigo-200 bg-indigo-50 p-4 flex items-center space-x-4">
                        <span class="shrink-0 w-11 h-11 rounded-lg bg-indigo-600 text-white flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-xs text-indigo-900">Analisis SKD &mdash; Sampul Analisis</p>
                            <p class="text-[11px] text-indigo-700 leading-snug">Diagram kartesius IPA + gauge IKK/IPAK dirender langsung oleh Python visualizers.</p>
                        </div>
                    </div>
                </div>

                <p class="text-[11px] text-slate-400 mt-4 leading-relaxed">
                    Pratinjau bersifat ilustratif tata letak. Cover final dihasilkan engine Typst dari metadata publikasi (nomor katalog, ISSN, volume) &mdash; tanpa angka karangan.
                </p>
            </div>
        </div>
    </section>


    <!-- ═══════════ KONTAK PST BPS JEMBER ═══════════ -->
    <section id="kontak" class="bg-bps-navy text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                @include('partials.bps-logo', ['class' => 'h-14 w-auto bg-white rounded-lg p-2 shadow-lg'])
                <h2 class="text-xl font-black mt-4">Pelayanan Statistik Terpadu</h2>
                <p class="text-sm text-slate-300 mt-1">BPS Kabupaten Jember &mdash; Kode Wilayah 3509</p>
                <p class="text-xs text-slate-400 mt-3 leading-relaxed">
                    Gerbang permintaan data, konsultasi statistik, dan pemesanan publikasi resmi BPS untuk
                    pemerintah daerah, akademisi, media, dan masyarakat Kabupaten Jember.
                </p>
            </div>

            <div class="md:col-span-1 space-y-4 text-sm">
                <h3 class="text-[11px] font-black uppercase tracking-widest text-amber-300">Alamat &amp; Kanal Resmi</h3>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-bps-orange shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-slate-200">Jl. Kalimantan No. 42, Jember 68121<br><span class="text-slate-400 text-xs">Jawa Timur, Indonesia</span></p>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-bps-orange shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <p class="text-slate-200">www.jemberkab.bps.go.id<br><span class="text-slate-400 text-xs">Portal data &amp; publikasi daring BPS Jember</span></p>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-bps-orange shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-slate-200">Jam layanan PST: Senin &ndash; Jumat<br><span class="text-slate-400 text-xs">08.00 &ndash; 16.00 WIB (kecuali hari libur nasional)</span></p>
                </div>
            </div>

            <div class="md:col-span-1 bg-white/10 border border-white/15 rounded-xl p-5 space-y-3">
                <h3 class="text-[11px] font-black uppercase tracking-widest text-amber-300">Siap Bekerja?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Akses SI-PENA dibatasi untuk pegawai BPS Kabupaten Jember melalui jaringan intranet kantor
                    sesuai peran masing-masing (Operator, Editor, Approver, Viewer).
                </p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full bg-bps-orange hover:bg-bps-darkorange text-white font-bold text-sm px-5 py-3 rounded-lg transition-colors shadow-lg">
                    Masuk ke Halaman Autentikasi
                </a>
                <a href="{{ route('portal') }}" class="inline-flex items-center justify-center w-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm px-5 py-3 rounded-lg transition-colors">
                    Muat Ulang Portal
                </a>
            </div>
        </div>

        <!-- Footer resmi -->
        <footer class="border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} <strong class="text-slate-200">SI-PENA</strong> &mdash; Badan Pusat Statistik Kabupaten Jember (3509)</p>
                <p class="font-medium">Laravel 11 &middot; Python Engine &middot; Typst 0.15 &mdash; Server Intranet Offline-Ready</p>
            </div>
        </footer>
    </section>

</body>
</html>

            </div>
