<?php

namespace App\Listeners\Orders;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderItemCreated;
use App\Events\Orders\OrderItemUpdated;
use App\Models\StockItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandleOrderItemStockAssignment
{
    /**
     * Handle OrderItem created event.
     */
    public function handleOrderItemCreated(OrderItemCreated $event): void
    {
        $orderItem = $event->orderItem;
        // Order'ı refresh et ki status güncel olsun
        $order = $orderItem->order()->first();
        $user = Auth::user();

        // Stoklar zaten attach edilmiş olmalı (form'dan geliyor)
        // Sadece status güncellemesi yap
        $order->loadMissing('items.stockItems');
        
        DB::transaction(function () use ($orderItem, $order, $user) {
            // OrderItem'ı refresh et ki stockItems yüklü olsun
            $orderItem->refresh();
            $stockItems = $orderItem->stockItems;

            // Order status'unu kontrol et (enum'a cast edilmiş olmalı)
            $orderStatus = $order->status instanceof OrderStatusEnum 
                ? $order->status 
                : OrderStatusEnum::from($order->status);

            // Order status'una göre stok durumunu güncelle
            if ($orderStatus === OrderStatusEnum::PENDING) {
                // PENDING: Stoklar henüz rezerve edilmemiş, merkeze ait ve AVAILABLE
                foreach ($stockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);
                }
            } elseif (in_array($orderStatus, [
                OrderStatusEnum::PROCESSING,
                OrderStatusEnum::SHIPPED,
                OrderStatusEnum::DELIVERED,
            ])) {
                foreach ($stockItems as $stockItem) {
                    if ($orderStatus === OrderStatusEnum::DELIVERED) {
                        // Delivered ise direkt dealer'a transfer et
                        $stockItem->update([
                            'dealer_id' => $order->dealer_id,
                            'location' => StockLocationEnum::DEALER->value,
                            'status' => StockStatusEnum::AVAILABLE->value,
                        ]);

                        // RECEIVED logu oluştur
                        StockMovement::create([
                            'stock_item_id' => $stockItem->id,
                            'user_id' => $user?->id ?? $order->created_by,
                            'action' => StockMovementActionEnum::RECEIVED->value,
                            'description' => "Sipariş #{$order->id} {$order->dealer->name} bayisine teslim edildi",
                            'created_at' => now(),
                        ]);
                    } else {
                        // Processing veya Shipped ise RESERVED yap, merkeze ait tut
                        $stockItem->update([
                            'status' => StockStatusEnum::RESERVED->value,
                            'location' => StockLocationEnum::CENTER->value,
                            'dealer_id' => null, // Merkeze ait
                        ]);

                        // TRANSFERRED_TO_DEALER logu oluştur (eğer daha önce oluşturulmamışsa)
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
                }
            }
        });
    }

    /**
     * Handle OrderItem updated event.
     */
    public function handleOrderItemUpdated(OrderItemUpdated $event): void
    {
        $orderItem = $event->orderItem;
        // Order'ı refresh et ki status güncel olsun
        $order = $orderItem->order()->first();
        $user = Auth::user();

        // Stok atamaları değişmiş olabilir
        // Bu durumda mevcut stokları kontrol et ve güncelle
        $order->loadMissing('items.stockItems');
        
        DB::transaction(function () use ($orderItem, $order, $user) {
            // OrderItem'ı refresh et ki stockItems yüklü olsun
            $orderItem->refresh();
            $stockItems = $orderItem->stockItems;

            // Order status'unu kontrol et (enum'a cast edilmiş olmalı)
            $orderStatus = $order->status instanceof \App\Enums\OrderStatusEnum 
                ? $order->status 
                : \App\Enums\OrderStatusEnum::from($order->status);

            // Order status'una göre stok durumunu güncelle
            if ($orderStatus === OrderStatusEnum::PENDING) {
                // PENDING: Stoklar henüz rezerve edilmemiş, merkeze ait ve AVAILABLE
                foreach ($stockItems as $stockItem) {
                    $stockItem->update([
                        'status' => StockStatusEnum::AVAILABLE->value,
                        'location' => StockLocationEnum::CENTER->value,
                        'dealer_id' => null, // Merkeze ait
                    ]);
                }
            } elseif (in_array($orderStatus, [
                OrderStatusEnum::PROCESSING,
                OrderStatusEnum::SHIPPED,
                OrderStatusEnum::DELIVERED,
            ])) {
                foreach ($stockItems as $stockItem) {
                    if ($orderStatus === OrderStatusEnum::DELIVERED) {
                        // Delivered ise direkt dealer'a transfer et
                        $stockItem->update([
                            'dealer_id' => $order->dealer_id,
                            'location' => StockLocationEnum::DEALER->value,
                            'status' => StockStatusEnum::AVAILABLE->value,
                        ]);
                    } else {
                        // Processing veya Shipped ise RESERVED yap, merkeze ait tut
                        $stockItem->update([
                            'status' => StockStatusEnum::RESERVED->value,
                            'location' => StockLocationEnum::CENTER->value,
                            'dealer_id' => null, // Merkeze ait
                        ]);
                    }
                }
            }
        });
    }
}

