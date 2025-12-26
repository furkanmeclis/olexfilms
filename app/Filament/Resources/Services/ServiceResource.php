<?php

namespace App\Filament\Resources\Services;

use App\Enums\ServiceStatusEnum;
use App\Filament\Infolists\Components\CarPartView;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use BackedEnum;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationLabel = 'Hizmetler';

    protected static UnitEnum|string|null $navigationGroup = 'Hizmet Yönetimi';

    protected static ?string $modelLabel = 'Hizmet';

    protected static ?string $pluralModelLabel = 'Hizmetler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'service_no';

    public static function getGloballySearchableAttributes(): array
    {
        return ['service_no', 'customer.name', 'carBrand.name', 'carModel.name', 'plate'];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->dealer_id && ! $user->hasAnyRole(['super_admin', 'center_staff'])) {
            // Bayi sadece kendi hizmetlerini görür
            $query->where('dealer_id', $user->dealer_id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Özet İstatistikler
                Section::make('Özet Bilgiler')
                    ->schema([
                        Infolists\Components\TextEntry::make('service_no')
                            ->label('Hizmet Numarası')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Durum')
                            ->badge()
                            ->size('lg')
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => match ($state->value) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'processing' => 'info',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('items_count')
                            ->label('Kullanılan Ürün Sayısı')
                            ->formatStateUsing(fn ($record) => $record->items()->count().' ürün')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-cube')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('images_count')
                            ->label('Galeri Görsel Sayısı')
                            ->formatStateUsing(fn ($record) => $record->images()->count().' görsel')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-photo')
                            ->columnSpan(1),
                    ])
                    ->columns(4)
                    ->icon('heroicon-o-information-circle'),

                // Müşteri Bilgileri
                Section::make('Müşteri Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Ad Soyad')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('customer.phone')
                            ->label('Telefon')
                            ->icon('heroicon-o-phone')
                            ->url(fn ($record) => $record->customer ? 'tel:'.$record->customer->phone : null)
                            ->openUrlInNewTab(false),

                        Infolists\Components\TextEntry::make('customer.email')
                            ->label('E-posta')
                            ->icon('heroicon-o-envelope')
                            ->url(fn ($record) => $record->customer && $record->customer->email ? 'mailto:'.$record->customer->email : null)
                            ->openUrlInNewTab(false)
                            ->placeholder('E-posta yok'),

                        Infolists\Components\TextEntry::make('customer.type')
                            ->label('Müşteri Tipi')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Belirtilmemiş')
                            ->color(fn ($state) => match ($state?->value) {
                                'individual' => 'primary',
                                'corporate' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('customer.address')
                            ->label('Adres')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('Adres girilmemiş')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('customer.city')
                            ->label('Şehir')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('customer.district')
                            ->label('İlçe')
                            ->placeholder('Girilmemiş'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-user-circle')
                    ->collapsible(),

                // Hizmet Detayları
                Section::make('Hizmet Detayları')
                    ->schema([
                        Infolists\Components\TextEntry::make('dealer.name')
                            ->label('Bayi')
                            ->icon('heroicon-o-building-storefront')
                            ->badge()
                            ->color('primary')
                            ->placeholder('Merkez'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Oluşturan Kullanıcı')
                            ->icon('heroicon-o-user-plus'),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Tamamlanma Tarihi')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-check-circle')
                            ->color(fn ($record) => $record->completed_at ? 'success' : 'gray')
                            ->placeholder('Henüz tamamlanmadı')
                            ->badge(),

                        Infolists\Components\TextEntry::make('package')
                            ->label('Paket')
                            ->badge()
                            ->color('info')
                            ->placeholder('Paket belirtilmemiş'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsible(),

                // Araç Bilgileri
                Section::make('Araç Bilgileri')
                    ->schema([
                        Infolists\Components\ImageEntry::make('carBrand.logo_url')
                            ->label('Marka Logosu')
                            ->defaultImageUrl(function ($record) {
                                if ($record->carBrand?->logo_url) {
                                    return $record->carBrand->logo_url;
                                }

                                return url('/images/placeholder.png');
                            })
                            ->circular()
                            ->height(100)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('carBrand.name')
                            ->label('Marka')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-tag'),

                        Infolists\Components\TextEntry::make('carModel.name')
                            ->label('Model')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-cog-6-tooth'),

                        Infolists\Components\TextEntry::make('year')
                            ->label('Yıl')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('plate')
                            ->label('Plaka')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-identification')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('vin')
                            ->label('Şasi No')
                            ->icon('heroicon-o-key')
                            ->placeholder('Girilmemiş')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('km')
                            ->label('Kilometre')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.').' km' : 'Girilmemiş')
                            ->icon('heroicon-o-map-pin'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-truck')
                    ->collapsible(),

                // Kaplama Alanları
                Section::make('Kaplama Alanları')
                    ->schema([
                        CarPartView::make('applied_parts')
                            ->label('Uygulanan Parçalar')
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsible(),

                // Kullanılan Ürünler
                Section::make('Kullanılan Ürünler')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('stockItem.product.name')
                                    ->label('Ürün Adı')
                                    ->weight('bold')
                                    ->icon('heroicon-o-cube'),

                                Infolists\Components\TextEntry::make('stockItem.barcode')
                                    ->label('Barkod')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-qr-code'),

                                Infolists\Components\TextEntry::make('stockItem.sku')
                                    ->label('Stok Kodu')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-tag'),

                                Infolists\Components\TextEntry::make('usage_type')
                                    ->label('Kullanım Tipi')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Belirtilmemiş')
                                    ->color(fn ($state) => match ($state?->value) {
                                        'full' => 'success',
                                        'partial' => 'warning',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-scissors'),

                                Infolists\Components\TextEntry::make('notes')
                                    ->label('Notlar')
                                    ->placeholder('Not yok')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->grid(1),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-shopping-bag'),

                // Galeri
                Section::make('Galeri')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('images')
                            ->label('')
                            ->schema([
                                Infolists\Components\ImageEntry::make('image_path')
                                    ->label('Görsel')
                                    ->height(150)
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('title')
                                    ->label('Başlık')
                                    ->weight('bold')
                                    ->placeholder('Başlık yok')
                                    ->columnSpan(2),

                                Infolists\Components\TextEntry::make('order')
                                    ->label('Sıra')
                                    ->badge()
                                    ->color('gray'),
                            ])
                            ->columns(3)
                            ->grid(3),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-photo'),

                // Garanti Bilgileri
                Section::make('Garanti Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('warranties_count')
                            ->label('Toplam Garanti Sayısı')
                            ->formatStateUsing(fn ($record) => $record->warranties()->count().' garanti')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-shield-check')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('active_warranties_count')
                            ->label('Aktif Garanti Sayısı')
                            ->formatStateUsing(fn ($record) => $record->warranties()->where('is_active', true)->count().' aktif')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-check-circle')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('expired_warranties_count')
                            ->label('Süresi Dolmuş Garanti Sayısı')
                            ->formatStateUsing(fn ($record) => $record->warranties()
                                ->where('is_active', true)
                                ->where('end_date', '<', now()->startOfDay())
                                ->count().' süresi dolmuş')
                            ->badge()
                            ->color('danger')
                            ->icon('heroicon-o-clock')
                            ->columnSpan(1),

                        Infolists\Components\RepeatableEntry::make('warranties')
                            ->label('Garanti Listesi')
                            ->schema([
                                Infolists\Components\TextEntry::make('stockItem.barcode')
                                    ->label('Barkod')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-qr-code'),

                                Infolists\Components\TextEntry::make('stockItem.product.name')
                                    ->label('Ürün')
                                    ->weight('bold')
                                    ->icon('heroicon-o-cube'),

                                Infolists\Components\TextEntry::make('end_date')
                                    ->label('Bitiş Tarihi')
                                    ->date('d.m.Y')
                                    ->badge()
                                    ->color(fn ($record) => $record->is_expired ? 'danger' : 'primary')
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('days_remaining')
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
                                    ->icon('heroicon-o-clock'),

                                Infolists\Components\TextEntry::make('is_active')
                                    ->label('Durum')
                                    ->badge()
                                    ->formatStateUsing(fn ($state, $record) => $state
                                        ? ($record->is_expired ? 'Süresi Dolmuş' : 'Aktif')
                                        : 'Pasif')
                                    ->color(fn ($state, $record) => match (true) {
                                        ! $state => 'gray',
                                        $record->is_expired => 'danger',
                                        default => 'success',
                                    }),
                            ])
                            ->columns(2)
                            ->grid(1)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(false)
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn ($record) => $record->status->value === ServiceStatusEnum::COMPLETED->value),

                // Notlar
                Section::make('Notlar')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Hizmet Notları')
                            ->placeholder('Not yok')
                            ->columnSpanFull()
                            ->icon('heroicon-o-document-text'),
                    ])
                    ->collapsible()
                    ->collapsed(true)
                    ->icon('heroicon-o-document'),

                // Tarihçe
                Section::make('Tarihçe')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Oluşturulma Tarihi')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-plus-circle')
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Son Güncelleme')
                            ->dateTime('d.m.Y H:i')
                            ->icon('heroicon-o-arrow-path')
                            ->badge()
                            ->color('info'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(true)
                    ->icon('heroicon-o-clock'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Services\RelationManagers\ServiceItemsRelationManager::class,
            \App\Filament\Resources\Services\RelationManagers\WarrantiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
            'manage-images' => \App\Filament\Resources\Services\Pages\ManageServiceImages::route('/{record}/images'),
        ];
    }
}
