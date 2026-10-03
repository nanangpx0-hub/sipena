<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'role_id',
        'role_en',
        'names',
        'sort_order',
    ];

    protected $casts = [
        'names' => 'array',
        'sort_order' => 'integer',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
