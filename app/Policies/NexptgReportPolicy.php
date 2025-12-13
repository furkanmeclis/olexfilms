<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\NexptgReport;
use App\Models\User;

class NexptgReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Super admin veya API user'ı olan kullanıcılar görebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->nexptgApiUser !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NexptgReport $nexptgReport): bool
    {
        // Super admin tüm raporları görebilir
        if ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value)) {
            return true;
        }

        // Kullanıcı sadece kendi API user'ının raporlarını görebilir
        return $user->nexptgApiUser && $nexptgReport->api_user_id === $user->nexptgApiUser->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Reports can only be created via API sync
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NexptgReport $nexptgReport): bool
    {
        // Sadece super admin düzenleyebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NexptgReport $nexptgReport): bool
    {
        // Sadece super admin silebilir
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NexptgReport $nexptgReport): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NexptgReport $nexptgReport): bool
    {
        return $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }
}
