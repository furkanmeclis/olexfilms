<?php

namespace App\Filament\Resources\Dealers\RelationManagers;

use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Enums\UserRoleEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Bayi Çalışanları';

    protected static ?string $modelLabel = 'Çalışan';

    protected static ?string $pluralModelLabel = 'Çalışanlar';

    public function form(Schema $schema): Schema
    {
        // UserForm'u kullan ama dealer_id'yi otomatik set et
        $form = UserForm::configure($schema);

        // Form component'lerini modify et - dealer_id'yi Hidden yap
        $components = collect($form->getComponents())->map(function ($component) {
            if ($component instanceof \Filament\Schemas\Components\Section) {
                $sectionComponents = collect($component->getComponents(withActions: false, withHidden: false))->map(function ($field) {
                    if ($field->getName() === 'dealer_id') {
                        return Hidden::make('dealer_id')
                            ->default(fn () => $this->ownerRecord->id);
                    }
                    return $field;
                })->toArray();

                return $component->schema($sectionComponents);
            }
            return $component;
        })->toArray();

        // Eğer dealer_id field'ı yoksa, ekle
        $hasDealerId = collect($components)->contains(function ($component) {
            if ($component instanceof \Filament\Schemas\Components\Section) {
                return collect($component->getComponents(withActions: false, withHidden: false))
                    ->contains(fn ($field) => $field->getName() === 'dealer_id');
            }
            return false;
        });

        if (!$hasDealerId) {
            // İlk section'a dealer_id ekle
            if (isset($components[0]) && $components[0] instanceof \Filament\Schemas\Components\Section) {
                $firstSectionComponents = $components[0]->getComponents(withActions: false, withHidden: false);
                $firstSectionComponents[] = Hidden::make('dealer_id')
                    ->default(fn () => $this->ownerRecord->id);
                $components[0] = $components[0]->schema($firstSectionComponents);
            }
        }

        return $schema->components($components);
    }

    public function table(Table $table): Table
    {
        $configuredTable = UsersTable::configure($table);

        return $configuredTable
            ->recordTitleAttribute('name')
            ->headerActions([
                CreateAction::make()
                    ->label('Hızlı Çalışan Ekle')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['dealer_id'] = $this->ownerRecord->id;
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Rolü dealer_staff olarak ata
                        $role = Role::findByName(UserRoleEnum::DEALER_STAFF->value);
                        if ($role) {
                            $record->assignRole($role);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Görüntüle'),
                EditAction::make()
                    ->label('Düzenle'),
                DeleteAction::make()
                    ->label('Sil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Sil'),
                ]),
            ]);
    }
}
