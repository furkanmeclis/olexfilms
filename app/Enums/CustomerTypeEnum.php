<?php

namespace App\Enums;

enum CustomerTypeEnum: string
{
    case INDIVIDUAL = 'individual';
    case CORPORATE = 'corporate';

    /**
     * Get all labels as key-value array
     *
     * @return array<string, string>
     */
    public static function getLabels(): array
    {
        return [
            self::INDIVIDUAL->value => 'Bireysel',
            self::CORPORATE->value => 'Kurumsal',
        ];
    }

    /**
     * Get the label for this enum case
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::getLabels()[$this->value] ?? $this->value;
    }
}

