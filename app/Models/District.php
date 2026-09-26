<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'bps_code',
        'name',
        'capital_city',
        'altitude_min',
        'altitude_max',
        'total_area_sqkm',
    ];

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    /**
     * Seluruh publikasi yang menempel pada kecamatan ini (multi-tahun).
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Publikasi KDA pada tahun tertentu; null bila tahun itu belum diinisialisasi.
     */
    public function publicationForYear(int $year): ?Publication
    {
        return $this->publications()
            ->where('type', 'KDA')
            ->where('year', $year)
            ->first();
    }

    /**
     * Backward-compatible: publikasi KDA tahun terbaru milik kecamatan ini.
     */
    public function publication(): HasOne
    {
        return $this->hasOne(Publication::class)
            ->where('type', 'KDA')
            ->latestOfMany('year');
    }
}
