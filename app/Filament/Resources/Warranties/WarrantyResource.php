<?php

namespace App\Filament\Resources\Warranties;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\Warranties\Pages\ListWarranties;
use App\Filament\Resources\Warranties\Pages\ViewWarranty;
use App\Filament\Resources\Warranties\Schemas\WarrantyForm;
use App\Filament\Resources\Warranties\Schemas\WarrantyInfolist;
use App\Filament\Resources\Warranties\Tables\WarrantiesTable;
use App\Models\Warranty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WarrantyResource extends Resource
{
    protected static ?string $model = Warranty::class;

    protected static ?string $navigationLabel = 'Garantiler';

    protected static ?string $modelLabel = 'Garanti';

    protected static ?string $pluralModelLabel = 'Garantiler';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static UnitEnum|string|null $navigationGroup = 'Hizmet Yönetimi';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['service.service_no', 'stockItem.barcode', 'stockItem.product.name'];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->dealer_id && !$user->hasAnyRole([
            UserRoleEnum::SUPER_ADMIN->value,
            UserRoleEnum::CENTER_STAFF->value,
        ])) {
            // Bayi sadece kendi bayi hizmetlerinin garantilerini görür
            $query->whereHas('service', function ($q) use ($user) {
                $q->where('dealer_id', $user->dealer_id);
            });
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return WarrantyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WarrantyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarrantiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWarranties::route('/'),
            'view' => ViewWarranty::route('/{record}'),
        ];
    }
}

