<?php

namespace App\Enums;

enum ServiceStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case READY = 'ready';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::DRAFT->value => 'Taslak',
            self::PENDING->value => 'Bekliyor',
            self::PROCESSING->value => 'İşlemde',
            self::READY->value => 'Hazır',
            self::COMPLETED->value => 'Tamamlandı',
            self::CANCELLED->value => 'İptal Edildi',
        ];
    }

    /**
     * Get the label for this enum case
     */
    public function getLabel(): string
    {
        return self::getLabels()[$this->value] ?? $this->value;
    }
}
