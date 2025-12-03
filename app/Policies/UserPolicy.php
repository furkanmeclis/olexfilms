<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Super admin and center staff can view all users
        if ($user->hasAnyRole([UserRoleEnum::SUPER_ADMIN->value, UserRoleEnum::CENTER_STAFF->value])) {
            return true;
        }

        // Dealer owner can only view users from their dealer
        if ($user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            return $model->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
            UserRoleEnum::DEALER_OWNER->value,
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Super admin and center staff can update all users
        if ($user->hasAnyRole([UserRoleEnum::SUPER_ADMIN->value, UserRoleEnum::CENTER_STAFF->value])) {
            return true;
        }

        // Dealer owner can only update users from their dealer
        if ($user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            return $model->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Super admin and center staff can delete all users
        if ($user->hasAnyRole([UserRoleEnum::SUPER_ADMIN->value, UserRoleEnum::CENTER_STAFF->value])) {
            return true;
        }

        // Dealer owner can only delete users from their dealer
        if ($user->hasRole(UserRoleEnum::DEALER_OWNER->value)) {
            return $model->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only super admin can force delete
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
