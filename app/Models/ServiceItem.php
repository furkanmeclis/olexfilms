<?php

namespace App\Models;

use App\Enums\ServiceItemUsageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'stock_item_id',
        'usage_type',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'usage_type' => ServiceItemUsageTypeEnum::class,
        ];
    }

    /**
     * Get the service that owns this service item.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the stock item for this service item.
     */
    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }
}
