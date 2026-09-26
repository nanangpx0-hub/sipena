<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'bps_code',
        'name',
        'is_kelurahan',
        'area_sqm',
    ];

    protected $casts = [
        'is_kelurahan' => 'boolean',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
