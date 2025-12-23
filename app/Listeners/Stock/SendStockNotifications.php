<?php

namespace App\Listeners\Stock;

use App\Enums\NotificationEventEnum;
use App\Enums\UserRoleEnum;
use App\Services\NotificationService;

class SendStockNotifications
{
    /**
     * Send notification when stock is imported
     */
    public static function sendStockImportedNotification(int $count, string $productName): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::STOCK_IMPORTED->value,
            UserRoleEnum::CENTER_STAFF->value,
            [
                'count' => $count,
                'product_name' => $productName,
            ]
        );
    }

    /**
     * Send notification when stock import fails
     */
    public static function sendStockImportFailedNotification(string $errorMessage): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::STOCK_IMPORT_FAILED->value,
            UserRoleEnum::SUPER_ADMIN->value,
            [
                'error_message' => $errorMessage,
            ]
        );
    }

    /**
     * Send notification when stock is critical low
     */
    public static function sendStockCriticalLowNotification(string $productName, int $count): void
    {
        NotificationService::sendToRoles(
            NotificationEventEnum::STOCK_CRITICAL_LOW->value,
            [
                UserRoleEnum::SUPER_ADMIN->value,
                UserRoleEnum::CENTER_STAFF->value,
            ],
            [
                'product_name' => $productName,
                'count' => $count,
            ]
        );
    }

    /**
     * Send notification when stock is insufficient for order
     */
    public static function sendStockInsufficientForOrderNotification(int $orderId, string $productName, int $quantity, int $available): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::STOCK_INSUFFICIENT_FOR_ORDER->value,
            UserRoleEnum::CENTER_STAFF->value,
            [
                'order_id' => $orderId,
                'product_name' => $productName,
                'quantity' => $quantity,
                'available' => $available,
            ]
        );
    }

    /**
     * Send notification when stock is transferred to dealer
     */
    public static function sendStockTransferToDealerNotification(string $productName, string $dealerName, int $count): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::STOCK_TRANSFER_TO_DEALER->value,
            UserRoleEnum::CENTER_STAFF->value,
            [
                'product_name' => $productName,
                'dealer_name' => $dealerName,
                'count' => $count,
            ]
        );
    }

    /**
     * Send notification when stock is received by dealer
     */
    public static function sendStockReceivedNotification(string $productName, int $count, int $dealerId): void
    {
        NotificationService::sendToRoles(
            NotificationEventEnum::STOCK_RECEIVED->value,
            [
                UserRoleEnum::DEALER_OWNER->value,
                UserRoleEnum::DEALER_STAFF->value,
            ],
            [
                'product_name' => $productName,
                'count' => $count,
                'dealer_id' => $dealerId,
            ]
        );
    }

    /**
     * Send notification when stock is used in service
     */
    public static function sendStockUsedInServiceNotification(string $productName, int $serviceId, int $dealerId): void
    {
        NotificationService::sendToRole(
            NotificationEventEnum::STOCK_USED_IN_SERVICE->value,
            UserRoleEnum::DEALER_STAFF->value,
            [
                'product_name' => $productName,
                'service_id' => $serviceId,
                'dealer_id' => $dealerId,
            ]
        );
    }
}

