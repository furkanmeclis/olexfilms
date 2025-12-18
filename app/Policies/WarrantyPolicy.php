<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;
use App\Models\Warranty;

class WarrantyPolicy
{
    /**
     * Kullanıcı herhangi bir garanti kaydını listeleyebilir mi?
     */
    public function viewAny(User $user): bool
    {
        // Herkes kendi yetkisine göre listeleme yapabilir
        return true;
    }

    /**
     * Kullanıcı tekil bir garanti kaydını görüntüleyebilir mi?
     */
    public function view(User $user, Warranty $warranty): bool
    {
        // Super admin ve merkez çalışanları tüm garantileri görebilir
        if ($user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            return true;
        }

        // Bayi yalnızca kendi bayisine ait servislerin garantisini görebilir
        if ($user->dealer_id) {
            return $warranty->service && $warranty->service->dealer_id === $user->dealer_id;
        }

        return false;
    }

    /**
     * Kullanıcı garanti oluşturabilir mi?
     */
    public function create(User $user): bool
    {
        // Garantiler manuel olarak oluşturulamaz
        return false;
    }

    /**
     * Kullanıcı garanti kaydını güncelleyebilir mi?
     */
    public function update(User $user, Warranty $warranty): bool
    {
        // Sadece super admin ve merkez çalışanı güncelleme yapabilir
        return $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
    }

    /**
     * Kullanıcı garanti kaydını silebilir mi?
     */
    public function delete(User $user, Warranty $warranty): bool
    {
        // Garantiler manuel olarak silinemez
        return false;
    }

    /**
     * Kullanıcı garanti kaydını geri getirebilir mi?
     */
    public function restore(User $user, Warranty $warranty): bool
    {
        return false;
    }

    /**
     * Kullanıcı garanti kaydını kalıcı olarak silebilir mi?
     */
    public function forceDelete(User $user, Warranty $warranty): bool
    {
        return false;
    }
}
