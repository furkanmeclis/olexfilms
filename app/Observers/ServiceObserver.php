<?php

namespace App\Observers;

use App\Enums\ServiceStatusEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Listeners\ServiceStatusUpdatedListener;
use App\Models\Service;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        // ServiceItemObserver created event'inde StockItem'ı RESERVED yapacak
        // Burada sadece stock movement logu oluşturulabilir (opsiyonel)
    }

    /**
     * Handle the Service "updated" event.
     */
    public function updated(Service $service): void
    {
        // Eğer status completed olduysa
        if ($service->wasChanged('status') && $service->status === ServiceStatusEnum::COMPLETED) {
            // Tüm ServiceItem'ların StockItem'larını USED yap
            $service->loadMissing('items.stockItem');
            
            foreach ($service->items as $serviceItem) {
                $stockItem = $serviceItem->stockItem;

                // StockItem null ise atla
                if (!$stockItem) {
                    continue;
                }

                // StockItem'ı USED yap
                $stockItem->update([
                    'status' => StockStatusEnum::USED->value,
                ]);

                // Stock movement logu oluştur
                StockMovement::create([
                    'stock_item_id' => $stockItem->id,
                    'user_id' => Auth::id() ?? $service->user_id,
                    'action' => StockMovementActionEnum::USED_IN_SERVICE->value,
                    'description' => "Hizmet #{$service->service_no} tamamlandı - kullanıldı",
                    'created_at' => now(),
                ]);
            }

            // Garanti başlat
            $listener = new ServiceStatusUpdatedListener();
            $listener->handle($service);
        }
    }

    /**
     * Handle the Service "deleted" event.
     */
    public function deleted(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "restored" event.
     */
    public function restored(Service $service): void
    {
        //
    }

    /**
     * Handle the Service "force deleted" event.
     */
    public function forceDeleted(Service $service): void
    {
        //
    }
}
