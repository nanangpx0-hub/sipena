<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SI-PENA - BPS Kabupaten Jember</title>
    <!-- Aset lokal (Tailwind, Alpine, font) - jalan penuh tanpa internet/intranet -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen">

    <!-- ═══════════ LAYOUT SPLIT-SCREEN ═══════════ -->
    <div class="min-h-screen lg:grid lg:grid-cols-2">

        <!-- ─────────── SISI KIRI: FORM AUTENTIKASI ─────────── -->
        <div class="flex items-center justify-center px-4 sm:px-8 py-10 bg-slate-50">
            <div class="w-full max-w-md space-y-6">

                <!-- Branding ringkas -->
                <a href="{{ route('portal') }}" class="flex items-center space-x-3 group w-fit">
                    @include('partials.bps-logo', ['class' => 'h-11 w-auto group-hover:scale-105 transition-transform'])
                    <div class="leading-tight">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-extrabold tracking-tight text-bps-navy">SI-PENA</span>
                            <span class="bg-bps-orange text-white text-[10px] font-bold px-1.5 py-0.5 rounded">BPS 3509</span>
                        </div>
                        <p class="text-xs font-medium text-slate-500">Sistem Penerbitan Angka &mdash; BPS Kabupaten Jember</p>
                    </div>
                </a>

                <!-- Card Login -->
                <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
                    <div class="bg-bps-navy px-6 py-4 border-b-4 border-bps-orange">
                        <h1 class="text-white font-bold text-base">Masuk ke Sistem</h1>
                        <p class="text-slate-300 text-[11px] mt-0.5">Gunakan akun resmi sesuai peran (Operator, Editor, Approver/QC, Viewer).</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="px-6 py-6 space-y-4">
                        @csrf

                        @if ($errors->any())
                            <div class="bg-rose-50 border-l-4 border-rose-500 rounded-r-md p-3 text-sm text-rose-800">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded-r-md p-3 text-sm text-emerald-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-600 mb-1">Email Dinas</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                   placeholder="nama@bps3509.go.id"
                                   class="w-full rounded-md border-slate-300 border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-bps-blue focus:border-bps-blue">
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi</label>
                            <input id="password" name="password" type="password" required
                                   class="w-full rounded-md border-slate-300 border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-bps-blue focus:border-bps-blue">
                        </div>

                        <label class="flex items-center space-x-2 text-xs text-slate-600 select-none">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-bps-blue focus:ring-bps-blue">
                            <span>Biarkan saya tetap masuk di perangkat ini</span>
                        </label>

                        <button type="submit"
                                class="w-full bg-bps-orange hover:bg-bps-darkorange text-white font-bold text-sm py-2.5 rounded-md transition-colors shadow-sm">
                            MASUK SISTEM
                        </button>
                    </form>
                </div>

                <p class="text-center text-[11px] text-slate-400">
                    Akses terbatas untuk pegawai BPS Kabupaten Jember &middot; Jaringan Intranet Kantor
                </p>
            </div>
        </div>
