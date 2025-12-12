<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StockLocationEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use App\Models\StockItem;
use App\Models\StockMovement;
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
        $user = Auth::user();

        // OrderItem'ları manuel oluştur ve stok atamalarını yap
        if (!empty($this->stockAssignments)) {
            DB::transaction(function () use ($order, $user) {
                foreach ($this->stockAssignments as $itemData) {
                    // OrderItem oluştur
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'quantity' => $itemData['quantity'] ?? 1,
                    ]);

                    // Stok atamalarını yap
                    $selectedStockIds = array_filter($itemData['stock_items'] ?? []);
                    
                    if (!empty($selectedStockIds)) {
                        // Seçilen stokları order_item_stock'a ekle
                        $orderItem->stockItems()->attach($selectedStockIds);

                        // Statüye göre stok durumunu güncelle
                        $orderStatus = $order->status;
                        
                        if (in_array($orderStatus->value, [OrderStatusEnum::PROCESSING->value, OrderStatusEnum::SHIPPED->value, OrderStatusEnum::DELIVERED->value])) {
                            // Processing veya üzeri ise stokları RESERVED yap
                            StockItem::whereIn('id', $selectedStockIds)->update([
                                'status' => StockStatusEnum::RESERVED->value,
                            ]);
                        }

                        // Her stok için hareket logu oluştur
                        foreach ($selectedStockIds as $stockId) {
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

                // Statü delivered ise, tüm stokları dealer'a transfer et
                if ($order->status->value === OrderStatusEnum::DELIVERED->value) {
                    $allStockItems = $order->items()->with('stockItems')->get()->pluck('stockItems')->flatten()->unique('id');
                    
                    foreach ($allStockItems as $stockItem) {
                        // Stok item'ı dealer'a transfer et
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
                }
            });
        }
    }
}
