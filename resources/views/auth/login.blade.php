<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SI-PENA - BPS Kabupaten Jember</title>
    <!-- Aset lokal (Tailwind, font) - jalan penuh tanpa internet/intranet -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        <!-- Branding -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-bps-navy border-4 border-bps-orange shadow-lg mb-3">
                <span class="text-3xl font-black text-bps-orange">P</span>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-bps-navy">SI-PENA</h1>
            <p class="text-xs font-medium text-slate-500 mt-1">Sistem Penerbitan Angka &mdash; BPS Kabupaten Jember (3509)</p>
            <p class="text-[11px] italic text-slate-400">Goresan Angka Pasti untuk Masa Depan Jember.</p>
        </div>

        <!-- Card Login -->
        <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
            <div class="bg-bps-navy px-6 py-4 border-b-4 border-bps-orange">
                <h2 class="text-white font-bold text-sm">Masuk ke Sistem</h2>
                <p class="text-slate-300 text-[11px] mt-0.5">Gunakan akun resmi sesuai peran (Operator, Editor, Approver, Viewer).</p>
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
                           class="w-full rounded-md border-slate-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-bps-blue focus:border-bps-blue">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-md border-slate-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-bps-blue focus:border-bps-blue">
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

        <p class="text-center text-[11px] text-slate-400 mt-4">
            Akses terbatas untuk pegawai BPS Kabupaten Jember &middot; Jaringan Intranet Kantor
        </p>
    </div>

</body>
</html>
