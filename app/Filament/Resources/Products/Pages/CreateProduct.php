<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Schemas\ProductForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }
}