<!-- ─────────── SISI KANAN: PANEL BRANDING NAVY (#0A3866) ─────────── -->
        <div class="hidden lg:flex relative overflow-hidden bg-bps-navy text-white flex-col justify-between p-10">
            <!-- Ornamen latar vektor -->
            <svg class="absolute inset-0 w-full h-full opacity-[0.08]" aria-hidden="true" viewBox="0 0 600 800" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <pattern id="loginGrid" width="36" height="36" patternUnits="userSpaceOnUse">
                        <path d="M36 0H0v36" fill="none" stroke="#ffffff" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="600" height="800" fill="url(#loginGrid)"/>
                <g fill="#E67E22" opacity="0.9">
                    <rect x="470" y="620" width="24" height="80" rx="3"/>
                    <rect x="505" y="560" width="24" height="140" rx="3"/>
                    <rect x="540" y="500" width="24" height="200" rx="3"/>
                </g>
                <circle cx="90" cy="120" r="70" fill="none" stroke="#ffffff" stroke-width="10" opacity="0.35"/>
                <path d="M90 60 a60 60 0 0 1 60 60" fill="none" stroke="#E67E22" stroke-width="10"/>
            </svg>

            <div class="relative">
                <!-- Logo + identitas -->
                <div class="flex items-center space-x-4">
                    @include('partials.bps-logo', ['class' => 'h-16 w-auto bg-white rounded-xl p-2 shadow-xl'])
                    <div class="leading-tight">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-amber-300">Badan Pusat Statistik</p>
                        <p class="text-xl font-black">Kabupaten Jember</p>
                        <p class="text-xs text-slate-300">Kode Wilayah 3509 &middot; Provinsi Jawa Timur</p>
                    </div>
                </div>

                <!-- Badge sertifikasi integritas data -->
                <div class="mt-6 inline-flex items-center space-x-2 bg-emerald-500/15 border border-emerald-400/40 rounded-full px-4 py-1.5">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span class="text-xs font-bold text-emerald-200">Badge Integritas Data &mdash; Audit SHA-256 Aktif</span>
<!-- Panduan 4 Peran Sistem -->
                <div class="mt-7">
                    <h2 class="text-[11px] font-black uppercase tracking-widest text-amber-300 mb-3">Panduan 4 Peran Sistem</h2>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-white/10 border border-white/15 rounded-lg p-3">
                            <p class="font-bold text-white flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-sky-400"></span><span>1. Operator OPD</span>
                            </p>
                            <p class="text-slate-300 mt-1 leading-snug">Mengunggah berkas Excel mentah dinas &amp; memetakan kolom sumber.</p>
                        </div>
                        <div class="bg-white/10 border border-white/15 rounded-lg p-3">
                            <p class="font-bold text-white flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span><span>2. Editor Bahasa</span>
                            </p>
                            <p class="text-slate-300 mt-1 leading-snug">Menyunting ulasan bab bilingual (Indonesia &amp; Inggris).</p>
                        </div>
                        <div class="bg-white/10 border border-white/15 rounded-lg p-3">
                            <p class="font-bold text-white flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span><span>3. Approver / QC</span>
                            </p>
                            <p class="text-slate-300 mt-1 leading-snug">Koordinator Publikasi: verifikasi mutu &amp; State Locking.</p>
                        </div>
                        <div class="bg-white/10 border border-white/15 rounded-lg p-3">
                            <p class="font-bold text-white flex items-center space-x-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-400"></span><span>4. Viewer / Pimpinan</span>
                            </p>
                            <p class="text-slate-300 mt-1 leading-snug">Akses baca: pantau progres rilis &amp; unduh draf publikasi.</p>
                        </div>
                    </div>
                </div>
<!-- Mini-widget statistik status rilis tahun aktif -->
                <div class="mt-6 bg-black/25 border border-white/15 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[11px] font-black uppercase tracking-widest text-amber-300">Status Rilis Tahun Aktif</h3>
                        <span class="text-[11px] font-bold bg-white/10 px-2 py-0.5 rounded text-slate-200">{{ $activeYear ?? '—' }}</span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-white/10 rounded-lg py-2">
                            <p class="text-lg font-black text-white">{{ (int) ($totalPubs ?? 0) }}</p>
                            <p class="text-[10px] text-slate-300 uppercase tracking-wide">Total Publikasi</p>
                        </div>
                        <div class="bg-white/10 rounded-lg py-2">
                            <p class="text-lg font-black text-emerald-300">{{ (int) ($releasedCount ?? 0) }}</p>
                            <p class="text-[10px] text-slate-300 uppercase tracking-wide">Final Rilis</p>
                        </div>
                        <div class="bg-white/10 rounded-lg py-2">
                            <p class="text-lg font-black text-purple-300">{{ (int) ($lockedCount ?? 0) }}</p>
                            <p class="text-[10px] text-slate-300 uppercase tracking-wide">Terkunci</p>
                        </div>
                    </div>
                    @if((int) ($totalPubs ?? 0) > 0)
                    @php $releasedPct = (int) (($releasedCount ?? 0) * 100 / max(1, (int) $totalPubs)); @endphp
                    <div class="mt-3">
                        <div class="flex justify-between text-[10px] text-slate-300 mb-1">
                            <span>Progres rilis final</span><span>{{ $releasedPct }}%</span>
                        </div>
                        <div class="h-2 bg-white/15 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $releasedPct }}%"></div>
                        </div>
                    </div>
                    @else
                    <p class="mt-3 text-[11px] text-slate-400 italic">Belum ada data publikasi untuk tahun aktif.</p>
                    @endif
                </div>

                <!-- Maklumat kepatuhan kerahasiaan -->
                <p class="mt-6 text-[11px] leading-relaxed text-slate-400 border-t border-white/10 pt-4">
                    <strong class="text-slate-200 uppercase tracking-wide">Maklumat Kerahasiaan:</strong>
                    Seluruh data bersifat rahasia dan hanya dapat diakses sesuai kewenangan peran.
                    Kebijakan pengelolaan statistik mengacu pada
                    <strong class="text-amber-300">Undang-Undang Nomor 16 Tahun 1997 tentang Statistik</strong>.
                </p>
            </div>

            <p class="relative text-[11px] text-slate-500">&copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Jember (3509)</p>
        </div>
    </div>

</body>
</html>
                </div>
