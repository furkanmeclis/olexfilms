<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class CustomersMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'customers';
    }

    protected function getOldTableName(): string
    {
        return 'customers';
    }

    protected function readOldData(): \Generator
    {
        // User bilgilerini önce yükle (email, phone için)
        $userCache = [];
        $oldUsers = $this->oldDb->table('users')
            ->select('id', 'email', 'phone')
            ->get();
        
        foreach ($oldUsers as $oldUser) {
            $userCache[$oldUser->id] = [
                'email' => $oldUser->email ?? null,
                'phone' => $oldUser->phone ?? null,
            ];
        }

        // Dealer bilgilerini önce yükle (email için)
        $dealerCache = [];
        $oldDealerDetails = $this->oldDb->table('dealer_details')
            ->select('id', 'user_id', 'company_email')
            ->get();
        
        foreach ($oldDealerDetails as $dealerDetail) {
            $dealerCache[$dealerDetail->id] = [
                'email' => $dealerDetail->company_email ?? null,
            ];
        }

        $query = $this->oldDb()
            ->select(
                'id',
                'type',
                'dealer_id',
                'worker_id',
                'name',
                'email',
                'phone',
                'player_id',
                'address',
                'vat_name',
                'vat_number',
                'vat_office',
                'notification_settings',
                'created_at',
                'updated_at'
            )
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // Worker (user) email ve phone'i cache'den ekle
            $oldWorkerId = $this->safeIntCast($rowArray['worker_id']);
            if ($oldWorkerId) {
                $rowArray['old_worker_email'] = $userCache[$oldWorkerId]['email'] ?? null;
                $rowArray['old_worker_phone'] = $userCache[$oldWorkerId]['phone'] ?? null;
            }
            // Dealer email'i cache'den ekle
            $oldDealerUserId = $this->safeIntCast($rowArray['dealer_id']);
            if ($oldDealerUserId) {
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                if ($dealerDetail) {
                    $rowArray['old_dealer_detail_id'] = $dealerDetail->id;
                    $rowArray['old_dealer_email'] = $dealerCache[$dealerDetail->id]['email'] ?? null;
                }
            }
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Dealer ID mapping (varchar -> bigint) - önce mapping'den bak
        $dealerId = null;
        if (!empty($oldData['dealer_id'])) {
            // Eski şemada dealer_id varchar, users tablosuna referans
            // Yeni şemada dealers tablosuna referans
            $dealerMapping = $this->getPreviousMapping('dealers');
            $oldDealerUserId = $this->safeIntCast($oldData['dealer_id']);
            
            if ($oldDealerUserId) {
                // Eski dealer_id bir user_id, onu dealer'a map et
                // dealer_details'ta user_id ile dealer bul
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                
                if ($dealerDetail) {
                    $dealerId = $dealerMapping[$dealerDetail->id] ?? null;

                    // Eğer mapping'de yoksa, email ile yeni veritabanında ara
                    if ($dealerId === null && !empty($oldData['old_dealer_email'])) {
                        $dealer = DB::table('dealers')
                            ->where('email', $oldData['old_dealer_email'])
                            ->first();
                        
                        if ($dealer) {
                            $dealerId = $dealer->id;
                        }
                    }
                }
            }
        }

        // Created by mapping (worker_id -> created_by) - önce mapping'den bak
        $createdBy = null;
        if (!empty($oldData['worker_id'])) {
            $userMapping = $this->getPreviousMapping('users');
            $oldWorkerId = $this->safeIntCast($oldData['worker_id']);
            if ($oldWorkerId) {
                $createdBy = $userMapping[$oldWorkerId] ?? null;

                // Eğer mapping'de yoksa, email ve phone ile yeni veritabanında ara
                if ($createdBy === null) {
                    $oldEmail = $oldData['old_worker_email'] ?? null;
                    $oldPhone = $oldData['old_worker_phone'] ?? null;

                    if ($oldEmail || $oldPhone) {
                        $query = DB::table('users');
                        
                        if ($oldEmail && $oldPhone) {
                            $user = $query->where('email', $oldEmail)
                                ->where('phone', $oldPhone)
                                ->first();
                        } elseif ($oldEmail) {
                            $user = $query->where('email', $oldEmail)->first();
                        } elseif ($oldPhone) {
                            $user = $query->where('phone', $oldPhone)->first();
                        } else {
                            $user = null;
                        }

                        if ($user) {
                            $createdBy = $user->id;
                        }
                    }
                }
            }
        }

        // Type enum -> varchar mapping (company -> corporate)
        $oldType = $this->enumToString($oldData['type'] ?? 'individual');
        $type = match($oldType) {
            'company' => 'corporate',
            'individual' => 'individual',
            default => 'individual',
        };

        // Notification settings JSON
        $notificationSettings = $this->safeJsonDecode($oldData['notification_settings'] ?? '{"email":true,"sms":true,"push":false}');

        return [
            'old_id' => $oldData['old_id'],
            'dealer_id' => $dealerId,
            'created_by' => $createdBy ?? 1, // Fallback to first user
            'type' => $type,
            'tc_no' => null, // Eski şemada yok
            'tax_no' => $oldData['vat_number'],
            'tax_office' => $oldData['vat_office'],
            'name' => $oldData['name'],
            'phone' => $oldData['phone'] ?? '',
            'email' => $oldData['email'],
            'address' => $oldData['address'],
            'city' => null, // Eski şemada yok
            'district' => null, // Eski şemada yok
            'fcm_token' => $oldData['player_id'], // player_id -> fcm_token
            'notification_settings' => $notificationSettings ? json_encode($notificationSettings) : null,
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        $id = DB::table('customers')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }
}

