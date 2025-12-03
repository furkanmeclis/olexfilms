<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\CarBrand;
use App\Models\User;

class CarBrandPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CarBrand $carBrand): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CarBrand $carBrand): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CarBrand $carBrand): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CarBrand $carBrand): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CarBrand $carBrand): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
