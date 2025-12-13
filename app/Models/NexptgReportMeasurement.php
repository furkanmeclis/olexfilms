<?php

namespace App\Models;

use App\Enums\NexptgPartTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexptgReportMeasurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'is_inside',
        'place_id',
        'part_type',
        'value',
        'interpretation',
        'substrate_type',
        'timestamp',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_inside' => 'boolean',
            'value' => 'decimal:2',
            'timestamp' => 'datetime',
            'part_type' => NexptgPartTypeEnum::class,
        ];
    }

    /**
     * Get the report that owns this measurement.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(NexptgReport::class, 'report_id');
    }

    /**
     * Scope a query to only include external measurements.
     */
    #[Scope]
    public function scopeExternal(Builder $query): void
    {
        $query->where('is_inside', false);
    }

    /**
     * Scope a query to only include internal measurements.
     */
    #[Scope]
    public function scopeInternal(Builder $query): void
    {
        $query->where('is_inside', true);
    }
}
