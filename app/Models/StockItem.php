<?php

namespace App\Models;

use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    /**
     * StockItem'ın sahipliğini transfer et (hak transferi)
     *
     * @param int|null $newDealerId Yeni bayi ID'si (null ise merkeze transfer)
     * @param int|null $userId İşlemi yapan kullanıcı ID'si
     * @param string|null $description Açıklama (opsiyonel)
     * @return bool
     */
    public function transferOwnership(?int $newDealerId, ?int $userId = null, ?string $description = null): bool
    {
        return DB::transaction(function () use ($newDealerId, $userId, $description) {
            $oldDealerId = $this->dealer_id;

            // Eğer aynı bayiye transfer ediliyorsa işlem yapma
            if ($oldDealerId === $newDealerId) {
                return false;
            }

            // Yeni dealer ve location belirle
            $newLocation = $newDealerId ? StockLocationEnum::DEALER : StockLocationEnum::CENTER;

            // StockItem'ı güncelle
            $this->update([
                'dealer_id' => $newDealerId,
                'location' => $newLocation->value,
            ]);

            // StockMovement logu oluştur
            $action = $newDealerId 
                ? StockMovementActionEnum::TRANSFERRED_TO_DEALER 
                : StockMovementActionEnum::IMPORTED;

            $dealerName = $this->dealer ? $this->dealer->name : 'Merkez';
            $oldDealerName = $oldDealerId ? (Dealer::find($oldDealerId)?->name ?? 'Bilinmeyen') : 'Merkez';

            $movementDescription = $description ?? (
                $newDealerId
                    ? "Sahiplik {$oldDealerName} bayisinden {$dealerName} bayisine transfer edildi"
                    : "Sahiplik {$oldDealerName} bayisinden Merkeze transfer edildi"
            );

            StockMovement::create([
                'stock_item_id' => $this->id,
                'user_id' => $userId,
                'action' => $action->value,
                'description' => $movementDescription,
                'created_at' => now(),
            ]);

            return true;
        });
    }
}
