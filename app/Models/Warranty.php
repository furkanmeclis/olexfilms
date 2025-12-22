<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'stock_item_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the service that owns this warranty.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the stock item for this warranty.
     */
    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    /**
     * Get the total number of days in the warranty period.
     */
    public function getTotalDaysAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    /**
     * Get the number of days remaining until warranty expires.
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $now = now()->startOfDay();
        $endDate = $this->end_date->startOfDay();

        return $now->diffInDays($endDate, false);
    }

    /**
     * Check if the warranty has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->startOfDay()->greaterThan($this->end_date->startOfDay());
    }

    /**
     * Scope a query to only include active warranties.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include expired warranties.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('end_date', '<', now()->startOfDay());
    }

    /**
     * Scope a query to only include warranties expiring soon (within 30 days).
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        $today = now()->startOfDay();
        $futureDate = $today->copy()->addDays($days);

        return $query->where('end_date', '>=', $today)
            ->where('end_date', '<=', $futureDate)
            ->where('is_active', true);
    }
}
