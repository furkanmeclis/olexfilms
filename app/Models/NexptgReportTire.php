<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexptgReportTire extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'width',
        'profile',
        'diameter',
        'maker',
        'season',
        'section',
        'value1',
        'value2',
    ];

    protected function casts(): array
    {
        return [
            'value1' => 'decimal:2',
            'value2' => 'decimal:2',
        ];
    }

    /**
     * Get the report that owns this tire.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(NexptgReport::class, 'report_id');
    }
}
