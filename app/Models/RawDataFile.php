<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RawDataFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'publication_id',
        'opd_source_name',
        'original_filename',
        'storage_path',
        'file_hash_sha256',
        'version_number',
        'uploaded_by',
        'notes',
        'status',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
