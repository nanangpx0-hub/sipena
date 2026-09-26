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

    public function publication(): HasOne
    {
        return $this->hasOne(Publication::class);
    }
}
