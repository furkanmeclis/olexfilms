<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderItem;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
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

        // OrderItem'ları güncelle ve stok atamalarını yap
        // Stok yönetimi ve movement logları observer tarafından yapılacak
        if (! empty($this->stockAssignments)) {
            DB::transaction(function () use ($order) {
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
                    if (! $orderItem) {
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

                    // Stokları ekle (observer stok durumunu güncelleyecek)
                    if (! empty($toAdd)) {
                        $orderItem->stockItems()->attach($toAdd);
                    }

                    // Stokları kaldır (observer stokları serbest bırakacak)
                    if (! empty($toRemove)) {
                        $orderItem->stockItems()->detach($toRemove);
                    }
                }

                // Silinen OrderItem'ları kaldır (observer stokları serbest bırakacak)
                $toDeleteOrderItemIds = array_diff($existingOrderItemIds, $processedOrderItemIds);
                if (! empty($toDeleteOrderItemIds)) {
                    foreach ($toDeleteOrderItemIds as $orderItemId) {
                        $orderItemToDelete = OrderItem::find($orderItemId);
                        if ($orderItemToDelete) {
                            // Stok atamalarını kaldır
                            $orderItemToDelete->stockItems()->detach();
                            $orderItemToDelete->delete();
                        }
                    }
                }
            });
        }
    }
}
