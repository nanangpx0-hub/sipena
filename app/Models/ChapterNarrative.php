<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterNarrative extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'chapter_number',
        'title_id',
        'title_en',
        'narrative_id',
        'narrative_en',
        'highlight_label',
        'highlight_value',
        'last_edited_by',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
