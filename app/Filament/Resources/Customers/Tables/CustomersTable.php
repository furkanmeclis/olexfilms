<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Enums\CustomerTypeEnum;
use App\Enums\UserRoleEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));

        // İl-İlçe JSON verisini yükle
        $cityData = [];
        $districtData = [];

        try {
            $jsonPath = storage_path('il-ilce.json');
            if (File::exists($jsonPath)) {
                $jsonData = json_decode(File::get($jsonPath), true);
                if (isset($jsonData['data'])) {
                    foreach ($jsonData['data'] as $city) {
                        $cityName = $city['il_adi'];
                        $cityData[$cityName] = $cityName;

                        if (isset($city['ilceler']) && is_array($city['ilceler'])) {
                            foreach ($city['ilceler'] as $district) {
                                $districtName = $district['ilce_adi'];
                                $districtData[$districtName] = $districtName;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // JSON yüklenemezse boş kalır
        }

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad Soyad / Firma')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->getLabel())
                    ->color(fn ($state) => $state->value === 'individual' ? 'info' : 'success'),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable(),

                TextColumn::make('city')
                    ->label('İl')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('district')
                    ->label('İlçe')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('dealer.name')
                    ->label('Bayi')
                    ->sortable()
                    ->placeholder('Bayiye bağlı değil')
                    ->visible(fn () => $isAdmin),

                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tip')
                    ->options(CustomerTypeEnum::getLabels()),

                SelectFilter::make('dealer_id')
                    ->label('Bayi')
                    ->relationship('dealer', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => $isAdmin),

                SelectFilter::make('city')
                    ->label('İl')
                    ->options($cityData)
                    ->searchable()
                    ->preload(),

                SelectFilter::make('district')
                    ->label('İlçe')
                    ->options($districtData)
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
