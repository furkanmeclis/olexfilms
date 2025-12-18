<?php

namespace App\Listeners\Orders;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderStatusChanged;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandleOrderStatusChanged
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;
        $user = Auth::user();

        DB::transaction(function () use ($order, $oldStatus, $newStatus, $user) {
            // Siparişe bağlı tüm stokları al
            $order->loadMissing('items.stockItems');
            $allStockItems = $order->items->pluck('stockItems')->flatten()->unique('id');

            // Status'a göre stok yönetimi
            if ($newStatus === OrderStatusEnum::PROCESSING) {
                // Processing: Stokları RESERVED yap, merkeze ait tut
                foreach ($allStockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::RESERVED->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);

                    // Movement logu oluştur (eğer daha önce oluşturulmamışsa)
                    $existingMovement = StockMovement::where('stock_item_id', $stockItem->id)
                        ->where('action', StockMovementActionEnum::TRANSFERRED_TO_DEALER->value)
                        ->where('description', 'like', "%Sipariş #{$order->id}%")
                        ->first();

                    if (!$existingMovement) {
                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'user_id' => $user?->id ?? $order->created_by,
                            'action' => StockMovementActionEnum::TRANSFERRED_TO_DEALER->value,
                            'description' => "Sipariş #{$order->id} ile {$order->dealer->name} bayisine yollandı",
                            'created_at' => now(),
                        ]);
                    }
                }
            } elseif ($newStatus === OrderStatusEnum::SHIPPED) {
                // Shipped: Stokları RESERVED tut, merkeze ait tut
                foreach ($allStockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::RESERVED->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);
                }
            } elseif ($newStatus === OrderStatusEnum::DELIVERED) {
                // Delivered: Stokları dealer'a transfer et ve AVAILABLE yap
                foreach ($allStockItems as $stockItem) {
                    $stockItem->update([
                        'dealer_id' => $order->dealer_id,
                        'location' => StockLocationEnum::DEALER->value,
                        'status' => StockStatusEnum::AVAILABLE->value,
                    ]);

                    // RECEIVED movement logu oluştur (eğer daha önce oluşturulmamışsa)
                    $existingMovement = StockMovement::where('stock_item_id', $stockItem->id)
                        ->where('action', StockMovementActionEnum::RECEIVED->value)
                        ->where('description', 'like', "%Sipariş #{$order->id}%")
                        ->first();

                    if (!$existingMovement) {
                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'user_id' => $user?->id ?? $order->created_by,
                            'action' => StockMovementActionEnum::RECEIVED->value,
                            'description' => "Sipariş #{$order->id} {$order->dealer->name} bayisine teslim edildi",
                            'created_at' => now(),
                        ]);
                    }
                }
            } elseif ($newStatus === OrderStatusEnum::CANCELLED) {
                // Cancelled: Stokları merkeze geri döndür ve AVAILABLE yap
                foreach ($allStockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);
                }
            } elseif ($newStatus === OrderStatusEnum::PENDING && $oldStatus !== OrderStatusEnum::PENDING) {
                // Pending'e geri dönüldüyse: Stokları merkeze geri döndür ve AVAILABLE yap
                foreach ($allStockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);
                }
            }
        });
    }
}

