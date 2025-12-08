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
            } elseif (!is_array($state)) {
                $component->state([]);
            }
        });
        
        // Dehydrate: Array'den JSON string'e çevir (sadece submit anında)
        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return json_encode($state);
            }
            return is_string($state) ? $state : json_encode([]);
        });
    }
}

