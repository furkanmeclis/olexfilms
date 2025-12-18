<?php

namespace App\Filament\Resources\ServiceStatusLogs\Pages;

use App\Filament\Resources\ServiceStatusLogs\ServiceStatusLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceStatusLog extends CreateRecord
{
    protected static string $resource = ServiceStatusLogResource::class;
}
