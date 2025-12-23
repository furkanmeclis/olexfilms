<?php

namespace App\Observers;

use App\Enums\ServiceStatusEnum;
use App\Enums\StockMovementActionEnum;
use App\Enums\StockStatusEnum;
use App\Listeners\ServiceStatusUpdatedListener;
use App\Listeners\Services\SendServiceNotifications;
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
        // Eğer status değiştiyse
        if ($service->wasChanged('status')) {
            // getOriginal() enum instance döndürebilir, bu durumda direkt kullan
            $originalStatus = $service->getOriginal('status');
            $oldStatus = $originalStatus instanceof ServiceStatusEnum 
                ? $originalStatus 
                : ServiceStatusEnum::from($originalStatus);
            $newStatus = $service->status;

            // Eğer status completed olduysa
            if ($newStatus === ServiceStatusEnum::COMPLETED) {
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
            // Eğer status COMPLETED'dan başka bir duruma geri alınırsa
            elseif ($oldStatus === ServiceStatusEnum::COMPLETED && $newStatus !== ServiceStatusEnum::COMPLETED) {
                // İlgili garantileri pasifleştir (silme, sadece is_active = false)
                $service->loadMissing('warranties');
                
                foreach ($service->warranties as $warranty) {
                    if ($warranty->is_active) {
                        $warranty->update([
                            'is_active' => false,
                        ]);
                    }
                }
            }

            // Notification gönder
            (new SendServiceNotifications())->handle($service, $oldStatus, $newStatus);
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
