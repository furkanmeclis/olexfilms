<?php

namespace App\Listeners\Orders;

use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderItemDeleted;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class HandleOrderItemDeletion
{
    /**
     * Handle the event.
     */
    public function handle(OrderItemDeleted $event): void
    {
        $orderItem = $event->orderItem;

        DB::transaction(function () use ($orderItem) {
            // OrderItem'a bağlı tüm stokları al
            $stockItems = $orderItem->stockItems;

            // Stokları serbest bırak (eğer CENTER'da ve RESERVED ise)
            // Merkeze ait stokları AVAILABLE yap
            foreach ($stockItems as $stockItem) {
                if ($stockItem->location->value === StockLocationEnum::CENTER->value 
                    && $stockItem->status->value === StockStatusEnum::RESERVED->value) {
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                        'dealer_id' => null, // Merkeze ait olduğundan emin ol
                    ]);
                }
            }

            // Stok atamalarını kaldır
            $orderItem->stockItems()->detach();
        });
    }
}

