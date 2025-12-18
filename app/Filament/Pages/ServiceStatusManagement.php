<?php

namespace App\Filament\Pages;

use App\Enums\UserRoleEnum;
use App\Models\Dealer;
use App\Models\Service;
use App\Models\ServiceStatusLog;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;

class ServiceStatusManagement extends Page implements HasSchemas, HasTable, HasInfolists
{
    use InteractsWithSchemas;
    use InteractsWithTable;
    use InteractsWithInfolists;

    protected static ?string $navigationLabel = 'Servis Durum Yönetimi';

    protected static ?string $title = 'Servis Durum Yönetimi';

    protected string $view = 'filament.pages.service-status-management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Hizmet Yönetimi';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public ?Service $service = null;

    public function mount(): void
    {
        $this->form->fill([
            'service_no' => '',
            'from_dealer_id' => null,
            'notes' => '',
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
        $user = auth()->user();

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

                Section::make('Log Ekleme')
                    ->schema([
                        Select::make('from_dealer_id')
                            ->label('Gelen Şube')
                            ->options(function () {
                                return Dealer::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->placeholder('Opsiyonel - Hizmetin geldiği şube')
                            ->helperText('Eğer hizmet başka bir şubeden geliyorsa seçin'),

                        TextInput::make('to_dealer_display')
                            ->label('Giden Şube')
                            ->default(fn () => $user->dealer?->name ?? 'Merkez')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Otomatik olarak sizin şubeniz seçilir'),

                        Textarea::make('notes')
                            ->label('Notlar')
                            ->rows(3)
                            ->maxLength(65535)
                            ->placeholder('Log notlarını girin (opsiyonel)'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn () => $this->service !== null),
            ])
            ->statePath('data');
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

        // Dealer kontrolü - sadece kendi dealer_id'sine sahip servisleri görebilir
        if ($user->dealer_id && $service->dealer_id !== $user->dealer_id) {
            Notification::make()
                ->warning()
                ->title('Yetki Hatası')
                ->body('Bu servise erişim yetkiniz bulunmamaktadır.')
                ->send();
            $this->service = null;
            return;
        }

        $this->service = $service;

        Notification::make()
            ->success()
            ->title('Hizmet Bulundu')
            ->body('Servis bilgileri yüklendi.')
            ->send();
    }

    public function addLog(): void
    {
        $data = $this->form->getState();

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
            'from_dealer_id' => $data['from_dealer_id'] ?? null,
            'to_dealer_id' => $user->dealer_id,
            'user_id' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        // Form'u temizle (notes hariç)
        $this->form->fill([
            'service_no' => $this->form->getState()['service_no'],
            'from_dealer_id' => null,
            'notes' => '',
        ]);

        Notification::make()
            ->success()
            ->title('Log Eklendi')
            ->body('Servis durum logu başarıyla eklendi.')
            ->send();

        // Tabloyu yenile
        $this->resetTable();
    }

    public function serviceStatusLogsTable(Table $table): Table
    {
        return $table
            ->query(
                fn () => $this->service
                    ? ServiceStatusLog::query()->where('service_id', $this->service->id)
                    : ServiceStatusLog::query()->whereRaw('1 = 0')
            )
            ->columns([
                TextColumn::make('fromDealer.name')
                    ->label('Gelen Şube')
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-left'),

                TextColumn::make('toDealer.name')
                    ->label('Giden Şube')
                    ->placeholder('Merkez')
                    ->icon('heroicon-o-arrow-right'),

                TextColumn::make('user.name')
                    ->label('Ekleyen Kullanıcı')
                    ->icon('heroicon-o-user'),

                TextColumn::make('notes')
                    ->label('Notlar')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->notes)
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

    public function serviceInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->service)
            ->schema([
                Section::make('Özet Bilgiler')
                    ->schema([
                        Infolists\Components\TextEntry::make('service_no')
                            ->label('Hizmet Numarası')
                            ->size('lg')
                            ->weight('bold')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Durum')
                            ->badge()
                            ->size('lg')
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => match ($state->value) {
                                'draft' => 'gray',
                                'pending' => 'warning',
                                'processing' => 'info',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('customer.name')
                            ->label('Müşteri')
                            ->size('lg')
                            ->weight('bold')
                            ->icon('heroicon-o-user')
                            ->columnSpan(1),

                        Infolists\Components\TextEntry::make('carBrand.name')
                            ->label('Marka')
                            ->icon('heroicon-o-tag')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-information-circle'),

                Section::make('Araç Bilgileri')
                    ->schema([
                        Infolists\Components\TextEntry::make('carModel.name')
                            ->label('Model')
                            ->icon('heroicon-o-cog-6-tooth'),

                        Infolists\Components\TextEntry::make('year')
                            ->label('Yıl')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('plate')
                            ->label('Plaka')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-identification')
                            ->placeholder('Girilmemiş'),

                        Infolists\Components\TextEntry::make('km')
                            ->label('Kilometre')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 0, ',', '.') . ' km' : 'Girilmemiş')
                            ->icon('heroicon-o-map-pin'),
                    ])
                    ->columns(2)
                    ->icon('heroicon-o-truck')
                    ->collapsible(),
            ]);
    }
}

