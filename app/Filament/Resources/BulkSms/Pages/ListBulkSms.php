<?php

namespace App\Filament\Resources\BulkSms\Pages;

use App\Filament\Resources\BulkSms\BulkSmsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBulkSms extends ListRecords
{
    protected static string $resource = BulkSmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
