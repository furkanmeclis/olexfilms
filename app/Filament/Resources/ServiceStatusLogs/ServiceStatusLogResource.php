<?php

namespace App\Filament\Resources\ServiceStatusLogs;

use App\Filament\Resources\ServiceStatusLogs\Pages\ListServiceStatusLogs;
use App\Filament\Resources\ServiceStatusLogs\Pages\ViewServiceStatusLog;
use App\Filament\Resources\ServiceStatusLogs\Schemas\ServiceStatusLogInfolist;
use App\Filament\Resources\ServiceStatusLogs\Tables\ServiceStatusLogsTable;
use App\Models\ServiceStatusLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ServiceStatusLogResource extends Resource
{
    protected static ?string $model = ServiceStatusLog::class;

    protected static ?string $navigationLabel = 'Servis Durum Logları';

    protected static ?string $modelLabel = 'Servis Durum Logu';

    protected static ?string $pluralModelLabel = 'Servis Durum Logları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Hizmet Yönetimi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole(['super_admin', 'center_staff']);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceStatusLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceStatusLogsTable::configure($table);
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
            'index' => ListServiceStatusLogs::route('/'),
            'view' => ViewServiceStatusLog::route('/{record}'),
        ];
    }
}
