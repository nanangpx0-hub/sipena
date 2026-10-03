<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SI-PENA' }} - BPS Kabupaten Jember</title>
    <!-- Aset lokal (Tailwind, Alpine.js, font) - jalan penuh tanpa internet/intranet -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">

    <!-- Header & Top Navigation -->
    <header class="bg-bps-navy text-white shadow-md border-b-4 border-bps-orange sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding: Logo Resmi BPS vektor + tipografi SI-PENA -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        @include('partials.bps-logo', ['class' => 'h-10 w-auto bg-white/95 rounded-lg p-1 border border-white/20 shadow-inner group-hover:scale-105 transition-transform'])
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-extrabold text-xl tracking-tight text-white">SI-PENA</span>
                                <span class="bg-bps-orange text-white text-[10px] font-bold px-1.5 py-0.5 rounded">3509</span>
                            </div>
                            <p class="text-[11px] text-slate-300 font-medium tracking-wide leading-tight">
                                SI-PENA BPS Kabupaten Jember 3509<br>
                                <span class="text-emerald-300 font-semibold">Goresan Angka Pasti untuk Masa Depan Jember</span>
                            </p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                @php
                    $navUser = Auth::user();
                @endphp
                <nav class="hidden md:flex space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('dashboard*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        Dashboard
                    </a>
                    @if($navUser && $navUser->hasRole(['operator', 'admin']))
                    <a href="{{ route('ingestion.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('ingestion*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        1. Ingesti Data OPD
                    </a>
                    @endif
                    @if($navUser && $navUser->hasRole(['admin']))
                    <a href="{{ route('editorial.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('editorial*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        2. Redaksi Ulasan
                    </a>
                    @endif
                    @if($navUser && $navUser->hasRole('admin'))
                    <a href="{{ route('approval.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('approval*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        3. Quality & Approval
                    </a>
                    @endif
                    <a href="{{ route('compilation.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('compilation*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        4. Kompilasi PDF
                    </a>
                    <a href="{{ route('skd.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('skd*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        Analisis SKD
                    </a>
                    <a href="{{ route('covers.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('covers*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        Cover & Divider
                    </a>
                </nav>

                <!-- Status Badge & Sesi Pengguna -->
                <div class="flex items-center space-x-3">
                    <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" title="Server intranet SI-PENA aktif dan siap menerima request.">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        Server Intranet Aktif
                    </span>

                    @php
                        $navUser = Auth::user();
                        $roleName = $navUser?->role?->name;
                        $roleBadgeClass = match($roleName) {
                            'operator' => 'bg-sky-500/25 text-sky-200 border-sky-400/40',
                            'admin' => 'bg-amber-500/25 text-amber-200 border-amber-400/40',
                            'viewer' => 'bg-purple-500/25 text-purple-200 border-purple-400/40',
                            default => 'bg-white/10 text-slate-200 border-white/20',
                        };
                    @endphp
                    @if($navUser)
                    <span class="hidden lg:inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $roleBadgeClass }}" title="Peran pengguna pada sistem">
                        {{ $navUser->role?->display_name ?? 'Tanpa Peran' }}
                    </span>
                    @endif

                    @auth
                    @if(!empty($availableYears))
                    <form method="POST" action="{{ route('active-year.set') }}" class="flex items-center space-x-1.5">
                        @csrf
                        <label for="active-year" class="hidden lg:block text-[10px] font-bold uppercase tracking-wider text-slate-300">Tahun Terbit</label>
                        <select name="year" id="active-year" onchange="this.form.submit()"
                                class="bg-white/10 border border-white/20 text-white text-xs font-bold rounded-md px-2 py-1.5 focus:border-bps-orange focus:ring focus:ring-bps-orange/40 cursor-pointer">
                            @foreach($availableYears as $yearOption)
                                <option value="{{ $yearOption }}" class="text-slate-800" @selected((int) $activeYear === (int) $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif

                    <div class="flex items-center space-x-2 bg-white/10 rounded-lg pl-2 pr-1 py-1 border border-white/15">
                        <div class="text-right leading-tight hidden sm:block">
                            <p class="text-[11px] font-bold text-white">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-amber-300 font-semibold uppercase tracking-wide">{{ Auth::user()->role?->display_name ?? 'Tanpa Peran' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="text-[11px] font-bold bg-bps-orange hover:bg-bps-darkorange text-white px-2.5 py-1.5 rounded-md transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Stepper Visual Mini 4 Tahapan Alur Publikasi -->
    <div class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
            @php
                $flowSteps = [
                    ['key' => 'ingestion', 'label' => 'Ingesti', 'en' => 'Data OPD', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
                    ['key' => 'editorial', 'label' => 'Redaksi', 'en' => 'Ulasan Bab', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                    ['key' => 'approval', 'label' => 'Quality', 'en' => 'Approval', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['key' => 'compilation', 'label' => 'Kompilasi', 'en' => 'PDF Typst', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ];
            @endphp
            <ol class="flex items-center justify-between sm:justify-start sm:space-x-2 overflow-x-auto">
                @foreach($flowSteps as $i => $step)
                    @php $isActive = request()->routeIs($step['key'].'*') || ($step['key'] === 'compilation' && request()->routeIs('compilation*')); @endphp
                    <li class="flex items-center shrink-0">
                        <span class="inline-flex items-center space-x-2 px-2.5 py-1 rounded-full border text-[11px] font-bold transition-colors
                            {{ $isActive ? 'bg-bps-navy text-white border-bps-navy shadow-sm' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center {{ $isActive ? 'bg-bps-orange text-white' : 'bg-slate-200 text-slate-600' }} text-[9px] font-black">{{ $i + 1 }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/></svg>
                            <span>{{ $step['label'] }}<span class="hidden sm:inline text-slate-400 font-medium"> &middot; {{ $step['en'] }}</span></span>
                        </span>
                        @if(! $loop->last)
                        <svg class="w-4 h-4 mx-1 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <!-- Notification Banners -->
    <div id="flash-banners" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-md shadow-sm flex items-start space-x-3">
                <svg class="h-5 w-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <div class="text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('warning') || session('deadline_warning'))
            <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-md shadow-sm flex items-start space-x-3">
                <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div class="text-sm font-medium text-amber-800">{{ session('warning') ?? session('deadline_warning') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-md shadow-sm flex items-start space-x-3">
                <svg class="h-5 w-5 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L10 10.586l1.293-1.293a1 1 0 101.414 1.414L11.414 11l1.293 1.293a1 1 0 001.414-1.414L12.586 10l1.293-1.293a1 1 0 00-1.414-1.414L11 8.586 9.707 7.293z" clip-rule="evenodd"/></svg>
                <div class="text-sm font-medium text-rose-800">{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-md shadow-sm">
                <div class="text-sm font-bold text-rose-800 mb-1">Formulir belum dapat diproses:</div>
                <ul class="list-disc list-inside text-sm text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Body Container -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 mt-auto py-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div class="text-center md:text-left">
                    &copy; {{ date('Y') }} <strong class="text-bps-navy">Badan Pusat Statistik Kabupaten Jember</strong>
                    &mdash; Kode Wilayah 3509 &middot; Jl. Kalimantan No. 42, Jember 68121
                    <span class="block text-[11px] text-slate-400">SI-PENA (Sistem Penerbitan Angka) &mdash; dikembangkan untuk kebutuhan internal BPS Kabupaten Jember.</span>
                </div>
                <div class="flex items-center space-x-4 text-[11px] font-medium">
                    <a href="{{ route('portal') }}" class="hover:text-bps-navy hover:underline">Portal Gerbang</a>
                    <span class="text-slate-300">&bull;</span>
                    <a href="{{ route('dashboard') }}" class="hover:text-bps-navy hover:underline">Panduan Teknis</a>
                    <span class="text-slate-300">&bull;</span>
                    <a href="{{ route('skd.index') }}" class="hover:text-bps-navy hover:underline">PST BPS Jember</a>
                </div>
            </div>

            <!-- Indikator status engine tri-bahasa.
                 Versi dibaca dari probe berkas biner sungguhan (EngineStatusService),
                 bukan teks hardcode, sehingga selalu sesuai kondisi server. -->
            <div class="flex flex-wrap items-center justify-center md:justify-end gap-2 text-[11px]">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border {{ $engineStatus['laravel']['ok'] ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-slate-200 bg-slate-50 text-slate-600' }} font-semibold"
                      title="Backend HTTP, RBAC, dan transaksi MySQL.">
                    <span class="w-1.5 h-1.5 rounded-full {{ $engineStatus['laravel']['ok'] ? 'bg-rose-500' : 'bg-slate-400' }} mr-1.5"></span>{{ $engineStatus['laravel']['label'] }} &middot; PHP Backend
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border {{ $engineStatus['python']['ok'] && ! $engineStatus['python']['minimum'] ? 'border-sky-200 bg-sky-50 text-sky-700' : 'border-amber-200 bg-amber-50 text-amber-800' }} font-semibold"
                      title="Worker analitis Python. {{ $engineStatus['python']['detail'] }}@if($engineStatus['python']['minimum'] && $engineStatus['python']['ok']) — di bawah minimum {{ \App\Services\EngineStatusService::PYTHON_MINIMUM }} sesuai AGENTS.md. @endif">
                    <span class="w-1.5 h-1.5 rounded-full {{ $engineStatus['python']['ok'] ? ($engineStatus['python']['minimum'] ? 'bg-amber-500' : 'bg-sky-500') : 'bg-slate-400' }} mr-1.5"></span>{{ $engineStatus['python']['label'] }} Engine
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border {{ $engineStatus['typst']['ok'] ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600' }} font-semibold"
                      title="Typesetting engine. Seluruh PDF wajib dikompilasi oleh typst.exe (AGENTS.md §1.2).">
                    <span class="w-1.5 h-1.5 rounded-full {{ $engineStatus['typst']['ok'] ? 'bg-emerald-500' : 'bg-slate-400' }} mr-1.5"></span>{{ $engineStatus['typst']['label'] }} Standalone
                </span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border {{ $engineStatus['offline_ready'] ? 'border-slate-200 bg-slate-50 text-slate-600' : 'border-rose-200 bg-rose-50 text-rose-700' }} font-semibold"
                      title="Seluruh aset (Tailwind, Alpine, font, logo vektor) dilayani lokal; tidak ada referensi CDN eksternal.">
                    <span class="w-1.5 h-1.5 rounded-full {{ $engineStatus['offline_ready'] ? 'bg-slate-400' : 'bg-rose-500' }} mr-1.5"></span>Offline-Ready (Tanpa CDN)
                </span>
            </div>
        </div>
    </footer>

    @stack('scripts')

</body>
</html>
