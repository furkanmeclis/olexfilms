<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRoleEnum;
use App\Models\Dealer;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = Auth::user();
        $isDealerOwner = $user && $user->hasRole(UserRoleEnum::DEALER_OWNER->value);
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));

        return $schema
            ->components([
                Section::make('Kişisel Bilgiler')
                    ->schema([
                        TextInput::make('name')
                            ->label('Ad Soyad')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        PhoneInput::make('phone')
                            ->label('Telefon')
                            ->required()
                            ->defaultCountry('TR')
                            ->validateFor('TR')
                            ->locale('tr'),
                    ])
                    ->columns(2),

                Section::make('Yetkilendirme')
                    ->schema([
                        // Dealer ID field - Hidden for dealer owner, Select for admin
                        $isDealerOwner
                            ? Hidden::make('dealer_id')
                                ->default(fn () => $user->dealer_id)
                            : Select::make('dealer_id')
                                ->label('Bayi')
                                ->relationship('dealer', 'name')
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->visible(fn () => $isAdmin),

                        // Role selection
                        Select::make('role')
                            ->label('Rol')
                            ->options(function () use ($isDealerOwner) {
                                if ($isDealerOwner) {
                                    return [
                                        UserRoleEnum::DEALER_STAFF->value => 'Bayi Çalışanı',
                                    ];
                                }

                                return [
                                    UserRoleEnum::SUPER_ADMIN->value => 'Süper Admin',
                                    UserRoleEnum::CENTER_STAFF->value => 'Merkez Çalışanı',
                                    UserRoleEnum::DEALER_OWNER->value => 'Bayi Sahibi',
                                    UserRoleEnum::DEALER_STAFF->value => 'Bayi Çalışanı',
                                ];
                            })
                            ->default(fn ($record) => $record?->roles->first()?->name)
                            ->required()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                Section::make('Güvenlik')
                    ->schema([
                        // Password field - optional on edit, required on create
                        TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->minLength(8)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Section::make('Durum')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
