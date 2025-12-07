<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Filament\Resources\Services\RelationManagers\ServiceItemsRelationManager;
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

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationLabel = 'Hizmetler';

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
        if ($user && $user->dealer_id && !$user->hasAnyRole(['super_admin', 'center_staff'])) {
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
                Section::make('Hizmet Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('service_no')
                            ->label('Hizmet Numarası'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => match ($state->value) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'processing' => 'info',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Müşteri'),

                        Infolists\Components\TextEntry::make('dealer.name')
                            ->label('Bayi'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Oluşturan'),

                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Tamamlanma Tarihi')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Henüz tamamlanmadı'),
                    ])
                    ->columns(2),

                Section::make('Araç Bilgileri')
                    ->schema([
                        Infolists\Components\ImageEntry::make('carBrand.logo')
                            ->label('Marka Logosu')
                            ->defaultImageUrl(function ($record) {
                                if ($record->carBrand?->logo) {
                                    $logoPath = $record->carBrand->logo;
                                    // Eğer storage path ise asset ile, değilse direkt kullan
                                    if (str_starts_with($logoPath, 'storage/')) {
                                        return asset($logoPath);
                                    }
                                    return asset('storage/' . $logoPath);
                                }
                                return null;
                            })
                            ->height(80)
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('carBrand.name')
                            ->label('Marka'),

                        Infolists\Components\TextEntry::make('carModel.name')
                            ->label('Model'),

                        Infolists\Components\TextEntry::make('year')
                            ->label('Yıl'),

                        Infolists\Components\TextEntry::make('vin')
                            ->label('Şasi No')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('plate')
                            ->label('Plaka')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('km')
                            ->label('Kilometre'),

                        Infolists\Components\TextEntry::make('package')
                            ->label('Paket'),
                    ])
                    ->columns(2),

                Section::make('Kaplama Alanları')
                    ->schema([
                        Infolists\Components\TextEntry::make('applied_parts')
                            ->label('Uygulanan Parçalar')
                            ->badge()
                            ->formatStateUsing(function ($state, $record) {
                                // Debug: State tipini ve değerini logla
                                \Log::info('Applied Parts Debug', [
                                    'service_id' => $record->id,
                                    'state_type' => gettype($state),
                                    'state_value' => $state,
                                    'is_array' => is_array($state),
                                    'is_string' => is_string($state),
                                    'is_null' => is_null($state),
                                ]);

                                // Eğer state null veya boş ise
                                if (empty($state)) {
                                    \Log::info('Applied Parts: Empty state');
                                    return 'Parça seçilmemiş';
                                }

                                // Eğer state array değilse (string ise), JSON decode et
                                if (!is_array($state)) {
                                    \Log::info('Applied Parts: State is not array, attempting JSON decode', [
                                        'state' => $state,
                                    ]);
                                    
                                    if (is_string($state)) {
                                        $decoded = json_decode($state, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                            $state = $decoded;
                                            \Log::info('Applied Parts: Successfully decoded JSON', [
                                                'decoded' => $state,
                                            ]);
                                        } else {
                                            \Log::warning('Applied Parts: JSON decode failed', [
                                                'json_error' => json_last_error_msg(),
                                                'original_state' => $state,
                                            ]);
                                            return 'Parça seçilmemiş';
                                        }
                                    } else {
                                        \Log::warning('Applied Parts: State is neither array nor string', [
                                            'type' => gettype($state),
                                            'value' => $state,
                                        ]);
                                        return 'Parça seçilmemiş';
                                    }
                                }

                                // Eğer hala array değilse veya boşsa
                                if (empty($state) || !is_array($state)) {
                                    \Log::warning('Applied Parts: State is empty or not array after processing', [
                                        'state' => $state,
                                        'is_array' => is_array($state),
                                    ]);
                                    return 'Parça seçilmemiş';
                                }

                                // Array'i label'lara çevir
                                $labels = array_map(function ($part) {
                                    $label = \App\Enums\CarPartEnum::getLabels()[$part] ?? $part;
                                    \Log::info('Applied Parts: Mapping part to label', [
                                        'part' => $part,
                                        'label' => $label,
                                    ]);
                                    return $label;
                                }, $state);

                                \Log::info('Applied Parts: Final labels', [
                                    'labels' => $labels,
                                ]);

                                return $labels;
                            })
                            ->listWithLineBreaks()
                            ->columnSpanFull(),
                    ]),

                Section::make('Notlar')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notlar')
                            ->placeholder('Not yok')
                            ->columnSpanFull(),
                    ]),

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
            \App\Filament\Resources\Services\RelationManagers\ServiceItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
