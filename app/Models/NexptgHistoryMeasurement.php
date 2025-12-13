<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexptgHistoryMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'history_id',
        'value',
        'interpretation',
        'substrate_type',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    /**
     * Get the history that owns this measurement.
     */
    public function history(): BelongsTo
    {
        return $this->belongsTo(NexptgHistory::class);
    }
}
