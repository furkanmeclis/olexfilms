<?php

namespace App\Filament\Resources\NexptgReports\Pages;

use App\Filament\Resources\NexptgReports\NexptgReportResource;
use Filament\Resources\Pages\ListRecords;

class ListNexptgReports extends ListRecords
{
    protected static string $resource = NexptgReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action removed - reports can only be created via API sync
        ];
    }
}
