<?php

namespace App\Observers;

use App\Enums\ServiceStatusEnum;
use App\Enums\StockStatusEnum;
use App\Models\ServiceItem;

class ServiceItemObserver
{
    /**
     * Handle the ServiceItem "created" event.
     */
    public function created(ServiceItem $serviceItem): void
    {
        // ServiceItem oluşturulduğunda StockItem'ı RESERVED yap
        $stockItem = $serviceItem->stockItem;
        
        if ($stockItem && $stockItem->status !== StockStatusEnum::RESERVED) {
            $stockItem->update([
                'status' => StockStatusEnum::RESERVED->value,
            ]);
        }
    }

    /**
     * Handle the ServiceItem "deleted" event.
     */
    public function deleted(ServiceItem $serviceItem): void
    {
        // ServiceItem silindiğinde, eğer service completed değilse StockItem'ı AVAILABLE yap
        $stockItem = $serviceItem->stockItem;
        $service = $serviceItem->service;

        if ($stockItem && $service && $service->status !== ServiceStatusEnum::COMPLETED) {
            // Service completed değilse, stok geri alınabilir
            $stockItem->update([
                'status' => StockStatusEnum::AVAILABLE->value,
            ]);
        }
    }

    /**
     * Handle the ServiceItem "restored" event.
     */
    public function restored(ServiceItem $serviceItem): void
    {
        // ServiceItem geri yüklendiğinde StockItem'ı tekrar RESERVED yap
        $stockItem = $serviceItem->stockItem;
        
        if ($stockItem && $stockItem->status === StockStatusEnum::AVAILABLE) {
            $stockItem->update([
                'status' => StockStatusEnum::RESERVED->value,
            ]);
        }
    }
}

