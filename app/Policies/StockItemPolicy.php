<?php

namespace App\Policies;

use App\Enums\StockStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\StockItem;
use App\Models\User;

class StockItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin tam yetkili, bayi sadece kendi stoklarını görebilir
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StockItem $stockItem): bool
    {
        // Admin tam yetkili
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi: Sadece kendi dealer_id'si eşit ve status != used olanları görebilir
        if ($user->dealer_id && $stockItem->dealer_id === $user->dealer_id) {
            return $stockItem->status !== StockStatusEnum::USED;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Sadece admin ve merkez çalışanları stok girişi yapabilir
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StockItem $stockItem): bool
    {
        // Sadece admin ve merkez çalışanları güncelleyebilir
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StockItem $stockItem): bool
    {
        // Sadece admin silebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StockItem $stockItem): bool
    {
        return $this->delete($user, $stockItem);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StockItem $stockItem): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
