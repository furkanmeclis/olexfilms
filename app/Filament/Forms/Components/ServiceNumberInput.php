<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;

class ServiceNumberInput extends Field
{
    protected string $view = 'filament.forms.components.service-number-input';

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(null);
        
        // Backend validation - unique kontrolü ve stok item kontrolü backend'de yapılacak
        // Frontend'de sadece required kontrolü yapılıyor
        $this->rules([
            'required',
            'string',
            'max:255',
        ]);
    }
}

