<?php

namespace App\Http\Controllers;

use App\Support\ActiveYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Pengganti Tahun Aktif dari dropdown header (sesi multi-tahun).
 */
class ActiveYearController extends Controller
{
    public function store(Request $request)
    {
        $available = ActiveYear::availableYears();

        $request->validate([
            'year' => ['required', 'integer', Rule::in($available)],
        ], [
            'year.in' => 'Tahun terbit tersebut belum terdaftar di SI-PENA.',
        ]);

        $year = (int) $request->integer('year');
        ActiveYear::set($year);

        return redirect()->back()->with('success', "Tahun terbit aktif diganti ke {$year}.");
    }
}
