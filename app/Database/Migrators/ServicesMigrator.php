<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServicesMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'services';
    }

    protected function getOldTableName(): string
    {
        return 'services';
    }

    protected function readOldData(): \Generator
    {
        // Customer bilgilerini önce yükle (phone, email için)
        $customerCache = [];
        $oldCustomers = $this->oldDb->table('customers')
            ->select('id', 'phone', 'email')
            ->get();
        
        foreach ($oldCustomers as $oldCustomer) {
            $customerCache[$oldCustomer->id] = [
                'phone' => $oldCustomer->phone ?? null,
                'email' => $oldCustomer->email ?? null,
            ];
        }

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

        $query = $this->oldDb()
            ->select('id', 'service_no', 'customer_id', 'worker_id', 'dealer_id', 'note', 'status', 'body', 'car', 'status_history', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            // Customer phone ve email'i cache'den ekle
            $rowArray['old_customer_phone'] = $customerCache[$rowArray['customer_id']]['phone'] ?? null;
            $rowArray['old_customer_email'] = $customerCache[$rowArray['customer_id']]['email'] ?? null;
            // User email ve phone'i cache'den ekle
            $rowArray['old_worker_email'] = $userCache[$rowArray['worker_id']]['email'] ?? null;
            $rowArray['old_worker_phone'] = $userCache[$rowArray['worker_id']]['phone'] ?? null;
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Customer ID mapping - önce mapping'den bak
        $customerMapping = $this->getPreviousMapping('customers');
        $newCustomerId = $customerMapping[$oldData['customer_id']] ?? null;

        // Eğer mapping'de yoksa, phone ve email kombinasyonu ile yeni veritabanında ara
        if ($newCustomerId === null) {
            $oldPhone = $oldData['old_customer_phone'] ?? null;
            $oldEmail = $oldData['old_customer_email'] ?? null;

            if ($oldPhone || $oldEmail) {
                $query = DB::table('customers');
                
                // Phone ve email kombinasyonu ile ara
                if ($oldPhone && $oldEmail) {
                    $customer = $query->where('phone', $oldPhone)
                        ->where('email', $oldEmail)
                        ->first();
                } elseif ($oldPhone) {
                    // Sadece phone ile ara
                    $customer = $query->where('phone', $oldPhone)->first();
                } elseif ($oldEmail) {
                    // Sadece email ile ara
                    $customer = $query->where('email', $oldEmail)->first();
                } else {
                    $customer = null;
                }

                if ($customer) {
                    $newCustomerId = $customer->id;
                }
            }
        }

        // Hala bulunamadıysa, eski veritabanından customer bilgilerini al ve tekrar ara
        if ($newCustomerId === null) {
            $oldCustomer = $this->oldDb()
                ->table('customers')
                ->where('id', $oldData['customer_id'])
                ->first();
            
            if ($oldCustomer) {
                $oldPhone = $oldCustomer->phone ?? null;
                $oldEmail = $oldCustomer->email ?? null;

                if ($oldPhone || $oldEmail) {
                    $query = DB::table('customers');
                    
                    if ($oldPhone && $oldEmail) {
                        $customer = $query->where('phone', $oldPhone)
                            ->where('email', $oldEmail)
                            ->first();
                    } elseif ($oldPhone) {
                        $customer = $query->where('phone', $oldPhone)->first();
                    } elseif ($oldEmail) {
                        $customer = $query->where('email', $oldEmail)->first();
                    } else {
                        $customer = null;
                    }

                    if ($customer) {
                        $newCustomerId = $customer->id;
                    }
                }
            }
        }

        if ($newCustomerId === null) {
            $errorMsg = "Customer ID {$oldData['customer_id']} bulunamadı (phone: {$oldData['old_customer_phone']}, email: {$oldData['old_customer_email']})";
            $this->command->warn("Service ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // User ID mapping (worker_id -> user_id) - önce mapping'den bak
        $userMapping = $this->getPreviousMapping('users');
        $oldWorkerId = $this->safeIntCast($oldData['worker_id']);
        $newUserId = $oldWorkerId ? ($userMapping[$oldWorkerId] ?? null) : null;

        // Eğer mapping'de yoksa, email ve phone ile yeni veritabanında ara
        if ($newUserId === null && $oldWorkerId) {
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
                    $newUserId = $user->id;
                }
            }

            // Hala bulunamadıysa, eski veritabanından user bilgilerini al ve tekrar ara
            if ($newUserId === null) {
                $oldUser = $this->oldDb->table('users')
                    ->where('id', $oldWorkerId)
                    ->first();
                
                if ($oldUser) {
                    $oldEmail = $oldUser->email ?? null;
                    $oldPhone = $oldUser->phone ?? null;

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
                            $newUserId = $user->id;
                        }
                    }
                }
            }
        }

        if ($newUserId === null) {
            $errorMsg = "Worker ID {$oldData['worker_id']} bulunamadı (email: {$oldData['old_worker_email']}, phone: {$oldData['old_worker_phone']})";
            $this->command->warn("Service ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Dealer ID mapping (users -> dealers)
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
                }
            }
        }

        if ($dealerId === null) {
            $errorMsg = "Dealer ID bulunamadı";
            $this->command->warn("Service ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Car JSON parsing
        $carData = $this->safeJsonDecode($oldData['car']);
        if (! $carData) {
            $errorMsg = "Car JSON parse edilemedi";
            $this->command->warn("Service ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Car brand ve model mapping - JSON'dan brand ve model string değerleri ile
        $carBrandId = null;
        $carModelId = null;
        $year = null;

        // Brand mapping - JSON'dan brand string değerini al
        if (isset($carData['brand']) && !empty($carData['brand'])) {
            $brandName = $this->normalizeCarName($carData['brand']);
            
            // 1. Tam eşleşme (case-insensitive)
            $newBrand = DB::table('car_brands')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($brandName))])
                ->first();
            
            if ($newBrand) {
                $carBrandId = $newBrand->id;
            } else {
                // 2. Boşlukları kaldırarak eşleşme
                $normalizedBrand = $this->normalizeCarName($brandName);
                $newBrand = DB::table('car_brands')
                    ->get()
                    ->first(function ($brand) use ($normalizedBrand) {
                        return $this->normalizeCarName($brand->name) === $normalizedBrand;
                    });
                
                if ($newBrand) {
                    $carBrandId = $newBrand->id;
                } else {
                    // 3. LIKE ile arama (içinde geçiyor mu)
                    $newBrand = DB::table('car_brands')
                        ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($brandName).'%'])
                        ->first();
                    
                    if ($newBrand) {
                        $carBrandId = $newBrand->id;
                    } else {
                        // 4. Tersine LIKE (brand name içinde aranan geçiyor mu)
                        $newBrand = DB::table('car_brands')
                            ->get()
                            ->first(function ($brand) use ($brandName) {
                                return stripos($brand->name, $brandName) !== false || stripos($brandName, $brand->name) !== false;
                            });
                        
                        if ($newBrand) {
                            $carBrandId = $newBrand->id;
                        }
                    }
                }
            }
        }

        // Model mapping - JSON'dan model string değerini al ve brand_id ile birlikte ara
        if (isset($carData['model']) && !empty($carData['model'])) {
            $modelName = $this->normalizeCarName($carData['model']);
            
            // Önce brand_id ile birlikte ara (daha kesin)
            if ($carBrandId !== null) {
                // 1. Tam eşleşme (case-insensitive)
                $newModel = DB::table('car_models')
                    ->where('brand_id', $carBrandId)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($modelName))])
                    ->first();
                
                if ($newModel) {
                    $carModelId = $newModel->id;
                } else {
                    // 2. Normalize edilmiş eşleşme (boşluklar, özel karakterler)
                    $normalizedModel = $this->normalizeCarName($modelName);
                    $newModel = DB::table('car_models')
                        ->where('brand_id', $carBrandId)
                        ->get()
                        ->first(function ($model) use ($normalizedModel) {
                            return $this->normalizeCarName($model->name) === $normalizedModel;
                        });
                    
                    if ($newModel) {
                        $carModelId = $newModel->id;
                    } else {
                        // 3. LIKE ile arama (içinde geçiyor mu)
                        $newModel = DB::table('car_models')
                            ->where('brand_id', $carBrandId)
                            ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($modelName).'%'])
                            ->first();
                        
                        if ($newModel) {
                            $carModelId = $newModel->id;
                        } else {
                            // 4. Tersine LIKE (model name içinde aranan geçiyor mu)
                            $newModel = DB::table('car_models')
                                ->where('brand_id', $carBrandId)
                                ->get()
                                ->first(function ($model) use ($modelName) {
                                    return stripos($model->name, $modelName) !== false || stripos($modelName, $model->name) !== false;
                                });
                            
                            if ($newModel) {
                                $carModelId = $newModel->id;
                            }
                        }
                    }
                }
            }
            
            // Eğer brand_id ile bulunamadıysa, sadece model name ile ara (daha az kesin)
            if ($carModelId === null) {
                // 1. Tam eşleşme
                $newModel = DB::table('car_models')
                    ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($modelName))])
                    ->first();
                
                if ($newModel) {
                    $carModelId = $newModel->id;
                    if ($carBrandId === null) {
                        $carBrandId = $newModel->brand_id;
                    }
                } else {
                    // 2. Normalize edilmiş eşleşme
                    $normalizedModel = $this->normalizeCarName($modelName);
                    $newModel = DB::table('car_models')
                        ->get()
                        ->first(function ($model) use ($normalizedModel) {
                            return $this->normalizeCarName($model->name) === $normalizedModel;
                        });
                    
                    if ($newModel) {
                        $carModelId = $newModel->id;
                        if ($carBrandId === null) {
                            $carBrandId = $newModel->brand_id;
                        }
                    } else {
                        // 3. LIKE ile arama
                        $newModel = DB::table('car_models')
                            ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($modelName).'%'])
                            ->first();
                        
                        if ($newModel) {
                            $carModelId = $newModel->id;
                            if ($carBrandId === null) {
                                $carBrandId = $newModel->brand_id;
                            }
                        }
                    }
                }
            }
        }

        // Year parsing
        if (isset($carData['year'])) {
            $year = is_numeric($carData['year']) ? (int) $carData['year'] : null;
        }

        if ($carBrandId === null || $carModelId === null) {
            $brandInfo = isset($carData['brand']) ? "brand: '{$carData['brand']}'" : 'brand yok';
            $modelInfo = isset($carData['model']) ? "model: '{$carData['model']}'" : 'model yok';
            $errorMsg = "Car brand/model bulunamadı ({$brandInfo}, {$modelInfo})";
            $this->command->warn("Service ID {$oldData['id']}: {$errorMsg}, atlanıyor.");
            $this->setTransformError($errorMsg);
            return null;
        }

        // Status enum -> varchar mapping
        $oldStatus = $this->enumToString($oldData['status'] ?? 'pending');
        $status = match($oldStatus) {
            'pending' => 'pending',
            'progress' => 'processing',
            'completed' => 'completed',
            'cancelled' => 'cancelled',
            default => 'pending',
        };

        // Applied parts (body'den parse edilebilir veya boş)
        $appliedParts = json_decode($oldData['body'] ?? '[]', true);

        return [
            'old_id' => $oldData['old_id'],
            'old_status_history' => $oldData['status_history'], // Sonra service_status_logs'a aktarılacak
            'service_no' => $oldData['service_no'],
            'dealer_id' => $dealerId,
            'customer_id' => $newCustomerId,
            'user_id' => $newUserId,
            'car_brand_id' => $carBrandId,
            'car_model_id' => $carModelId,
            'year' => $year ?? 2000, // Fallback
            'vin' => $carData['vin'] ?? null,
            'plate' => $carData['plate'] ?? '',
            'km' => isset($carData['km']) ? (int) $carData['km'] : null,
            'package' => $carData['package'] ?? null,
            'applied_parts' => $oldData['body'] ?? null,
            'notes' => $oldData['note'] ?? $oldData['body'] ?? null,
            'status' => $status,
            'completed_at' => $status === 'completed' ? $oldData['updated_at'] : null,
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        $oldStatusHistory = $newData['old_status_history'] ?? null;
        unset($newData['old_id'], $newData['old_status_history']);

        // Service no unique kontrolü
        $existing = DB::table('services')
            ->where('service_no', $newData['service_no'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('services')->insertGetId($newData);

        if ($id && $oldId) {
            $this->idMapping[$oldId] = $id;

            // Status history'yi service_status_logs'a aktar
            if ($oldStatusHistory) {
                $this->migrateStatusHistory($oldId, $id, $oldStatusHistory);
            }
        }

        return $id;
    }

    /**
     * Status history'yi service_status_logs'a aktar
     */
    protected function migrateStatusHistory(int $oldServiceId, int $newServiceId, string $statusHistoryJson): void
    {
        $history = $this->safeJsonDecode($statusHistoryJson);
        if (! $history || ! is_array($history)) {
            return;
        }

        $userMapping = $this->getPreviousMapping('users');
        $dealerMapping = $this->getPreviousMapping('dealers');

        foreach ($history as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $userId = 1; // Fallback
            if (isset($entry['user_id'])) {
                $oldUserId = (int) $entry['user_id'];
                $userId = $userMapping[$oldUserId] ?? $userId;
            }

            $fromDealerId = null;
            if (isset($entry['from_dealer_id'])) {
                $oldFromDealerUserId = (int) $entry['from_dealer_id'];
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldFromDealerUserId)
                    ->first();
                if ($dealerDetail) {
                    $fromDealerId = $dealerMapping[$dealerDetail->id] ?? null;
                }
            }

            $toDealerId = null;
            if (isset($entry['to_dealer_id'])) {
                $oldToDealerUserId = (int) $entry['to_dealer_id'];
                $dealerDetail = $this->oldDb->table('dealer_details')
                    ->where('user_id', $oldToDealerUserId)
                    ->first();
                if ($dealerDetail) {
                    $toDealerId = $dealerMapping[$dealerDetail->id] ?? null;
                }
            }

            // DateTime formatını düzelt (ISO 8601 -> MySQL datetime)
            $createdAt = $this->parseDateTime($entry['created_at'] ?? null) ?? now();
            $updatedAt = $this->parseDateTime($entry['updated_at'] ?? null) ?? now();

            DB::table('service_status_logs')->insert([
                'service_id' => $newServiceId,
                'from_dealer_id' => $fromDealerId,
                'to_dealer_id' => $toDealerId,
                'user_id' => $userId,
                'notes' => $entry['notes'] ?? null,
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);
        }
    }

    /**
     * DateTime string'ini MySQL datetime formatına çevir
     */
    protected function parseDateTime(?string $dateTimeString): ?Carbon
    {
        if (empty($dateTimeString)) {
            return null;
        }

        try {
            // ISO 8601 formatını (2024-08-21T10:06:17.216901Z) MySQL datetime formatına çevir
            return Carbon::parse($dateTimeString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Car name'i normalize et (boşluklar, özel karakterler, büyük/küçük harf)
     */
    protected function normalizeCarName(string $name): string
    {
        // Trim
        $normalized = trim($name);
        
        // Tüm boşlukları tek boşluğa indir
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Özel karakterleri normalize et
        $normalized = str_replace(['-', '_', '.', ',', ':', ';', '!', '?', '(', ')', '[', ']', '{', '}', '/', '\\'], ' ', $normalized);
        
        // Tekrar boşlukları temizle
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Küçük harfe çevir
        $normalized = strtolower($normalized);
        
        // Trim
        $normalized = trim($normalized);
        
        return $normalized;
    }
}

