<?php

namespace App\Listeners\Services;

use App\Enums\NotificationEventEnum;
use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Service;
use App\Services\NotificationService;

class SendServiceNotifications
{
    /**
     * Handle service status changes and send notifications
     */
    public function handle(Service $service, ServiceStatusEnum $oldStatus, ServiceStatusEnum $newStatus): void
    {
        $service->loadMissing(['customer', 'carBrand', 'carModel', 'dealer']);

        $baseData = [
            'service_id' => $service->service_no ?? $service->id,
            'customer_name' => $service->customer->name ?? 'Bilinmeyen Müşteri',
            'car_brand' => $service->carBrand->name ?? '',
            'car_model' => $service->carModel->name ?? '',
        ];

        match ($newStatus) {
            ServiceStatusEnum::PENDING => $this->handlePending($service, $baseData),
            ServiceStatusEnum::READY => $this->handleReady($service, $baseData),
            ServiceStatusEnum::COMPLETED => $this->handleCompleted($service, $baseData),
            ServiceStatusEnum::CANCELLED => $this->handleCancelled($service, $baseData),
            default => null,
        };
    }

    private function handlePending(Service $service, array $baseData): void
    {
        if ($service->dealer_id) {
            NotificationService::sendToRole(
                NotificationEventEnum::SERVICE_PENDING->value,
                UserRoleEnum::DEALER_STAFF->value,
                array_merge($baseData, [
                    'dealer_id' => $service->dealer_id,
                ])
            );
        }
    }

    private function handleReady(Service $service, array $baseData): void
    {
        // Center Staff
        NotificationService::sendToRole(
            NotificationEventEnum::SERVICE_READY->value,
            UserRoleEnum::CENTER_STAFF->value,
            $baseData
        );

        // Dealer Owner ve Staff
        if ($service->dealer_id) {
            NotificationService::sendToRoles(
                NotificationEventEnum::SERVICE_READY->value,
                [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ],
                array_merge($baseData, [
                    'dealer_id' => $service->dealer_id,
                ])
            );
        }
    }

    private function handleCompleted(Service $service, array $baseData): void
    {
        // Super Admin
        NotificationService::sendToRole(
            NotificationEventEnum::SERVICE_COMPLETED->value,
            UserRoleEnum::SUPER_ADMIN->value,
            $baseData
        );

        // Dealer Owner ve Staff
        if ($service->dealer_id) {
            NotificationService::sendToRoles(
                NotificationEventEnum::SERVICE_COMPLETED->value,
                [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ],
                array_merge($baseData, [
                    'dealer_id' => $service->dealer_id,
                ])
            );
        }
    }

    private function handleCancelled(Service $service, array $baseData): void
    {
        // Dealer Owner ve Staff
        if ($service->dealer_id) {
            NotificationService::sendToRoles(
                NotificationEventEnum::SERVICE_CANCELLED->value,
                [
                    UserRoleEnum::DEALER_OWNER->value,
                    UserRoleEnum::DEALER_STAFF->value,
                ],
                array_merge($baseData, [
                    'dealer_id' => $service->dealer_id,
                ])
            );
        }
    }
}
