<?php

namespace App\Listeners\Orders;

use App\Enums\NotificationEventEnum;
use App\Enums\UserRoleEnum;
use App\Events\Orders\OrderCreated;
use App\Services\NotificationService;

class SendOrderCreatedNotification
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $order->loadMissing('dealer');

        $data = [
            'order_id' => $order->id,
            'dealer_name' => $order->dealer->name ?? 'Bilinmeyen Bayi',
        ];

        // Super Admin ve Center Staff'a gönder
        NotificationService::sendToRoles(
            NotificationEventEnum::ORDER_CREATED->value,
            [
                UserRoleEnum::SUPER_ADMIN->value,
                UserRoleEnum::CENTER_STAFF->value,
            ],
            $data
        );

        // Dealer Owner'a gönder (sadece kendi siparişi ise)
        if ($order->dealer_id) {
            NotificationService::sendToRole(
                NotificationEventEnum::ORDER_CREATED->value,
                UserRoleEnum::DEALER_OWNER->value,
                array_merge($data, [
                    'dealer_id' => $order->dealer_id,
                    'total_amount' => $order->items->sum(fn ($item) => $item->quantity * ($item->unit_price ?? 0)) . ' TL',
                ])
            );
        }
    }
}

