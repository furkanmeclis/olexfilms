<?php

namespace App\Filament\Resources\NexptgApiUsers;

use App\Filament\Resources\NexptgApiUsers\Pages\CreateNexptgApiUser;
use App\Filament\Resources\NexptgApiUsers\Pages\EditNexptgApiUser;
use App\Filament\Resources\NexptgApiUsers\Pages\ListNexptgApiUsers;
use App\Filament\Resources\NexptgApiUsers\Pages\ViewNexptgApiUser;
use App\Filament\Resources\NexptgApiUsers\Schemas\NexptgApiUserForm;
use App\Filament\Resources\NexptgApiUsers\Tables\NexptgApiUsersTable;
use App\Models\NexptgApiUser;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NexptgApiUserResource extends Resource
{
    protected static ?string $model = NexptgApiUser::class;

    protected static ?string $navigationLabel = 'API Kullanıcıları';

    protected static ?string $modelLabel = 'API Kullanıcı';

    protected static ?string $pluralModelLabel = 'API Kullanıcıları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'NexPTG';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'username';

    public static function form(Schema $schema): Schema
    {
        return NexptgApiUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NexptgApiUsersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Kullanıcı Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('username')
                            ->label('Kullanıcı Adı')
                            ->icon('heroicon-m-key'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Bağlı Kullanıcı')
                            ->placeholder('Bağlı kullanıcı yok')
                            ->icon('heroicon-m-user'),

                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                        Infolists\Components\TextEntry::make('last_used_at')
                            ->label('Son Kullanım')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Henüz kullanılmadı')
                            ->icon('heroicon-m-clock'),

                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Oluşturan')
                            ->icon('heroicon-m-user'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Oluşturulma')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Güncellenme')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-m-arrow-path'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNexptgApiUsers::route('/'),
            'create' => CreateNexptgApiUser::route('/create'),
            'view' => ViewNexptgApiUser::route('/{record}'),
            'edit' => EditNexptgApiUser::route('/{record}/edit'),
        ];
    }
}
