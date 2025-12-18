<?php

namespace App\Filament\Resources\Warranties\Tables;

use App\Enums\UserRoleEnum;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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

                TextColumn::make('days_remaining')
                    ->label('Kalan Gün')
                    ->formatStateUsing(fn ($state) => $state !== null 
                        ? ($state > 0 ? "{$state} gün" : 'Süresi dolmuş')
                        : 'Bilinmiyor')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state <= 0 => 'danger',
                        $state <= 30 => 'warning',
                        default => 'success',
                    })
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
                        !$state => 'gray',
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

                SelectFilter::make('expired')
                    ->label('Süresi Dolmuş')
                    ->query(fn ($query) => $query->where('end_date', '<', now()->startOfDay())),

                SelectFilter::make('service.dealer_id')
                    ->label('Bayi')
                    ->relationship('service.dealer', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => $isAdmin),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
            ])
            ->defaultSort('end_date', 'asc');
    }
}

