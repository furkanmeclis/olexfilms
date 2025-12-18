<?php

namespace App\Observers;

use App\Enums\OrderStatusEnum;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderStatusChanged;
use App\Events\Orders\OrderUpdated;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Order oluşturulduğunda event tetikle
        event(new OrderCreated($order));
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Status değişikliğini kontrol et
        if ($order->wasChanged('status')) {
            $oldStatus = OrderStatusEnum::from($order->getOriginal('status'));
            $newStatus = $order->status;

            // Status değişikliği event'i tetikle
            event(new OrderStatusChanged($order, $oldStatus, $newStatus));

            // İptal edildiyse özel event tetikle
            if ($newStatus === OrderStatusEnum::CANCELLED) {
                event(new OrderCancelled($order));
            }
        }

        // Genel güncelleme event'i tetikle
        event(new OrderUpdated($order));
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Sipariş silindiğinde, eğer iptal edilmemişse iptal event'i tetikle
        // Bu sayede stoklar serbest bırakılabilir
        if ($order->status !== OrderStatusEnum::CANCELLED) {
            // Status'u cancelled yap ve event tetikle
            $order->status = OrderStatusEnum::CANCELLED;
            event(new OrderCancelled($order));
        }
    }
}

