<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class DealersMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'dealers';
    }

    protected function getOldTableName(): string
    {
        return 'dealer_details';
    }

    protected function readOldData(): \Generator
    {
        // dealer_details tablosundan oku, users ile join et
        $query = $this->oldDb()
            ->select(
                'dealer_details.id',
                'dealer_details.user_id',
                'dealer_details.company_name',
                'dealer_details.company_phone',
                'dealer_details.company_email',
                'dealer_details.company_logo',
                'dealer_details.company_country',
                'dealer_details.company_city',
                'dealer_details.company_district',
                'dealer_details.company_zip',
                'dealer_details.company_address',
                'dealer_details.created_at',
                'dealer_details.updated_at',
                'users.email as user_email',
                'users.name as user_name'
            )
            ->join('users', 'dealer_details.user_id', '=', 'users.id')
            ->orderBy('dealer_details.id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // User ID mapping - önce mapping'den bak
        $userMapping = $this->getPreviousMapping('users');
        $newUserId = $userMapping[$oldData['user_id']] ?? null;

        // Eğer mapping'de yoksa, email ile yeni veritabanında ara
        if ($newUserId === null && !empty($oldData['user_email'])) {
            $user = DB::table('users')
                ->where('email', $oldData['user_email'])
                ->first();
            
            if ($user) {
                $newUserId = $user->id;
            }
        }

        // Hala bulunamadıysa, eski veritabanından user email'ini al ve tekrar ara
        if ($newUserId === null) {
            $oldUser = $this->oldDb->table('users')
                ->where('id', $oldData['user_id'])
                ->first();
            
            if ($oldUser && !empty($oldUser->email)) {
                $user = DB::table('users')
                    ->where('email', $oldUser->email)
                    ->first();
                
                if ($user) {
                    $newUserId = $user->id;
                }
            }
        }

        if ($newUserId === null) {
            $this->command->warn("Dealer ID {$oldData['id']}: User ID {$oldData['user_id']} (email: {$oldData['user_email']}) bulunamadı, atlanıyor.");
            return null;
        }

        // Dealer code oluştur (varsa)
        $dealerCode = $this->generateDealerCode($oldData['company_name'] ?? $oldData['user_name']);

        // Logo'yu storage'a kaydet
        $logoPath = null;
        if (!empty($oldData['company_logo'])) {
            $logoPath = $this->saveImageToStorage($oldData['company_logo'], 'dealers');
        }

        return [
            'old_id' => $oldData['old_id'],
            'old_user_id' => $oldData['user_id'], // User'ın dealer_id'sini güncellemek için
            'dealer_code' => $dealerCode,
            'name' => $oldData['company_name'] ?? $oldData['user_name'],
            'email' => $oldData['company_email'] ?? $oldData['user_email'],
            'phone' => $oldData['company_phone'] ?? '',
            'address' => $oldData['company_address'] ?? '',
            'city' => $oldData['company_city'],
            'district' => $oldData['company_district'],
            'logo_path' => $logoPath,
            'is_active' => 1, // Default aktif
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        $oldUserId = $newData['old_user_id'] ?? null;
        unset($newData['old_id'], $newData['old_user_id']);

        // Email unique kontrolü
        $existing = DB::table('dealers')
            ->where('email', $newData['email'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            // User'ın dealer_id'sini güncelle
            if ($oldUserId) {
                $userMapping = $this->getPreviousMapping('users');
                $newUserId = $userMapping[$oldUserId] ?? null;
                if ($newUserId) {
                    DB::table('users')
                        ->where('id', $newUserId)
                        ->update(['dealer_id' => $existing->id]);
                }
            }
            return $existing->id;
        }

        $id = DB::table('dealers')->insertGetId($newData);

        if ($id && $oldId) {
            $this->idMapping[$oldId] = $id;

            // User'ın dealer_id'sini güncelle
            if ($oldUserId) {
                $userMapping = $this->getPreviousMapping('users');
                $newUserId = $userMapping[$oldUserId] ?? null;
                if ($newUserId) {
                    DB::table('users')
                        ->where('id', $newUserId)
                        ->update(['dealer_id' => $id]);

                    // User'a dealer_owner rolü ver
                    $dealerOwnerRole = DB::table('roles')
                        ->where('name', 'dealer_owner')
                        ->first();

                    if ($dealerOwnerRole) {
                        DB::table('model_has_roles')->insertOrIgnore([
                            'role_id' => $dealerOwnerRole->id,
                            'model_type' => 'App\\Models\\User',
                            'model_id' => $newUserId,
                        ]);
                    }
                }
            }
        }

        return $id;
    }

    /**
     * Dealer code oluştur
     */
    protected function generateDealerCode(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }

        // İlk 3 harfi al, büyük harfe çevir
        $code = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));

        if (strlen($code) < 3) {
            $code = str_pad($code, 3, 'X');
        }

        // Unique kontrolü
        $exists = DB::table('dealers')
            ->where('dealer_code', $code)
            ->exists();

        if ($exists) {
            // Sonuna sayı ekle
            $counter = 1;
            while ($exists) {
                $newCode = $code.str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
                $exists = DB::table('dealers')
                    ->where('dealer_code', $newCode)
                    ->exists();
                if (! $exists) {
                    $code = $newCode;
                    break;
                }
                $counter++;
            }
        }

        return $code;
    }
}

