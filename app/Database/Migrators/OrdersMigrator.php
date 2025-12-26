<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class OrdersMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'orders';
    }

    protected function getOldTableName(): string
    {
        return 'orders';
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
                'user_id' => $dealerDetail->user_id,
                'email' => $dealerDetail->company_email ?? null,
            ];
        }

        $query = $this->oldDb()
            ->select('id', 'dealer_id', 'user_id', 'note', 'status', 'tracking_code', 'tracking_url', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // User email ve phone'i cache'den ekle
            $rowArray['old_user_email'] = $userCache[$rowArray['user_id']]['email'] ?? null;
            $rowArray['old_user_phone'] = $userCache[$rowArray['user_id']]['phone'] ?? null;
            // Dealer user_id ve email'i cache'den ekle (dealer_id bir user_id)
            $oldDealerUserId = $this->safeIntCast($rowArray['dealer_id']);
            if ($oldDealerUserId) {
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                if ($dealerDetail) {
                    $rowArray['old_dealer_detail_id'] = $dealerDetail->id;
                    $rowArray['old_dealer_email'] = $dealerDetail->company_email ?? null;
                }
            }
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Dealer ID mapping (users -> dealers) - önce mapping'den bak
        $dealerId = null;
        if (!empty($oldData['dealer_id'])) {
            $oldDealerUserId = $this->safeIntCast($oldData['dealer_id']);
            if ($oldDealerUserId) {
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldDealerUserId)
                    ->first();
                
                if ($dealerDetail) {
                    $dealerMapping = $this->getPreviousMapping('dealers');
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

        if ($dealerId === null) {
            $this->command->warn("Order ID {$oldData['id']}: Dealer ID bulunamadı, atlanıyor.");
            return null;
        }

        // Created by mapping (user_id -> created_by) - önce mapping'den bak
        $createdBy = null;
        if (!empty($oldData['user_id'])) {
            $userMapping = $this->getPreviousMapping('users');
            $oldUserId = (int) $oldData['user_id'];
            $createdBy = $userMapping[$oldUserId] ?? null;

            // Eğer mapping'de yoksa, email ve phone ile yeni veritabanında ara
            if ($createdBy === null) {
                $oldEmail = $oldData['old_user_email'] ?? null;
                $oldPhone = $oldData['old_user_phone'] ?? null;

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

        if ($createdBy === null) {
            $createdBy = 1; // Fallback
        }

        // Status enum -> varchar mapping
        $oldStatus = $this->enumToString($oldData['status'] ?? 'pending');
        $status = match($oldStatus) {
            'draft' => 'pending',
            'pending' => 'pending',
            'processing' => 'processing',
            'shipping' => 'shipped',
            'completed' => 'delivered',
            'cancelled' => 'cancelled',
            'refunded' => 'cancelled',
            default => 'pending',
        };

        return [
            'old_id' => $oldData['old_id'],
            'dealer_id' => $dealerId,
            'created_by' => $createdBy,
            'status' => $status,
            'cargo_company' => null, // Eski şemada yok
            'tracking_number' => $oldData['tracking_code'], // tracking_code -> tracking_number
            'notes' => $oldData['note'], // note -> notes
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        $id = DB::table('orders')->insertGetId($newData);

        if ($id && $oldId) {
            $this->idMapping[$oldId] = $id;

            // Order items'ı migrate et
            $this->migrateOrderItems($oldId, $id);
        }

        return $id;
    }

    /**
     * Order items'ı migrate et
     */
    protected function migrateOrderItems(int $oldOrderId, int $newOrderId): void
    {
        $orderItems = $this->oldDb->table('order_items')
            ->where('order_id', $oldOrderId)
            ->get();

        $productMapping = $this->getPreviousMapping('products');

        foreach ($orderItems as $oldItem) {
            $newProductId = $productMapping[$oldItem->product_id] ?? null;

            if ($newProductId === null) {
                continue;
            }

            $newItemId = DB::table('order_items')->insertGetId([
                'order_id' => $newOrderId,
                'product_id' => $newProductId,
                'quantity' => $oldItem->quantity,
                'created_at' => $oldItem->created_at,
                'updated_at' => $oldItem->updated_at,
            ]);

            // Order item stock ilişkilerini migrate et (eğer varsa)
            // Bu kısım product_codes tablosundan order_id ile bulunabilir
        }
    }
}

