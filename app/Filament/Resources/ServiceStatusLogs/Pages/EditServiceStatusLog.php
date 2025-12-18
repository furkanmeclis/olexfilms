<?php

namespace App\Filament\Resources\ServiceStatusLogs\Pages;

use App\Filament\Resources\ServiceStatusLogs\ServiceStatusLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceStatusLog extends EditRecord
{
    protected static string $resource = ServiceStatusLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
