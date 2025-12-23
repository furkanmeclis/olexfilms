<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationLabel = 'Müşteriler';

    protected static ?string $modelLabel = 'Müşteri';

    protected static ?string $pluralModelLabel = 'Müşteriler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Müşteri & Sipariş';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'email', 'dealer.name'];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->dealer_id && ! $user->hasAnyRole(['super_admin', 'center_staff'])) {
            // Bayi sadece kendi müşterilerini görür
            $query->where('dealer_id', $user->dealer_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->hasRole('super_admin') || $user->hasRole('center_staff'));

        return $schema
            ->components([
                Section::make('Müşteri Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Ad Soyad / Firma'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('Tip')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => $state->value === 'individual' ? 'info' : 'success'),

                        Infolists\Components\TextEntry::make('phone')
                            ->label('Telefon')
                            ->icon('heroicon-m-phone'),

                        Infolists\Components\TextEntry::make('email')
                            ->label('E-posta')
                            ->icon('heroicon-m-envelope')
                            ->placeholder('E-posta yok'),

                        Infolists\Components\TextEntry::make('tc_no')
                            ->label('TC Kimlik No')
                            ->placeholder('Girilmemiş')
                            ->visible(fn ($record) => $record->type === \App\Enums\CustomerTypeEnum::INDIVIDUAL),

                        Infolists\Components\TextEntry::make('tax_no')
                            ->label('Vergi No')
                            ->placeholder('Girilmemiş')
                            ->visible(fn ($record) => $record->type === \App\Enums\CustomerTypeEnum::CORPORATE),

                        Infolists\Components\TextEntry::make('tax_office')
                            ->label('Vergi Dairesi')
                            ->placeholder('Girilmemiş')
                            ->visible(fn ($record) => $record->type === \App\Enums\CustomerTypeEnum::CORPORATE),
                    ])
                    ->columns(2),

                Section::make('Adres Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('city')
                            ->label('İl')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('district')
                            ->label('İlçe')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('address')
                            ->label('Adres Detayı')
                            ->placeholder('Adres yok')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Bayi ve Oluşturan')
                    ->schema([
                        Infolists\Components\TextEntry::make('dealer.name')
                            ->label('Bayi')
                            ->placeholder('Bayiye bağlı değil')
                            ->visible(fn () => $isAdmin),

                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Oluşturan')
                            ->placeholder('Bilinmiyor'),
                    ])
                    ->columns(2),

                Section::make('FCM Token')
                    ->schema([
                        Infolists\Components\TextEntry::make('fcm_token')
                            ->label('FCM Token')
                            ->placeholder('Token yok')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => $isAdmin),

                Section::make('Tarihçe')
                    ->schema([
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
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
