<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\Warranty;

class WarrantyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Herkes kendi yetkilerine göre garantileri görebilir
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Warranty $warranty): bool
    {
        // Admin ve merkez çalışanları tüm garantileri görebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi bayi hizmetlerinin garantilerini görebilir
        if ($user->dealer_id) {
            return $warranty->service && $warranty->service->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Garantiler otomatik oluşturulur, manuel oluşturma yok
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Warranty $warranty): bool
    {
        // Sadece admin is_active alanını değiştirebilir
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Warranty $warranty): bool
    {
        // Garantiler silinemez (cascade delete zaten var, ama manuel silme yok)
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Warranty $warranty): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Warranty $warranty): bool
    {
        return false;
    }
}

