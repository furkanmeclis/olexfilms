<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Herkes kendi yetkilerine göre müşterileri görebilir
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Admin ve merkez çalışanları tüm müşterileri görebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi müşterilerini görebilir
        if ($user->dealer_id) {
            return $customer->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Herkes müşteri oluşturabilir
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Admin ve merkez çalışanları tüm müşterileri güncelleyebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi müşterilerini güncelleyebilir
        if ($user->dealer_id) {
            return $customer->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Admin ve merkez çalışanları tüm müşterileri silebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi müşterilerini silebilir
        if ($user->dealer_id) {
            return $customer->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $this->delete($user, $customer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
