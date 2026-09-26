<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisualAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'asset_type',
        'chapter_number',
        'mode',
        'file_path',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
