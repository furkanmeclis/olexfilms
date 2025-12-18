<?php

namespace App\Filament\Resources\Warranties\Pages;

use App\Filament\Resources\Warranties\WarrantyResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWarranties extends ListRecords
{
    protected static string $resource = WarrantyResource::class;

    protected function getEloquentQuery(): Builder
    {
        // Parent'taki getEloquentQuery() zaten Resource'taki getEloquentQuery()'yi kullanıyor
        return parent::getEloquentQuery();
    }
}

