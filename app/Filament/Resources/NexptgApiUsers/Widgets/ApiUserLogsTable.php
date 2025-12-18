<?php

namespace App\Filament\Resources\NexptgApiUsers\Widgets;

use App\Enums\NexptgApiLogTypeEnum;
use App\Models\NexptgApiUser;
use App\Models\NexptgApiUserLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApiUserLogsTable extends TableWidget
{
    public ?Model $record = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                if (! $this->record instanceof NexptgApiUser) {
                    return NexptgApiUserLog::query()->whereRaw('1 = 0'); // Empty query
                }

                return NexptgApiUserLog::query()
                    ->where('nexptg_api_user_id', $this->record->id)
                    ->latest();
            })
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Tip')
                    ->formatStateUsing(fn ($state) => NexptgApiLogTypeEnum::getLabels()[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        NexptgApiLogTypeEnum::AUTH_ERROR->value => 'danger',
                        NexptgApiLogTypeEnum::VALIDATION_ERROR->value => 'warning',
                        NexptgApiLogTypeEnum::SYNC_ERROR->value => 'warning',
                        NexptgApiLogTypeEnum::EXCEPTION->value => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_code')
                    ->label('Durum Kodu')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        400 => 'warning',
                        401 => 'warning',
                        403 => 'danger',
                        500 => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('message')
                    ->label('Mesaj')
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->message)
                    ->searchable(),

                TextColumn::make('details')
                    ->label('Detaylar')
                    ->formatStateUsing(fn ($state) => $state ? 'Detayları görmek için tıklayın' : '-')
                    ->tooltip(function ($record) {
                        if (! $record->details) {
                            return null;
                        }

                        return json_encode($record->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    })
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }
}
