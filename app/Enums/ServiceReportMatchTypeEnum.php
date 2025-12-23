<?php

namespace App\Enums;

enum ServiceReportMatchTypeEnum: string
{
    case BEFORE = 'before';
    case AFTER = 'after';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::BEFORE->value => 'Hizmet Öncesi',
            self::AFTER->value => 'Hizmet Sonrası',
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
