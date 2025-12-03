<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Kullanıcılar';

    protected static ?string $modelLabel = 'Kullanıcı';

    protected static ?string $pluralModelLabel = 'Kullanıcılar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'dealer.name'];
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kişisel Bilgiler')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Ad Soyad'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('E-posta')
                            ->icon('heroicon-m-envelope'),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefon')
                            ->icon('heroicon-m-phone'),
                    ])
                    ->columns(2),

                Section::make('Yetkilendirme')
                    ->schema([
                        Infolists\Components\TextEntry::make('dealer.name')
                            ->label('Bayi')
                            ->placeholder('Bayiye bağlı değil'),

                        Infolists\Components\TextEntry::make('roles.name')
                            ->label('Rol')
                            ->badge()
                            ->placeholder('Rol atanmamış'),
                    ])
                    ->columns(2),

                Section::make('Durum')
                    ->schema([
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                        Infolists\Components\TextEntry::make('email_verified_at')
                            ->label('E-posta Doğrulandı')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Doğrulanmamış'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Oluşturulma')
                            ->dateTime('d.m.Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Güncellenme')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
