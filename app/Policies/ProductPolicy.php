<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Herkes görebilir (admin, merkez, bayi)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Herkes görebilir
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $this->delete($user, $product);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
