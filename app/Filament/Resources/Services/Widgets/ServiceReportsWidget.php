<?php

namespace App\Filament\Resources\Services\Widgets;

use App\Enums\ServiceReportMatchTypeEnum;
use App\Filament\Resources\NexptgReports\NexptgReportResource;
use App\Models\NexptgReport;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ServiceReportsWidget extends TableWidget
{
    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (! $this->record instanceof Service) {
                    return NexptgReport::query()->whereRaw('1 = 0'); // Empty query
                }

                return NexptgReport::query()
                    ->join('service_nexptg_report', 'nexptg_reports.id', '=', 'service_nexptg_report.nexptg_report_id')
                    ->where('service_nexptg_report.service_id', $this->record->id)
                    ->select('nexptg_reports.*', 'service_nexptg_report.match_type', 'service_nexptg_report.created_at as match_created_at')
                    ->with(['measurements', 'tires', 'apiUser.user'])
                    ->orderBy('service_nexptg_report.created_at', 'desc');
            })
            ->columns([
                TextColumn::make('match_type')
                    ->label('Eşleşme Tipi')
                    ->formatStateUsing(fn ($state) => $state ? ServiceReportMatchTypeEnum::from($state)->getLabel() : '-')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        ServiceReportMatchTypeEnum::BEFORE->value => 'warning',
                        ServiceReportMatchTypeEnum::AFTER->value => 'success',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        ServiceReportMatchTypeEnum::BEFORE->value => 'heroicon-o-arrow-down',
                        ServiceReportMatchTypeEnum::AFTER->value => 'heroicon-o-arrow-up',
                        default => null,
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Rapor Adı')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('measurements_count')
                    ->label('Ölçüm Sayısı')
                    ->counts('measurements')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('tires_count')
                    ->label('Lastik Sayısı')
                    ->counts('tires')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('apiUser.user.name')
                    ->label('API Kullanıcısı')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Bilinmiyor')
                    ->sortable(),

                TextColumn::make('match_created_at')
                    ->label('Eşleşme Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Görüntüle')
                    ->icon('heroicon-o-eye')
                    ->url(fn (NexptgReport $record) => NexptgReportResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),

                Action::make('detach')
                    ->label('Eşleşmeyi Kaldır')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eşleşmeyi Kaldır')
                    ->modalDescription('Bu raporu hizmetten ayırmak istediğinize emin misiniz?')
                    ->modalSubmitActionLabel('Evet, Kaldır')
                    ->modalCancelActionLabel('İptal')
                    ->action(function (NexptgReport $record) {
                        if ($this->record instanceof Service) {
                            $this->record->reports()->detach($record->id);

                            \Filament\Notifications\Notification::make()
                                ->title('Eşleşme kaldırıldı')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('service_nexptg_report.created_at', 'desc')
            ->poll('30s')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Henüz eşleşen rapor yok')
            ->emptyStateDescription('Bu hizmete rapor eşleştirmek için "Rapor Eşleştir" butonunu kullanın.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function getHeading(): string
    {
        if (! $this->record instanceof Service) {
            return 'Ölçüm Eşleştirmeleri';
        }

        $beforeCount = $this->record->beforeReports()->count();
        $afterCount = $this->record->afterReports()->count();

        return "Ölçüm Eşleştirmeleri ({$beforeCount} Öncesi, {$afterCount} Sonrası)";
    }
}
