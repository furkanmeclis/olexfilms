<?php

namespace App\Filament\Resources\CarBrands\RelationManagers;

use App\Filament\Resources\CarModels\Schemas\CarModelForm;
use App\Filament\Resources\CarModels\Tables\CarModelsTable;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    protected static ?string $title = 'Modeller';

    protected static ?string $modelLabel = 'Model';

    protected static ?string $pluralModelLabel = 'Modeller';

    public function form(Schema $schema): Schema
    {
        // CarModelForm'u kullan ama brand_id'yi otomatik set et
        $form = CarModelForm::configure($schema);

        // Form component'lerini modify et - brand_id Select'ini Hidden yap
        $components = collect($form->getComponents())->map(function ($component) {
            if ($component instanceof \Filament\Schemas\Components\Section) {
                $sectionComponents = collect($component->getComponents(withActions: false, withHidden: false))->map(function ($field) {
                    if ($field->getName() === 'brand_id') {
                        return Hidden::make('brand_id')
                            ->default(fn () => $this->ownerRecord->id);
                    }
                    return $field;
                })->toArray();

                return $component->schema($sectionComponents);
            }
            return $component;
        })->toArray();

        return $schema->components($components);
    }

    public function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\CarModels\Schemas\CarModelInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        $configuredTable = CarModelsTable::configure($table);

        return $configuredTable
            ->recordTitleAttribute('name')
            ->headerActions([
                CreateAction::make()
                    ->label('Yeni Model'),
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
