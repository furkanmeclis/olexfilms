<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'created_by',
        'status',
        'cargo_company',
        'tracking_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
        ];
    }

    /**
     * Get the dealer that owns the order.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the user who created the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include pending orders.
     */
    #[Scope]
    public function scopePending(Builder $query): void
    {
        $query->where('status', OrderStatusEnum::PENDING->value);
    }

    /**
     * Scope a query to only include processing orders.
     */
    #[Scope]
    public function scopeProcessing(Builder $query): void
    {
        $query->where('status', OrderStatusEnum::PROCESSING->value);
    }

    /**
     * Scope a query to only include shipped orders.
     */
    #[Scope]
    public function scopeShipped(Builder $query): void
    {
        $query->where('status', OrderStatusEnum::SHIPPED->value);
    }

    /**
     * Scope a query to only include delivered orders.
     */
    #[Scope]
    public function scopeDelivered(Builder $query): void
    {
        $query->where('status', OrderStatusEnum::DELIVERED->value);
    }
}
