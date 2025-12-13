<?php

namespace App\Filament\Resources\NexptgReports\Pages;

use App\Filament\Resources\NexptgReports\NexptgReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNexptgReport extends ViewRecord
{
    protected static string $resource = NexptgReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Düzenle'),
        ];
    }
}
