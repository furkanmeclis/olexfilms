<?php

namespace App\Filament\Resources\NexptgApiUsers\Pages;

use App\Filament\Resources\NexptgApiUsers\NexptgApiUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNexptgApiUsers extends ListRecords
{
    protected static string $resource = NexptgApiUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
