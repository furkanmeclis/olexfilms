<?php

namespace App\Filament\Resources\BulkSms\Pages;

use App\Filament\Resources\BulkSms\BulkSmsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBulkSms extends EditRecord
{
    protected static string $resource = BulkSmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
