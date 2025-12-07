<?php

namespace App\Models;

use App\Enums\StockMovementActionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'stock_item_id',
        'user_id',
        'action',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => StockMovementActionEnum::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the stock item for the movement.
     */
    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
