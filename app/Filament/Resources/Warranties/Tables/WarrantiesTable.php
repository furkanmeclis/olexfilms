<?php

namespace App\Filament\Resources\Warranties\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Tapp\FilamentProgressBarColumn\Tables\Columns\ProgressBarColumn;

class WarrantiesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));

        return $table
            ->columns([
                TextColumn::make('service.service_no')
                    ->label('Hizmet No')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('stockItem.barcode')
                    ->label('Barkod')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('stockItem.product.name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Başlangıç')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Bitiş')
                    ->date('d.m.Y')
                    ->sortable(),

                ProgressBarColumn::make('days_remaining')
                    ->label('Kalan Gün')
                    ->maxValue(fn ($record) => $record->total_days ?? 1)
                    ->lowThreshold(fn ($record) => (int) round(($record->total_days ?? 1) * 0.3))
                    ->dangerColor('rgb(239, 68, 68)')
                    ->warningColor('rgb(245, 158, 11)')
                    ->successColor('rgb(34, 197, 94)')
                    ->dangerLabel(fn ($state) => $state !== null && $state <= 0 ? 'Süresi dolmuş' : ($state !== null ? "{$state} gün" : 'Bilinmiyor'))
                    ->warningLabel(fn ($state) => $state !== null ? "{$state} gün" : 'Bilinmiyor')
                    ->successLabel(fn ($state) => $state !== null ? "{$state} gün" : 'Bilinmiyor')
                    ->sortable(query: function ($query, string $direction): \Illuminate\Database\Eloquent\Builder {
                        return $query->orderBy('end_date', $direction);
                    }),

                TextColumn::make('is_active')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $state
                        ? ($record->is_expired ? 'Süresi Dolmuş' : 'Aktif')
                        : 'Pasif')
                    ->color(fn ($state, $record) => match (true) {
                        ! $state => 'gray',
                        $record->is_expired => 'danger',
                        default => 'success',
                    })
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Durum')
                    ->options([
                        true => 'Aktif',
                        false => 'Pasif',
                    ]),

                SelectFilter::make('service.dealer_id')
                    ->label('Bayi')
                    ->relationship('service.dealer', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => $isAdmin),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('Dışa Aktar'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                Action::make('viewWarranty')
                    ->label('PDF Görüntüle')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn ($record) => route('warranty.pdf', ['serviceNo' => $record->service->service_no]))
                    ->openUrlInNewTab(),
                Action::make('viewService')
                    ->label('Hizmet Görüntüle')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => ServiceResource::getUrl('view', ['record' => $record->service_id]))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    FilamentExportBulkAction::make('export')
                        ->label('Dışa Aktar'),
                ]),
            ])
            ->defaultSort('end_date', 'asc');
    }
}
