<?php

namespace App\Filament\Pages;

use App\Enums\UserRoleEnum;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use BackedEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ServiceStatusManagement extends Page implements HasSchemas, HasTable
{
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Hizmet Arama';

    protected static ?string $title = 'Hizmet Arama';

    protected string $view = 'filament.pages.service-status-management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Hizmet Yönetimi';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public ?Service $service = null;

    public function mount(): void
    {
        $this->form->fill([
            'service_no' => '',
        ]);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasAnyRole([
            UserRoleEnum::DEALER_OWNER->value,
            UserRoleEnum::DEALER_STAFF->value,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hizmet Arama')
                    ->schema([
                        TextInput::make('service_no')
                            ->label('Hizmet No')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Hizmet numarasını girin'),
                    ])
                    ->icon('heroicon-o-magnifying-glass'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addServiceStatusLog')
                ->label('Servis Durum Kaydı Ekle')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => $this->service !== null)
                ->modalHeading('Servis Durum Kaydı Ekleme')
                ->modalSubmitActionLabel('Kaydet')
                ->modalCancelActionLabel('İptal')
                ->form([
                    RichEditor::make('notes')
                        ->label('Notlar')
                        ->fileAttachmentsDisk(config('filesystems.default'))
                        ->fileAttachmentsVisibility('public')
                        ->placeholder('Servis durum kaydı notlarını girin (opsiyonel)')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    if (!$this->service) {
                        Notification::make()
                            ->warning()
                            ->title('Hizmet Bulunamadı')
                            ->body('Önce bir hizmet araması yapmalısınız.')
                            ->send();
                        return;
                    }

                    $user = auth()->user();

                    ServiceStatusLog::create([
                        'service_id' => $this->service->id,
                        'from_dealer_id' => $this->service->dealer_id, // Servis oluşturan bayi
                        'to_dealer_id' => $user->dealer_id,
                        'user_id' => $user->id,
                        'notes' => $data['notes'] ?? null,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Servis Durum Kaydı Eklendi')
                        ->body('Servis durum kaydı başarıyla eklendi.')
                        ->send();

                    // Tabloyu yenile
                    $this->resetTable();
                }),
        ];
    }

    public function searchService(): void
    {
        $serviceNo = $this->form->getState()['service_no'] ?? '';

        if (empty($serviceNo)) {
            Notification::make()
                ->warning()
                ->title('Hizmet No Gerekli')
                ->body('Lütfen bir hizmet numarası girin.')
                ->send();
            return;
        }

        $user = auth()->user();
        $service = Service::where('service_no', $serviceNo)->first();

        if (!$service) {
            Notification::make()
                ->danger()
                ->title('Hizmet Bulunamadı')
                ->body('Girdiğiniz hizmet numarasına ait bir servis bulunamadı.')
                ->send();
            $this->service = null;
            return;
        }

        // Bayi tüm hizmetleri arayabilir (servis durum yönetimi için)
        $this->service = $service;

        Notification::make()
            ->success()
            ->title('Hizmet Bulundu')
            ->body('Servis bilgileri yüklendi.')
            ->send();
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                if (!$this->service) {
                    return ServiceStatusLog::query()->whereRaw('1 = 0');
                }
                
                return ServiceStatusLog::query()->where('service_id', $this->service->id);
            })
            ->columns([
                TextColumn::make('fromDealer.name')
                    ->label('Uygulayan Bayi')
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-left'),

                TextColumn::make('toDealer.name')
                    ->label('Gidilen Şube')
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-right'),

                TextColumn::make('user.name')
                    ->label('Ekleyen Kullanıcı')
                    ->icon('heroicon-o-user'),

                TextColumn::make('notes')
                    ->label('Notlar')
                    ->html()
                    ->limit(50)
                    ->tooltip(fn ($record) => strip_tags($record->notes ?? ''))
                    ->placeholder('Not yok'),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Henüz log kaydı yok')
            ->emptyStateDescription('Bu servis için henüz bir durum logu eklenmemiş.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public function getServiceInfolistProperty()
    {
        if (!$this->service) {
            return null;
        }

        // ServiceResource'daki infolist'i kullan ve Livewire component'ine bağla
        return \App\Filament\Resources\Services\ServiceResource::infolist(
            Schema::make()
                ->record($this->service)
                ->livewire($this)
        );
    }
}

