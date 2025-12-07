<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Herkes kendi yetkilerine göre hizmetleri görebilir
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        // Admin ve merkez çalışanları tüm hizmetleri görebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi hizmetlerini görebilir
        if ($user->dealer_id) {
            return $service->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Herkes hizmet oluşturabilir
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        // Admin ve merkez çalışanları tüm hizmetleri güncelleyebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi sadece kendi hizmetlerini güncelleyebilir
        if ($user->dealer_id) {
            return $service->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        // Sadece admin silebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Service $service): bool
    {
        return $this->delete($user, $service);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
