<?php

namespace App\Enums;

enum ServiceItemUsageTypeEnum: string
{
    case FULL = 'full';
    case PARTIAL = 'partial';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::FULL->value => 'Tamamı',
            self::PARTIAL->value => 'Parça',
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
