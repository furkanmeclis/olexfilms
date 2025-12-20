<?php

namespace App\Models;

use App\Enums\ServiceReportMatchTypeEnum;
use App\Enums\ServiceStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_no',
        'dealer_id',
        'customer_id',
        'user_id',
        'car_brand_id',
        'car_model_id',
        'year',
        'vin',
        'plate',
        'km',
        'package',
        'applied_parts',
        'notes',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'applied_parts' => 'array',
            'status' => ServiceStatusEnum::class,
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the dealer that owns the service.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the customer for this service.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created the service.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the car brand for this service.
     */
    public function carBrand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    /**
     * Get the car model for this service.
     */
    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }

    /**
     * Get the service items for this service.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Get the warranties for this service.
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    } 

    /**
     * Get the images for this service.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderBy('order');
    }

    /**
     * Get all Nexptg reports for this service.
     */
    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(NexptgReport::class, 'service_nexptg_report')
            ->withPivot('match_type')
            ->withTimestamps();
    }

    /**
     * Get before service reports.
     */
    public function beforeReports(): BelongsToMany
    {
        return $this->belongsToMany(NexptgReport::class, 'service_nexptg_report')
            ->wherePivot('match_type', ServiceReportMatchTypeEnum::BEFORE->value)
            ->withPivot('match_type')
            ->withTimestamps();
    }

    /**
     * Get after service reports.
     */
    public function afterReports(): BelongsToMany
    {
        return $this->belongsToMany(NexptgReport::class, 'service_nexptg_report')
            ->wherePivot('match_type', ServiceReportMatchTypeEnum::AFTER->value)
            ->withPivot('match_type')
            ->withTimestamps();
    }

    /**
     * Get all status logs for this service.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ServiceStatusLog::class)->orderBy('created_at', 'desc');
    }
}
