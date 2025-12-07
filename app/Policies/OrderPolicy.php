<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Herkes kendi yetkilerine göre siparişleri görebilir
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Admin ve merkez çalışanları tüm siparişleri görebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi siparişlerini görebilir
        if ($user->dealer_id) {
            return $order->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, merkez çalışanları ve bayiler sipariş oluşturabilir
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
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
    public function delete(User $user, Order $order): bool
    {
        // Sadece admin silebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $this->delete($user, $order);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
