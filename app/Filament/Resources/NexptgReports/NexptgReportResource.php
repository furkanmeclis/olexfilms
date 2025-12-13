<?php

namespace App\Filament\Resources\NexptgReports;

use App\Filament\Resources\NexptgReports\Pages\EditNexptgReport;
use App\Filament\Resources\NexptgReports\Pages\ListNexptgReports;
use App\Filament\Resources\NexptgReports\Pages\ViewNexptgReport;
use App\Filament\Resources\NexptgReports\RelationManagers\MeasurementsRelationManager;
use App\Filament\Resources\NexptgReports\RelationManagers\TiresRelationManager;
use App\Filament\Resources\NexptgReports\Schemas\NexptgReportForm;
use App\Filament\Resources\NexptgReports\Schemas\NexptgReportInfolist;
use App\Filament\Resources\NexptgReports\Tables\NexptgReportsTable;
use App\Models\NexptgReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NexptgReportResource extends Resource
{
    protected static ?string $model = NexptgReport::class;

    protected static ?string $navigationLabel = 'Ölçüm Sonuçları';

    protected static ?string $modelLabel = 'Ölçüm Sonucu';

    protected static ?string $pluralModelLabel = 'Ölçüm Sonuçları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'NexPTG';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'brand', 'model', 'vin', 'device_serial_number'];
    }

    public static function canCreate(): bool
    {
        return false; // Reports can only be created via API sync
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['apiUser.user']); // Eager loading for table performance

        $user = auth()->user();
        if ($user && $user->hasRole(\App\Enums\UserRoleEnum::SUPER_ADMIN->value)) {
            // Super admin tüm raporları görebilir
            return $query;
        }

        // Diğer kullanıcılar sadece kendi API user'larının raporlarını görebilir
        if ($user && $user->nexptgApiUser) {
            return $query->where('api_user_id', $user->nexptgApiUser->id);
        }

        // API user'ı olmayan kullanıcılar hiçbir rapor göremez
        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return NexptgReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NexptgReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NexptgReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MeasurementsRelationManager::class,
            TiresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNexptgReports::route('/'),
            'view' => ViewNexptgReport::route('/{record}'),
            'edit' => EditNexptgReport::route('/{record}/edit'),
        ];
    }
}
