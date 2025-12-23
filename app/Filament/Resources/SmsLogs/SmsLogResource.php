<?php

namespace App\Filament\Resources\SmsLogs;

use App\Filament\Resources\SmsLogs\Pages\ListSmsLogs;
use App\Filament\Resources\SmsLogs\Pages\ViewSmsLog;
use App\Filament\Resources\SmsLogs\Schemas\SmsLogInfolist;
use App\Filament\Resources\SmsLogs\Tables\SmsLogsTable;
use App\Models\SmsLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationLabel = 'SMS Logları';

    protected static ?string $modelLabel = 'SMS Logu';

    protected static ?string $pluralModelLabel = 'SMS Logları';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'SMS';

    protected static ?int $navigationSort = 1;

    public static function infolist(Schema $schema): Schema
    {
        return SmsLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SmsLogsTable::configure($table);
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
            'index' => ListSmsLogs::route('/'),
            'view' => ViewSmsLog::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Sadece super_admin görebilir
        if (! auth()->user()?->hasRole('super_admin')) {
            $query->whereRaw('1 = 0'); // Hiçbir kayıt gösterme
        }

        return $query;
    }
}
