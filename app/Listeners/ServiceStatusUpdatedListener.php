<?php

namespace App\Listeners;

use App\Models\Service;
use App\Models\Warranty;

class ServiceStatusUpdatedListener
{
    /**
     * Handle the event.
     * Service status completed olduğunda garanti başlatır.
     */
    public function handle(Service $service): void
    {
        // Her service_item için garanti oluştur
        // Relationship'leri yükle
        if (! $service->relationLoaded('items')) {
            $service->load('items.stockItem.product');
        } else {
            $service->loadMissing(['items.stockItem', 'items.stockItem.product']);
        }

        // completed_at tarihini kontrol et (Carbon instance olmalı)
        $startDate = $service->completed_at;
        if (! $startDate) {
            $startDate = now();
        } elseif (is_string($startDate)) {
            // Eğer string ise Carbon'a çevir
            $startDate = \Carbon\Carbon::parse($startDate);
        }

        foreach ($service->items as $serviceItem) {
            $stockItem = $serviceItem->stockItem;

            // StockItem null ise atla
            if (! $stockItem) {
                continue;
            }

            $product = $stockItem->product;

            // Product null ise atla
            if (! $product) {
                continue;
            }

            // Ürünün garanti süresini al (ay cinsinden)
            $warrantyDuration = $product->warranty_duration ?? 0;

            if ($warrantyDuration > 0) {
                // Garanti kaydı oluştur
                Warranty::create([
                    'service_id' => $service->id,
                    'stock_item_id' => $stockItem->id,
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addMonths($warrantyDuration),
                    'is_active' => true,
                ]);
            }
        }
    }
}
