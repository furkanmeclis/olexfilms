<?php

declare(strict_types=1);

namespace App\Database\Migrators;

use Illuminate\Support\Facades\DB;

class ProductCategoriesMigrator extends BaseMigrator
{
    protected function getTableName(): string
    {
        return 'product_categories';
    }

    protected function getOldTableName(): string
    {
        return 'categories';
    }

    protected function readOldData(): \Generator
    {
        $query = $this->oldDb()
            ->select('id', 'name', 'created_at', 'updated_at')
            ->orderBy('id');

        foreach ($query->cursor() as $row) {
            $rowArray = (array) $row;
            $rowArray['old_id'] = $rowArray['id'];
            yield $rowArray;
        }
    }

    protected function transformData(array $oldData): ?array
    {
        return [
            'old_id' => $oldData['old_id'],
            'name' => $oldData['name'],
            'available_parts' => json_encode([]), // Default boş array
            'is_active' => 1,
            'created_at' => $oldData['created_at'],
            'updated_at' => $oldData['updated_at'],
        ];
    }

    protected function saveNewData(array $newData): ?int
    {
        $oldId = $newData['old_id'] ?? null;
        unset($newData['old_id']);

        // Name unique kontrolü
        $existing = DB::table('product_categories')
            ->where('name', $newData['name'])
            ->first();

        if ($existing) {
            if ($oldId) {
                $this->idMapping[$oldId] = $existing->id;
            }
            return $existing->id;
        }

        $id = DB::table('product_categories')->insertGetId($newData);

        if ($oldId && $id) {
            $this->idMapping[$oldId] = $id;
        }

        return $id;
    }
}

