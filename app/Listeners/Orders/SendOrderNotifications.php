<?php

namespace App\Listeners\Orders;

use App\Enums\NotificationEventEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\UserRoleEnum;
use App\Events\Orders\OrderStatusChanged;
use App\Services\NotificationService;

class SendOrderNotifications
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;
        $newStatus = $event->newStatus;
        $order->loadMissing('dealer');

        $baseData = [
            'order_id' => $order->id,
            'dealer_name' => $order->dealer->name ?? 'Bilinmeyen Bayi',
        ];

        match ($newStatus) {
            OrderStatusEnum::PENDING => $this->handlePending($order, $baseData),
            OrderStatusEnum::PROCESSING => $this->handleProcessing($order, $baseData),
            OrderStatusEnum::SHIPPED => $this->handleShipped($order, $baseData),
            OrderStatusEnum::DELIVERED => $this->handleDelivered($order, $baseData),
            OrderStatusEnum::CANCELLED => $this->handleCancelled($order, $baseData),
        };
    }

    private function handlePending($order, array $baseData): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::ORDER_PENDING->value,
            UserRoleEnum::CENTER_STAFF->value,
            $baseData
        );
    }

    private function handleProcessing($order, array $baseData): void
    {
        if ($order->dealer_id) {
            NotificationService::sendToRole(
                NotificationEventEnum::ORDER_PROCESSING->value,
                UserRoleEnum::DEALER_OWNER->value,
                array_merge($baseData, [
                    'dealer_id' => $order->dealer_id,
                ])
            );
        }
    }

    private function handleShipped($order, array $baseData): void
    {
        $data = array_merge($baseData, [
            'cargo_company' => $order->cargo_company ?? 'Bilinmiyor',
            'tracking_number' => $order->tracking_number ?? 'Bilinmiyor',
        ]);

        // Super Admin ve Center Staff
        NotificationService::sendToRoles(
            NotificationEventEnum::ORDER_SHIPPED->value,
            [
                UserRoleEnum::SUPER_ADMIN->value,
                UserRoleEnum::CENTER_STAFF->value,
            ],
            $data
        );

        // Dealer Owner ve Staff
        if ($order->dealer_id) {
            NotificationService::sendToRoles(
                NotificationEventEnum::ORDER_SHIPPED->value,
                [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ],
                array_merge($data, [
                    'dealer_id' => $order->dealer_id,
                ])
            );
        }
    }

    private function handleDelivered($order, array $baseData): void
    {
        // Super Admin ve Center Staff
        NotificationService::sendToRoles(
            NotificationEventEnum::ORDER_DELIVERED->value,
            [
                UserRoleEnum::SUPER_ADMIN->value,
                UserRoleEnum::CENTER_STAFF->value,
            ],
            $baseData
        );

        // Dealer Owner ve Staff
        if ($order->dealer_id) {
            NotificationService::sendToRoles(
                NotificationEventEnum::ORDER_DELIVERED->value,
                [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ],
                array_merge($baseData, [
                    'dealer_id' => $order->dealer_id,
                ])
            );
        }
    }

    private function handleCancelled($order, array $baseData): void
    {
        $data = array_merge($baseData, [
            'reason' => $order->notes ?? 'Belirtilmedi',
        ]);

        // Super Admin ve Center Staff
        NotificationService::sendToRoles(
            NotificationEventEnum::ORDER_CANCELLED->value,
            [
                UserRoleEnum::SUPER_ADMIN->value,
                UserRoleEnum::CENTER_STAFF->value,
            ],
            $data
        );

        // Dealer Owner
        if ($order->dealer_id) {
            NotificationService::sendToRole(
                NotificationEventEnum::ORDER_CANCELLED->value,
                UserRoleEnum::DEALER_OWNER->value,
                array_merge($data, [
                    'dealer_id' => $order->dealer_id,
                ])
            );
        }
    }
}
