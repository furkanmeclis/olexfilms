<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Events\Orders\OrderItemUpdated;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected array $stockAssignments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Bayi için dealer_id otomatik set edilir
        if ($user && $user->dealer_id) {
            $data['dealer_id'] = $user->dealer_id;
        }

        // created_by otomatik set edilir
        $data['created_by'] = $user->id;
        
        // Statü admin/center_staff tarafından set edilmişse kullan, yoksa pending
        if (!isset($data['status'])) {
            $data['status'] = OrderStatusEnum::PENDING->value;
        }

        // items'ı geçici olarak sakla (OrderItem'ları manuel oluşturacağız)
        if (isset($data['items']) && is_array($data['items'])) {
            $this->stockAssignments = $data['items'];
            unset($data['items']); // OrderItem'ları manuel oluşturacağız
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        // Order'ı refresh et ki status güncel olsun
        $order->refresh();
        $user = Auth::user();

        // OrderItem'ları oluştur ve stok atamalarını yap
        // Stok yönetimi ve movement logları observer tarafından yapılacak
        if (!empty($this->stockAssignments)) {
            DB::transaction(function () use ($order, $user) {
                foreach ($this->stockAssignments as $itemData) {
                    // Stok atamalarını al
                    $selectedStockIds = array_filter($itemData['stock_items'] ?? []);
                    
                    if (empty($selectedStockIds)) {
                        continue;
                    }

                    // OrderItem oluştur
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'quantity' => $itemData['quantity'] ?? 1,
                    ]);

                    // Stok atamalarını yap
                    $orderItem->stockItems()->attach($selectedStockIds);
                    
                    // Stokları attach ettikten sonra OrderItem'ı refresh et
                    $orderItem->refresh();
                    
                    // Order status'una göre stokları güncelle
                    $orderStatus = $order->status instanceof OrderStatusEnum 
                        ? $order->status 
                        : OrderStatusEnum::from($order->status);
                    
                    $stockItems = $orderItem->stockItems;
                    
                    if ($orderStatus === OrderStatusEnum::DELIVERED) {
                        // Delivered ise direkt dealer'a transfer et
                        foreach ($stockItems as $stockItem) {
                            $stockItem->update([
                                'dealer_id' => $order->dealer_id,
                                'location' => StockLocationEnum::DEALER->value,
                                'status' => StockStatusEnum::AVAILABLE->value,
                            ]);

                            // RECEIVED logu oluştur
                            \App\Models\StockMovement::create([
                                'stock_item_id' => $stockItem->id,
                                'user_id' => $user?->id ?? $order->created_by,
                                'action' => StockMovementActionEnum::RECEIVED->value,
                                'description' => "Sipariş #{$order->id} {$order->dealer->name} bayisine teslim edildi",
                                'created_at' => now(),
                            ]);
                        }
                    } elseif (in_array($orderStatus, [
                        OrderStatusEnum::PROCESSING,
                        OrderStatusEnum::SHIPPED,
                    ])) {
                        // Processing veya Shipped ise RESERVED yap, merkeze ait tut
                        foreach ($stockItems as $stockItem) {
                            $stockItem->update([
                                'status' => StockStatusEnum::RESERVED->value,
                                'location' => StockLocationEnum::CENTER->value,
                                'dealer_id' => null, // Merkeze ait
                            ]);

                            // TRANSFERRED_TO_DEALER logu oluştur (eğer daha önce oluşturulmamışsa)
                            $existingMovement = \App\Models\StockMovement::where('stock_item_id', $stockItem->id)
                                ->where('action', StockMovementActionEnum::TRANSFERRED_TO_DEALER->value)
                                ->where('description', 'like', "%Sipariş #{$order->id}%")
                                ->first();

                            if (!$existingMovement) {
                                \App\Models\StockMovement::create([
                                    'stock_item_id' => $stockItem->id,
                                    'user_id' => $user?->id ?? $order->created_by,
                                    'action' => StockMovementActionEnum::TRANSFERRED_TO_DEALER->value,
                                    'description' => "Sipariş #{$order->id} ile {$order->dealer->name} bayisine yollandı",
                                    'created_at' => now(),
                                ]);
                            }
                        }
                    } elseif ($orderStatus === OrderStatusEnum::PENDING) {
                        // PENDING: Stoklar henüz rezerve edilmemiş, merkeze ait ve AVAILABLE
                        foreach ($stockItems as $stockItem) {
                            $stockItem->update([
                                'status' => StockStatusEnum::AVAILABLE->value,
                                'location' => StockLocationEnum::CENTER->value,
                                'dealer_id' => null, // Merkeze ait
                            ]);
                        }
                    }
                }
            });
        }
    }
}
