<?php

namespace App\Observers;

use App\Enums\NotificationEventEnum;
use App\Enums\UserRoleEnum;
use App\Models\Dealer;
use App\Services\NotificationService;

class DealerObserver
{
    /**
     * Handle the Dealer "updated" event.
     */
    public function updated(Dealer $dealer): void
    {
        // is_active değişikliğini kontrol et
        if ($dealer->wasChanged('is_active')) {
            $isActive = $dealer->is_active;

            $data = [
                'dealer_name' => $dealer->name,
            ];

            if ($isActive) {
                // Bayi aktif yapıldı
                NotificationService::sendToRole(
                    NotificationEventEnum::DEALER_ACTIVATED->value,
                    UserRoleEnum::SUPER_ADMIN->value,
                    $data
                );
            } else {
                // Bayi pasif yapıldı
                // Super Admin'e bildir
                NotificationService::sendToRole(
                    NotificationEventEnum::DEALER_DEACTIVATED->value,
                    UserRoleEnum::SUPER_ADMIN->value,
                    $data
                );

                // Bayi sahibi ve çalışanlarına bildir
                NotificationService::sendToRoles(
                    NotificationEventEnum::DEALER_DEACTIVATED->value,
                    [
                        UserRoleEnum::DEALER_OWNER->value,
                        UserRoleEnum::DEALER_STAFF->value,
                    ],
                    array_merge($data, [
                        'dealer_id' => $dealer->id,
                    ])
                );
            }
        }
    }
}
