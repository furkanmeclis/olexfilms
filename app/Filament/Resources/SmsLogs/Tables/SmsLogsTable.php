<?php

namespace App\Filament\Resources\SmsLogs\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Mesaj')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->message)
                    ->searchable(),

                TextColumn::make('sender')
                    ->label('Gönderici')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Beklemede',
                        'sent' => 'Gönderildi',
                        'failed' => 'Başarısız',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Kalan SMS Adedi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('SMS Adedi')
                    ->numeric()
                    ->suffix(' Adet')
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Gönderim Zamanı')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('sentBy.name')
                    ->label('Gönderen')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options([
                        'pending' => 'Beklemede',
                        'sent' => 'Gönderildi',
                        'failed' => 'Başarısız',
                    ]),

                SelectFilter::make('sender')
                    ->label('Gönderici')
                    ->options(function () {
                        return \App\Models\SmsLog::query()
                            ->distinct()
                            ->pluck('sender', 'sender')
                            ->toArray();
                    }),

                Filter::make('sent_at')
                    ->label('Gönderim Tarihi')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('sent_from')
                            ->label('Başlangıç'),
                        \Filament\Forms\Components\DatePicker::make('sent_until')
                            ->label('Bitiş'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                ]),
            ])
            ->defaultSort('sent_at', 'desc');
    }
}
