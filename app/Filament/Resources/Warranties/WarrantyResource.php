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
        // #region agent log
        $logData = ['location' => 'WarrantyResource.php:47', 'message' => 'getEloquentQuery entry', 'data' => ['user_id' => $user?->id, 'user_email' => $user?->email], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run2', 'hypothesisId' => 'A'];
        file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        // OrderResource ve StockItemResource pattern'ini takip ediyoruz
        // Admin ve merkez çalışanları için filtreleme yapmıyoruz
        if ($user && $user->dealer_id && !$user->hasAnyRole(['super_admin', 'center_staff'])) {
            // #region agent log
            $logData = ['location' => 'WarrantyResource.php:52', 'message' => 'Dealer user (not admin), applying whereHas filter', 'data' => ['dealer_id' => $user->dealer_id], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run2', 'hypothesisId' => 'B'];
            file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            // Bayi sadece kendi bayi hizmetlerinin garantilerini görür
            $query->whereHas('service', function ($q) use ($user) {
                $q->where('dealer_id', $user->dealer_id);
            });
        } else {
            // #region agent log
            $logData = ['location' => 'WarrantyResource.php:60', 'message' => 'Admin/Center staff or no dealer_id, no filter applied', 'data' => ['dealer_id' => $user?->dealer_id, 'hasAnyRole' => $user ? $user->hasAnyRole(['super_admin', 'center_staff']) : false], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run2', 'hypothesisId' => 'B'];
            file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
        }

        // #region agent log
        $finalSql = $query->toSql();
        $logData = ['location' => 'WarrantyResource.php:66', 'message' => 'Final query before return', 'data' => ['sql' => $finalSql, 'bindings' => $query->getBindings()], 'timestamp' => time(), 'sessionId' => 'debug-session', 'runId' => 'run2', 'hypothesisId' => 'E'];
        file_put_contents('/Users/furkanmeclis/Documents/Herd/glorian-v2_3/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
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

