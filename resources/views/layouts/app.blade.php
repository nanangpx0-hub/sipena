<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SI-PENA' }} - BPS Kabupaten Jember</title>
    <!-- Aset lokal (Tailwind, Alpine.js, font) - jalan penuh tanpa internet/intranet -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col">

    <!-- Header & Top Navigation -->
    <header class="bg-bps-navy text-white shadow-md border-b-4 border-bps-orange sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Branding -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center font-black text-xl text-bps-orange border border-white/20 shadow-inner group-hover:scale-105 transition-transform">
                            P
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-extrabold text-xl tracking-tight text-white">SI-PENA</span>
                                <span class="bg-bps-orange text-white text-[10px] font-bold px-1.5 py-0.5 rounded">BPS 3509</span>
                            </div>
                            <p class="text-[11px] text-slate-300 font-medium tracking-wide">Goresan Angka Pasti untuk Masa Depan Jember</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                @php($navUser = Auth::user())
                <nav class="hidden md:flex space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('dashboard*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        Dashboard
                    </a>
                    @if($navUser && $navUser->hasRole(['operator', 'approver']))
                    <a href="{{ route('ingestion.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('ingestion*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        1. Ingesti Data OPD
                    </a>
                    @endif
                    @if($navUser && $navUser->hasRole(['editor', 'approver']))
                    <a href="{{ route('editorial.index') }}" class="px-3 py-2 rounded-md text-xs font-semibold {{ request()->routeIs('editorial*') ? 'bg-white/15 text-white' : 'text-slate-200 hover:bg-white/10' }}">
                        2. Redaksi Ulasan
                    </a>
                    @endif
                    @if($navUser && $navUser->hasRole('approver'))
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
                    <span class="hidden lg:inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        Server Intranet Aktif
                    </span>

                    @auth
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

    <!-- Notification Banners -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
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
    <footer class="bg-white border-t border-slate-200 mt-auto py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                &copy; {{ date('Y') }} <strong>SI-PENA</strong> — Badan Pusat Statistik Kabupaten Jember (Kode Wilayah 3509).
            </div>
            <div class="flex items-center space-x-4">
                <span>Laravel 11</span>
                <span>•</span>
                <span>Python 3.10 Engine</span>
                <span>•</span>
                <span>Typst 0.15 Standalone</span>
            </div>
        </div>
    </footer>

</body>
</html>
