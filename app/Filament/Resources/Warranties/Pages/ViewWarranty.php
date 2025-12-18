<?php

namespace App\Filament\Resources\Warranties\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Warranties\WarrantyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWarranty extends ViewRecord
{
    protected static string $resource = WarrantyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewService')
                ->label('Hizmeti Görüntüle')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->url(fn () => $this->record->service 
                    ? ServiceResource::getUrl('view', ['record' => $this->record->service])
                    : null)
                ->visible(fn () => $this->record->service !== null),
        ];
    }
}

