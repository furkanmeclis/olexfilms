<?php

namespace App\Filament\Resources\Dealers;

use App\Filament\Resources\Dealers\Pages\CreateDealer;
use App\Filament\Resources\Dealers\Pages\EditDealer;
use App\Filament\Resources\Dealers\Pages\ListDealers;
use App\Filament\Resources\Dealers\Pages\ViewDealer;
use App\Filament\Resources\Dealers\RelationManagers;
use App\Filament\Resources\Dealers\Schemas\DealerForm;
use App\Filament\Resources\Dealers\Tables\DealersTable;
use App\Models\Dealer;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static ?string $navigationLabel = 'Bayiler';

    protected static ?string $modelLabel = 'Bayi';

    protected static ?string $pluralModelLabel = 'Bayiler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function form(Schema $schema): Schema
    {
        return DealerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bayi Bilgileri')
                    ->schema([
                        Infolists\Components\ImageEntry::make('logo_path')
                            ->label('Logo')
                            ->circular()
                            ->defaultImageUrl(url('/images/placeholder.png'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('dealer_code')
                            ->label('Bayi Kodu')
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('name')
                            ->label('Bayi Adı'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('E-posta')
                            ->icon('heroicon-m-envelope'),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefon')
                            ->icon('heroicon-m-phone'),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Adres')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Konum Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('city')
                            ->label('İl')
                            ->icon('heroicon-m-map-pin'),

                        Infolists\Components\TextEntry::make('district')
                            ->label('İlçe')
                            ->icon('heroicon-m-map-pin'),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->city || $record->district),

                Section::make('Sosyal Medya')
                    ->schema([
                        Infolists\Components\TextEntry::make('website_url')
                            ->label('Web Sitesi')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-globe-alt')
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('facebook_url')
                            ->label('Facebook')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-facebook')
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('instagram_url')
                            ->label('Instagram')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-instagram')
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('twitter_url')
                            ->label('Twitter/X')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-x-mark')
                            ->openUrlInNewTab(),

                        Infolists\Components\TextEntry::make('linkedin_url')
                            ->label('LinkedIn')
                            ->url(fn ($state) => $state)
                            ->icon('heroicon-m-linkedin')
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->website_url || $record->facebook_url || $record->instagram_url || $record->twitter_url || $record->linkedin_url),

                Section::make('Durum')
                    ->schema([
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('Aktif')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Pasif')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),

                        Infolists\Components\TextEntry::make('users_count')
                            ->label('Kullanıcı Sayısı')
                            ->state(fn ($record) => $record->users()->count()),

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
            RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealers::route('/'),
            'create' => CreateDealer::route('/create'),
            'view' => ViewDealer::route('/{record}'),
            'edit' => EditDealer::route('/{record}/edit'),
        ];
    }
}
