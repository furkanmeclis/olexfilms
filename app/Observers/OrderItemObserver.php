<?php

namespace App\Observers;

use App\Events\Orders\OrderItemCreated;
use App\Events\Orders\OrderItemDeleted;
use App\Events\Orders\OrderItemUpdated;
use App\Models\OrderItem;

class OrderItemObserver
{
    /**
     * Handle the OrderItem "created" event.
     */
    public function created(OrderItem $orderItem): void
    {
        // OrderItem oluşturulduğunda event tetikle
        event(new OrderItemCreated($orderItem));
    }

    /**
     * Handle the OrderItem "updated" event.
     */
    public function updated(OrderItem $orderItem): void
    {
        // OrderItem güncellendiğinde event tetikle
        event(new OrderItemUpdated($orderItem));
    }

    /**
     * Handle the OrderItem "deleted" event.
     */
    public function deleted(OrderItem $orderItem): void
    {
        // OrderItem silindiğinde event tetikle
        event(new OrderItemDeleted($orderItem));
    }
}
