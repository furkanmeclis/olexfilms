<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use App\Models\StockItem;
use App\Models\StockMovement;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected array $stockAssignments = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Mevcut OrderItem'ları ve stok atamalarını form'a yükle
        if (isset($this->record->items)) {
            $data['items'] = $this->record->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'stock_items' => $item->stockItems->pluck('id')->toArray(),
                ];
            })->toArray();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // items'ı geçici olarak sakla (OrderItem'ları manuel güncelleyeceğiz)
        if (isset($data['items']) && is_array($data['items'])) {
            $this->stockAssignments = $data['items'];
            unset($data['items']); // OrderItem'ları manuel güncelleyeceğiz
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $order = $this->record;
        $user = Auth::user();

        // OrderItem'ları manuel güncelle ve stok atamalarını yap
        if (!empty($this->stockAssignments)) {
            DB::transaction(function () use ($order, $user) {
                $order->refresh();
                $existingOrderItemIds = $order->items()->pluck('id')->toArray();
                $processedOrderItemIds = [];

                foreach ($this->stockAssignments as $itemData) {
                    $orderItem = null;
                    
                    // Mevcut OrderItem'ı güncelle veya yeni oluştur
                    if (isset($itemData['id']) && $itemData['id']) {
                        $orderItem = $order->items()->find($itemData['id']);
                        if ($orderItem) {
                            // Mevcut OrderItem'ı güncelle
                            $orderItem->update([
                                'product_id' => $itemData['product_id'] ?? null,
                                'quantity' => $itemData['quantity'] ?? 1,
                            ]);
                            $processedOrderItemIds[] = $orderItem->id;
                        }
                    }
                    
                    // Yeni OrderItem oluştur
                    if (!$orderItem) {
                        $orderItem = OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $itemData['product_id'] ?? null,
                            'quantity' => $itemData['quantity'] ?? 1,
                        ]);
                        $processedOrderItemIds[] = $orderItem->id;
                    }

                    // Mevcut stok atamalarını al
                    $currentStockIds = $orderItem->stockItems->pluck('id')->toArray();
                    
                    // Yeni seçilen stokları al
                    $selectedStockIds = array_filter($itemData['stock_items'] ?? []);

                    // Farkı bul: eklenen ve kaldırılan stoklar
                    $toAdd = array_diff($selectedStockIds, $currentStockIds);
                    $toRemove = array_diff($currentStockIds, $selectedStockIds);

                    // Stokları ekle
                    if (!empty($toAdd)) {
                        $orderItem->stockItems()->attach($toAdd);

                        // Statüye göre stok durumunu güncelle
                        if (in_array($order->status->value, [OrderStatusEnum::PROCESSING->value, OrderStatusEnum::SHIPPED->value, OrderStatusEnum::DELIVERED->value])) {
                            // Eğer statü delivered ise, direkt dealer'a transfer et
                            if ($order->status->value === OrderStatusEnum::DELIVERED->value) {
                                StockItem::whereIn('id', $toAdd)->update([
                                    'dealer_id' => $order->dealer_id,
                                    'location' => StockLocationEnum::DEALER->value,
                                    'status' => StockStatusEnum::AVAILABLE->value,
                                ]);

                                // Hareket logu oluştur
                                foreach ($toAdd as $stockId) {
                                    // TRANSFERRED_TO_DEALER logu
                                    StockMovement::create([
                                        'stock_item_id' => $stockId,
                                        'user_id' => $user->id,
                                        'action' => StockMovementActionEnum::TRANSFERRED_TO_DEALER->value,
                                        'description' => "Sipariş #{$order->id} ile {$order->dealer->name} bayisine yollandı",
                                        'created_at' => now(),
                                    ]);

                                    // RECEIVED logu
                                    StockMovement::create([
                                        'stock_item_id' => $stockId,
                                        'user_id' => $user->id,
                                        'action' => StockMovementActionEnum::RECEIVED->value,
                                        'description' => "Sipariş #{$order->id} {$order->dealer->name} bayisine teslim edildi",
                                        'created_at' => now(),
                                    ]);
                                }
                            } else {
                                // Processing veya Shipped ise RESERVED yap
                                StockItem::whereIn('id', $toAdd)->update([
                                    'status' => StockStatusEnum::RESERVED->value,
                                ]);

                                // Hareket logu oluştur
                                foreach ($toAdd as $stockId) {
                                    StockMovement::create([
                                        'stock_item_id' => $stockId,
                                        'user_id' => $user->id,
                                        'action' => StockMovementActionEnum::TRANSFERRED_TO_DEALER->value,
                                        'description' => "Sipariş #{$order->id} ile {$order->dealer->name} bayisine yollandı",
                                        'created_at' => now(),
                                    ]);
                                }
                            }
                        }
                    }

                    // Stokları kaldır
                    if (!empty($toRemove)) {
                        $orderItem->stockItems()->detach($toRemove);

                        // Kaldırılan stokları tekrar AVAILABLE yap (eğer RESERVED ise)
                        StockItem::whereIn('id', $toRemove)
                            ->where('status', StockStatusEnum::RESERVED->value)
                            ->where('location', StockLocationEnum::CENTER->value)
                            ->update([
                                'status' => StockStatusEnum::AVAILABLE->value,
                            ]);
                    }
                }

                // Silinen OrderItem'ları kaldır
                $toDeleteOrderItemIds = array_diff($existingOrderItemIds, $processedOrderItemIds);
                if (!empty($toDeleteOrderItemIds)) {
                    foreach ($toDeleteOrderItemIds as $orderItemId) {
                        $orderItemToDelete = OrderItem::find($orderItemId);
                        if ($orderItemToDelete) {
                            // Stok atamalarını kaldır
                            $orderItemToDelete->stockItems()->detach();
                            
                            // Kaldırılan stokları tekrar AVAILABLE yap
                            StockItem::whereIn('id', $orderItemToDelete->stockItems->pluck('id')->toArray())
                                ->where('status', StockStatusEnum::RESERVED->value)
                                ->where('location', StockLocationEnum::CENTER->value)
                                ->update([
                                    'status' => StockStatusEnum::AVAILABLE->value,
                                ]);
                            
                            $orderItemToDelete->delete();
                        }
                    }
                }

                // Statü değişikliğine göre tüm stokları güncelle
                $allStockItems = $order->items()->with('stockItems')->get()->pluck('stockItems')->flatten()->unique('id');

                if ($order->status->value === OrderStatusEnum::DELIVERED->value) {
                    // Delivered ise, tüm stokları dealer'a transfer et
                    foreach ($allStockItems as $stockItem) {
                        $stockItem->update([
                            'dealer_id' => $order->dealer_id,
                            'location' => StockLocationEnum::DEALER->value,
                            'status' => StockStatusEnum::AVAILABLE->value,
                        ]);

                        // Hareket logu oluştur (eğer daha önce oluşturulmamışsa)
                        $existingMovement = StockMovement::where('stock_item_id', $stockItem->id)
                            ->where('action', StockMovementActionEnum::RECEIVED->value)
                            ->where('description', 'like', "%Sipariş #{$order->id}%")
                            ->first();

                        if (!$existingMovement) {
                            StockMovement::create([
                                'stock_item_id' => $stockItem->id,
                                'user_id' => $user->id,
                                'action' => StockMovementActionEnum::RECEIVED->value,
                                'description' => "Sipariş #{$order->id} {$order->dealer->name} bayisine teslim edildi",
                                'created_at' => now(),
                            ]);
                        }
                    }
                } elseif (in_array($order->status->value, [OrderStatusEnum::PROCESSING->value, OrderStatusEnum::SHIPPED->value])) {
                    // Processing veya Shipped ise, stokları RESERVED yap
                    foreach ($allStockItems as $stockItem) {
                        if ($stockItem->status->value !== StockStatusEnum::RESERVED->value) {
                            $stockItem->update([
                                'status' => StockStatusEnum::RESERVED->value,
                            ]);
                        }
                    }
                }
            });
        }
    }
}
