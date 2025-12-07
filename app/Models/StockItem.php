<?php

namespace App\Models;

use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'dealer_id',
        'sku',
        'barcode',
        'location',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'location' => StockLocationEnum::class,
            'status' => StockStatusEnum::class,
        ];
    }

    /**
     * Get the product that owns the stock item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the dealer that owns the stock item.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the stock movements for the stock item.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the order items that use this stock item.
     */
    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_stock');
    }

    /**
     * Get the service items that use this stock item.
     */
    public function serviceItems(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }

    /**
     * Get the warranties for this stock item.
     */
    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    /**
     * Scope a query to only include available stock items.
     */
    #[Scope]
    public function scopeAvailable(Builder $query): void
    {
        $query->where('status', StockStatusEnum::AVAILABLE->value);
    }

    /**
     * Scope a query to only include reserved stock items.
     */
    #[Scope]
    public function scopeReserved(Builder $query): void
    {
        $query->where('status', StockStatusEnum::RESERVED->value);
    }

    /**
     * Scope a query to only include stock items at a specific location.
     */
    public function scopeAtLocation(Builder $query, StockLocationEnum $location): void
    {
        $query->where('location', $location->value);
    }
}
