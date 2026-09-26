<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'chapter_number',
        'table_number',
        'title_id',
        'title_en',
        'source_agency',
        'table_data',
        'is_verified',
    ];

    protected $casts = [
        'table_data' => 'array',
        'is_verified' => 'boolean',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
