<?php

namespace App\Filament\Resources\BulkSms;

use App\Filament\Resources\BulkSms\Pages\CreateBulkSms;
use App\Filament\Resources\BulkSms\Pages\ListBulkSms;
use App\Filament\Resources\BulkSms\Pages\ViewBulkSms;
use App\Filament\Resources\BulkSms\Schemas\BulkSmsForm;
use App\Filament\Resources\BulkSms\Schemas\BulkSmsInfolist;
use App\Filament\Resources\BulkSms\Tables\BulkSmsTable;
use App\Models\BulkSms;
use BackedEnum;
use Filament\Resources\Resource;
use UnitEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BulkSmsResource extends Resource
{
    protected static ?string $model = BulkSms::class;

    protected static ?string $navigationLabel = 'Toplu SMS';

    protected static ?string $modelLabel = 'Toplu SMS';

    protected static ?string $pluralModelLabel = 'Toplu SMS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static string|UnitEnum|null $navigationGroup = 'SMS';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BulkSmsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BulkSmsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulkSmsTable::configure($table);
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
            'index' => ListBulkSms::route('/'),
            'create' => CreateBulkSms::route('/create'),
            'view' => ViewBulkSms::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Sadece super_admin görebilir
        if (!auth()->user()?->hasRole('super_admin')) {
            $query->whereRaw('1 = 0'); // Hiçbir kayıt gösterme
        }

        return $query;
    }
}
