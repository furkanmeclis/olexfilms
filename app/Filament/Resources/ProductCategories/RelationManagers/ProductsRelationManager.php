<?php

namespace App\Filament\Resources\ProductCategories\RelationManagers;

use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Ürünler';

    protected static ?string $modelLabel = 'Ürün';

    protected static ?string $pluralModelLabel = 'Ürünler';

    public function form(Schema $schema): Schema
    {
        // ProductForm'u kullan ama category_id'yi otomatik set et
        $form = ProductForm::configure($schema);

        // Form component'lerini modify et - category_id Select'ini Hidden yap
        $components = collect($form->getComponents())->map(function ($component) {
            if ($component instanceof \Filament\Schemas\Components\Section) {
                $childSchema = $component->getChildSchema();
                if ($childSchema) {
                    $sectionComponents = collect($childSchema->getComponents())->map(function ($field) {
                        if ($field->getName() === 'category_id') {
                            return Hidden::make('category_id')
                                ->default(fn () => $this->ownerRecord->id);
                        }

                        return $field;
                    })->toArray();

                    return $component->schema($sectionComponents);
                }
            }

            return $component;
        })->toArray();

        return $schema->components($components);
    }

    public function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Products\Schemas\ProductInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        $configuredTable = ProductsTable::configure($table);

        return $configuredTable
            ->recordTitleAttribute('name')
            ->headerActions([
                CreateAction::make()
                    ->label('Yeni Ürün'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
                DeleteAction::make()
                    ->label('Sil'),
                ForceDeleteAction::make()
                    ->label('Kalıcı Sil'),
                RestoreAction::make()
                    ->label('Geri Yükle'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
