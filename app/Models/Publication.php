<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends Model
{
    use HasFactory;

    /**
     * Peta transisi status alur kerja (State Machine) sesuai PRD §4.
     * Transisi di luar peta ini DITOLAK oleh sistem.
     */
    public const ALLOWED_TRANSITIONS = [
        'PENDING_DATA' => ['DATA_INGESTED'],
        'DATA_INGESTED' => ['IN_EDITORIAL', 'PENDING_APPROVAL'],
        'IN_EDITORIAL' => ['PENDING_APPROVAL'],
        'PENDING_APPROVAL' => ['APPROVED_LOCKED', 'IN_EDITORIAL'],
        'APPROVED_LOCKED' => ['FINAL_RELEASED', 'IN_EDITORIAL'],
        'FINAL_RELEASED' => ['IN_EDITORIAL'],
    ];

    protected $fillable = [
        'type',
        'district_id',
        'year',
        'title',
        'catalog_number',
        'publication_number',
        'issn',
        'volume',
        'book_size',
        'status',
        'soft_deadline',
        'hard_deadline',
    ];

    protected $casts = [
        'soft_deadline' => 'datetime',
        'hard_deadline' => 'datetime',
        'year' => 'integer',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function rawDataFiles(): HasMany
    {
        return $this->hasMany(RawDataFile::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(PublicationTable::class);
    }

    public function narratives(): HasMany
    {
        return $this->hasMany(ChapterNarrative::class);
    }

    public function visualAssets(): HasMany
    {
        return $this->hasMany(VisualAsset::class);
    }

    public function workflowLogs(): HasMany
    {
        return $this->hasMany(WorkflowLog::class);
    }

    /**
     * Status terkunci permanen: bab tidak boleh diedit tanpa membuka kunci.
     */
    public function isLocked(): bool
    {
        return in_array($this->status, ['APPROVED_LOCKED', 'FINAL_RELEASED'], true);
    }

    /**
     * Apakah transisi dari status saat ini ke $target diizinkan state machine?
     */
    public function canTransitionTo(string $target): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        return in_array($target, $allowed, true);
    }

    /**
     * Jalankan transisi status + tulis jejak audit workflow_logs.
     * Mengembalikan false (tanpa perubahan) bila transisi tidak diizinkan.
     */
    public function transitionTo(string $target, ?int $userId, string $remarks = ''): bool
    {
        if (!$this->canTransitionTo($target)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->update(['status' => $target]);

        WorkflowLog::create([
            'publication_id' => $this->id,
            'from_status' => $oldStatus,
            'to_status' => $target,
            'user_id' => $userId ?? ($this->getConnection()->table('users')->value('id') ?? 1),
            'remarks' => $remarks !== '' ? $remarks : "Status berubah dari {$oldStatus} menjadi {$target}.",
        ]);

        return true;
    }
}
