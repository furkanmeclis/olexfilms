<?php

namespace App\Filament\Resources\NexptgReports\Pages;

use App\Filament\Resources\NexptgReports\NexptgReportResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNexptgReport extends EditRecord
{
    protected static string $resource = NexptgReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Görüntüle'),
            DeleteAction::make()
                ->label('Sil'),
        ];
    }
}
