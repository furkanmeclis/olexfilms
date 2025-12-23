<?php

namespace App\Filament\Resources\ServiceStatusLogs\Pages;

use App\Filament\Resources\ServiceStatusLogs\ServiceStatusLogResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceStatusLogs extends ListRecords
{
    protected static string $resource = ServiceStatusLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
