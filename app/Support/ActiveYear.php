<?php

namespace App\Support;

use App\Models\Publication;

/**
 * Sesi Tahun Aktif SI-PENA (multi-tahun).
 *
 * Seluruh modul membaca tahun dari sini sehingga satu sesi pengguna selalu
 * bekerja pada satu tahun terbit saja (mis. 2026 vs 2027).
 */
class ActiveYear
{
    public const SESSION_KEY = 'active_year';

    /**
     * Tahun aktif sesi berjalan. Null hanya bila database sama sekali kosong.
     *
     * Bila pengguna belum pernah memilih tahun, gunakan tahun terakhir yang
     * benar-benar dikerjakan (statusnya sudah bergerak keluar PENDING_DATA),
     * bukan sekadar tahun dengan master baru dibuat — agar inisialisasi tahun
     * berikutnya tidak otomatis menarik seluruh UI ke tahun yang belum ada isinya.
     */
    public static function get(): ?int
    {
        $sessionYear = session(self::SESSION_KEY);
        if ($sessionYear !== null) {
            return (int) $sessionYear;
        }

        $inProgress = Publication::where('status', '!=', 'PENDING_DATA')->max('year');
        if ($inProgress !== null) {
            return (int) $inProgress;
        }

        $latest = Publication::max('year');

        return $latest === null ? null : (int) $latest;
    }

    public static function set(int $year): void
    {
        session([self::SESSION_KEY => $year]);
    }

    /**
     * Daftar tahun terbit yang tersedia di database (terurut menurun).
     *
     * @return array<int, int>
     */
    public static function availableYears(): array
    {
        return Publication::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->all();
    }
}
