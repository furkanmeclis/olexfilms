<?php

namespace App\Filament\Resources\BulkSms\Pages;

use App\Filament\Resources\BulkSms\BulkSmsResource;
use App\Jobs\SendBulkSmsJob;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewBulkSms extends ViewRecord
{
    protected static string $resource = BulkSmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Gönder')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === 'draft')
                ->action(function () {
                    SendBulkSmsJob::dispatch($this->record->id);
                    $this->dispatch('notification', [
                        'type' => 'success',
                        'message' => 'Toplu SMS gönderimi başlatıldı.',
                    ]);
                }),
        ];
    }
}
