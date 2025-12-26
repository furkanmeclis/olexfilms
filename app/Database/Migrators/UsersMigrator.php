<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UsersMigrator extends BaseMigrator
{
    /**
     * Eski rol -> yeni rol mapping
     */
    protected array $roleMapping = [
        'super' => UserRoleEnum::SUPER_ADMIN->value,
        'central' => UserRoleEnum::CENTER_STAFF->value,
        'central_salesman' => UserRoleEnum::CENTER_STAFF->value,
        'central_contact' => UserRoleEnum::CENTER_STAFF->value,
        'central_worker' => UserRoleEnum::CENTER_STAFF->value,
        'admin' => UserRoleEnum::DEALER_OWNER->value,
        'worker' => UserRoleEnum::DEALER_STAFF->value,
    ];

    protected function getTableName(): string
    {
        return 'users';
    }

    protected function getOldTableName(): string
    {
        return 'users';
    }

    protected function readOldData(): \Generator
    {
        $query = $this->oldDb()
            ->select('id', 'name', 'email', 'phone', 'avatar', 'email_verified_at', 'role', 'parent_id', 'password', 'active', 'remember_token', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Email unique kontrolü
        $existing = DB::table('users')
            ->where('email', $oldData['email'])
            ->first();

        if ($existing) {
            // Zaten varsa, ID mapping'e ekle
            if ($oldData['old_id']) {
                $this->idMapping[$oldData['old_id']] = $existing->id;
            }
            return null; // Skip, zaten var
        }

        // Avatar'ı storage'a kaydet
        $avatarUrl = null;
        if (!empty($oldData['avatar'])) {
            $avatarUrl = $this->saveImageToStorage($oldData['avatar'], 'avatars');
        }

        return [
            'old_id' => $oldData['old_id'],
            'old_parent_id' => $oldData['parent_id'], // Sonra dealer_id'ye map edilecek
            'name' => $oldData['name'],
            'email' => $oldData['email'],
            'phone' => $oldData['phone'] ?? '',
            'avatar_url' => $avatarUrl,
            'email_verified_at' => $oldData['email_verified_at'],
            'password' => $oldData['password'],
            'remember_token' => $oldData['remember_token'],
            'is_active' => $this->boolToTinyint((bool) $oldData['active']),
            'dealer_id' => null, // DealersMigrator'dan sonra güncellenecek
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
            'old_role' => $oldData['role'], // Rol ataması için sakla
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        $oldRole = $newData['old_role'] ?? null;
        $oldParentId = $newData['old_parent_id'] ?? null;
        unset($newData['old_id'], $newData['old_role'], $newData['old_parent_id']);

        $id = DB::table('users')->insertGetId($newData);

        if ($id && $oldId) {
            $this->idMapping[$oldId] = $id;

            // Rol ataması
            if ($oldRole && isset($this->roleMapping[$oldRole])) {
                $roleName = $this->roleMapping[$oldRole];
                $role = Role::where('name', $roleName)->first();

                if ($role) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $id,
                    ]);
                }
            }
        }

        return $id;
    }
}

