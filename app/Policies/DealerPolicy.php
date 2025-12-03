<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Dealer;
use App\Models\User;

class DealerPolicy
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
    public function view(User $user, Dealer $dealer): bool
    {
        return $this->viewAny($user);
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
    public function update(User $user, Dealer $dealer): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dealer $dealer): bool
    {
        // Only super admin can delete dealers
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dealer $dealer): bool
    {
        return $this->delete($user, $dealer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dealer $dealer): bool
    {
        return $this->delete($user, $dealer);
    }
}
