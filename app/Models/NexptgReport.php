<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexptgReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_user_id',
        'external_id',
        'name',
        'date',
        'calibration_date',
        'device_serial_number',
        'model',
        'brand',
        'type_of_body',
        'capacity',
        'power',
        'vin',
        'fuel_type',
        'year',
        'unit_of_measure',
        'extra_fields',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'calibration_date' => 'datetime',
            'extra_fields' => 'array',
        ];
    }

    /**
     * Get the API user that owns this report.
     */
    public function apiUser(): BelongsTo
    {
        return $this->belongsTo(NexptgApiUser::class, 'api_user_id');
    }

    /**
     * Get all measurements for this report.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(NexptgReportMeasurement::class, 'report_id');
    }

    /**
     * Get all tires for this report.
     */
    public function tires(): HasMany
    {
        return $this->hasMany(NexptgReportTire::class, 'report_id');
    }
}
