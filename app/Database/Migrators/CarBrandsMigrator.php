<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use App\Models\CarBrand;
use Illuminate\Support\Facades\DB;

class CarBrandsMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'car_brands';
    }

    protected function getOldTableName(): string
    {
        return 'car_brands';
    }

    protected function readOldData(): \Generator
    {
        $query = $this->oldDb()
            ->select('id', 'name', 'external_id', 'logo', 'last_update', 'is_active', 'created_at', 'updated_at', 'deleted_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id']; // Eski ID'yi sakla
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        // Logo'yu storage'a kaydet (eğer URL veya path ise)
        $logoPath = null;
        if (!empty($oldData['logo'])) {
            $logoPath = $this->saveImageToStorage($oldData['logo'], 'car-brands');
        }

        return [
            'old_id' => $oldData['old_id'],
            'name' => $oldData['name'],
            'external_id' => $oldData['external_id'],
            'logo' => $logoPath,
            'last_update' => $oldData['last_update'],
            'is_active' => $this->boolToTinyint((bool) $oldData['is_active']),
            'show_name' => 1, // Default değer, eski şemada yok
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
            'deleted_at' => $oldData['deleted_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']); // old_id'yi kaldır

        // External ID unique kontrolü
        $existing = DB::table('car_brands')
            ->where('external_id', $newData['external_id'])
            ->first();

        if ($existing) {
            // Zaten varsa, ID mapping'e ekle ama yeni kayıt oluşturma
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('car_brands')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }
}

