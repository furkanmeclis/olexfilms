<?php

namespace App\Listeners\Orders;

use App\Enums\StockLocationEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderCancelled;
use Illuminate\Support\Facades\DB;

class HandleOrderCancellation
{
    /**
     * Handle the event.
     */
    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        DB::transaction(function () use ($order) {
            // Siparişe bağlı tüm stokları al
            $order->loadMissing('items.stockItems');
            $allStockItems = $order->items->pluck('stockItems')->flatten()->unique('id');

            // Tüm stokları merkeze geri döndür ve AVAILABLE yap
            foreach ($allStockItems as $stockItem) {
                $stockItem->update([
                    'status' => StockStatusEnum::AVAILABLE->value,
                    'location' => StockLocationEnum::CENTER->value,
                    'dealer_id' => null, // Merkeze ait
                ]);
            }

            // OrderItem'lardan stok atamalarını kaldır
            foreach ($order->items as $orderItem) {
                $orderItem->stockItems()->detach();
            }
        });
    }
}
