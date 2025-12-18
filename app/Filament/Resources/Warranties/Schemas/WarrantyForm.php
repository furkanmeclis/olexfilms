<?php

namespace App\Filament\Resources\Warranties\Schemas;

use App\Enums\UserRoleEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class WarrantyForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));

        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        Select::make('service_id')
                            ->label('Hizmet')
                            ->relationship('service', 'service_no')
                            ->disabled()
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('stock_item_id')
                            ->label('Stok Kalemi')
                            ->relationship('stockItem', 'barcode')
                            ->disabled()
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Garanti Detayları')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Başlangıç Tarihi')
                            ->disabled()
                            ->required()
                            ->displayFormat('d.m.Y')
                            ->native(false),

                        DatePicker::make('end_date')
                            ->label('Bitiş Tarihi')
                            ->disabled()
                            ->required()
                            ->displayFormat('d.m.Y')
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->disabled(!$isAdmin)
                            ->helperText($isAdmin ? 'Garanti durumunu değiştirebilirsiniz' : 'Sadece yöneticiler garanti durumunu değiştirebilir')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }
}

