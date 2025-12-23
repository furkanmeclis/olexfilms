<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class CarPartPicker extends Field
{
    protected string $view = 'filament.forms.components.car-part-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);
        $this->dehydrated(true);

        // Hydrate: JSON string'den array'e çevir
        $this->afterStateHydrated(function (CarPartPicker $component, $state) {
            if (is_string($state)) {
                $decoded = json_decode($state, true);
                $component->state(is_array($decoded) ? $decoded : []);
            } elseif (! is_array($state)) {
                $component->state([]);
            }
        });

        // Dehydrate: Array'den JSON string'e çevir (sadece submit anında)
        // NOT: Model'de 'array' cast var, bu yüzden Laravel otomatik encode yapacak
        // Burada sadece array ise olduğu gibi bırak, Laravel model cast'i halledecek
        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                // Array ise olduğu gibi döndür, Laravel model cast'i JSON'a çevirecek
                return $state;
            }
            // String ise decode et ve array'e çevir
            if (is_string($state)) {
                $decoded = json_decode($state, true);

                return is_array($decoded) ? $decoded : [];
            }

            return [];
        });
    }
}
