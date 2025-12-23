<?php

namespace App\Observers;

use App\Enums\NotificationEventEnum;
use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Services\NotificationService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Eğer bayi sahibi ise, bayi sahibine bildir
        if ($user->dealer_id && $user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            $dealerOwner = User::where('dealer_id', $user->dealer_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', UserRoleEnum::DEALER_OWNER->value);
                })
                ->where('id', '!=', $user->id)
                ->first();

            if ($dealerOwner) {
                NotificationService::send(
                    NotificationEventEnum::USER_CREATED->value,
                    [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                    ],
                    $dealerOwner
                );
            }
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // is_active değişikliğini kontrol et
        if ($user->wasChanged('is_active')) {
            $isActive = $user->is_active;

            $data = [
                'user_name' => $user->name,
                'user_email' => $user->email,
            ];

            if (! $isActive) {
                // Kullanıcı pasif yapıldı
                // Super Admin'e bildir
                NotificationService::sendToRole(
                    NotificationEventEnum::USER_DEACTIVATED->value,
                    UserRoleEnum::SUPER_ADMIN->value,
                    $data
                );

                // Eğer bayi sahibi ise, bayi sahibine bildir
                if ($user->dealer_id) {
                    $dealerOwner = User::where('dealer_id', $user->dealer_id)
                        ->whereHas('roles', function ($query) {
                            $query->where('name', UserRoleEnum::DEALER_OWNER->value);
                        })
                        ->where('id', '!=', $user->id)
                        ->first();

                    if ($dealerOwner) {
                        NotificationService::send(
                            NotificationEventEnum::USER_DEACTIVATED->value,
                            $data,
                            $dealerOwner
                        );
                    }
                }

                // Kullanıcının kendisine bildir (eğer aktifse)
                if ($user->canAccess()) {
                    NotificationService::send(
                        NotificationEventEnum::USER_DEACTIVATED->value,
                        $data,
                        $user
                    );
                }
            }
        }
    }
}
