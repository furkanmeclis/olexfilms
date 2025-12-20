<?php

namespace App\Filament\Resources\StockItems;

use App\Filament\Resources\StockItems\Pages\ListStockItems;
use App\Filament\Resources\StockItems\Pages\ViewStockItem;
use App\Filament\Resources\StockItems\Schemas\StockItemForm;
use App\Filament\Resources\StockItems\Schemas\StockItemInfolist;
use App\Filament\Resources\StockItems\Tables\StockItemsTable;
use App\Models\StockItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockItemResource extends Resource
{
    protected static ?string $model = StockItem::class;

    protected static ?string $navigationLabel = 'Stok Envanteri';

    protected static ?string $modelLabel = 'Stok Kalemi';

    protected static ?string $pluralModelLabel = 'Stok Kalemleri';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Ürün Yönetimi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'barcode';

    public static function getGloballySearchableAttributes(): array
    {
        return ['barcode', 'sku', 'product.name'];
    }

    public static function form(Schema $schema): Schema
    {
        return StockItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockItemsTable::configure($table);
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
            'index' => ListStockItems::route('/'),
            'view' => ViewStockItem::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        if ($user && $user->dealer_id && !$user->hasAnyRole(['super_admin', 'center_staff'])) {
            // Bayi: kendi dealer_id'sine sahip TÜM stok kodlarını (durum fark etmeksizin) görür
            $query->where('dealer_id', $user->dealer_id);
        }

        return $query;
    }
}
