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
        // #region agent log
        $logData = ['location' => 'WarrantyPolicy.php:23', 'message' => 'view method entry', 'data' => ['user_id' => $user->id, 'warranty_id' => $warranty->id, 'warranty_service_id' => $warranty->service_id], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F'];
        file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        // Super admin ve merkez çalışanları tüm garantileri görebilir
        $isAdmin = $user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ]);
        // #region agent log
        $logData = ['location' => 'WarrantyPolicy.php:30', 'message' => 'Admin check in view', 'data' => ['isAdmin' => $isAdmin, 'user_roles' => $user->getRoleNames()->toArray()], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F'];
        file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        if ($isAdmin) {
            // #region agent log
            $logData = ['location' => 'WarrantyPolicy.php:33', 'message' => 'Admin detected in view, returning true', 'data' => [], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F'];
            file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            return true;
        }

        // Bayi yalnızca kendi bayisine ait servislerin garantisini görebilir
        if ($user->dealer_id) {
            $service = $warranty->service;
            $result = $service && $service->dealer_id === $user->dealer_id;
            // #region agent log
            $logData = ['location' => 'WarrantyPolicy.php:40', 'message' => 'Dealer check in view', 'data' => ['dealer_id' => $user->dealer_id, 'service_exists' => $service !== null, 'service_dealer_id' => $service?->dealer_id, 'result' => $result], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F'];
            file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            return $result;
        }

        // #region agent log
        $logData = ['location' => 'WarrantyPolicy.php:46', 'message' => 'No dealer_id and not admin, returning false', 'data' => ['dealer_id' => $user->dealer_id], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'F'];
        file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
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
