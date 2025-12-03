<?php

namespace App\Filament\Resources\CarBrands\Pages;

use App\Filament\Resources\CarBrands\CarBrandResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCarBrand extends ViewRecord
{
    protected static string $resource = CarBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
