<?php

namespace App\Filament\Resources\CarBrands;

use App\Filament\Resources\CarBrands\Pages\CreateCarBrand;
use App\Filament\Resources\CarBrands\Pages\EditCarBrand;
use App\Filament\Resources\CarBrands\Pages\ListCarBrands;
use App\Filament\Resources\CarBrands\Pages\ViewCarBrand;
use App\Filament\Resources\CarBrands\Schemas\CarBrandForm;
use App\Filament\Resources\CarBrands\Schemas\CarBrandInfolist;
use App\Filament\Resources\CarBrands\Tables\CarBrandsTable;
use App\Models\CarBrand;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CarBrandResource extends Resource
{
    protected static ?string $model = CarBrand::class;

    protected static ?string $navigationLabel = 'Araç Markaları';

    protected static ?string $modelLabel = 'Araç Markası';

    protected static ?string $pluralModelLabel = 'Araç Markaları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'external_id'];
    }

    public static function form(Schema $schema): Schema
    {
        return CarBrandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CarBrandInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarBrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ModelsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarBrands::route('/'),
            'create' => CreateCarBrand::route('/create'),
            'view' => ViewCarBrand::route('/{record}'),
            'edit' => EditCarBrand::route('/{record}/edit'),
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
