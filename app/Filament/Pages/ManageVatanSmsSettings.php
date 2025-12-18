<?php

namespace App\Filament\Pages;

use App\Enums\UserRoleEnum;
use App\Services\VatanSmsService;
use App\Settings\VatanSmsSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use UnitEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageVatanSmsSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static ?string $navigationLabel = 'SMS Ayarları';

    protected static ?string $title = 'Vatan SMS Ayarları';

    protected string $view = 'filament.pages.manage-vatan-sms-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'SMS';

    protected static ?int $navigationSort = 3;

    /**
     * Sadece super_admin rolüne sahip kullanıcılar SMS ayarları sayfasına erişebilir
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        
        return $user && $user->hasRole(UserRoleEnum::SUPER_ADMIN->value);
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(VatanSmsSettings::class);
        $this->form->fill([
            'api_id' => $settings->api_id,
            'api_key' => $settings->api_key,
            'sender' => $settings->sender,
            'endpoint' => $settings->endpoint,
            'installed' => $settings->installed,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Bilgileri')
                    ->schema([
                        TextInput::make('api_id')
                            ->label('API ID')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('api_key')
                            ->label('API Key')
                            ->required()
                            ->password()
                            ->maxLength(255),

                        TextInput::make('endpoint')
                            ->label('Endpoint')
                            ->required()
                            ->default('https://api.vatansms.net/api/v1')
                            ->maxLength(255),

                        Toggle::make('installed')
                            ->label('SMS Servisi Aktif')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Gönderici Ayarları')
                    ->schema([
                        Select::make('sender')
                            ->label('Gönderici Adı')
                            ->options(function () {
                                $senders = VatanSmsService::getSenderNames();
                                
                                if (!$senders) {
                                    return [];
                                }
                                
                                // API'den dönen yapı: {"code":200,"status":"success","data":[...]}
                                $senderList = $senders['data'] ?? $senders['response'] ?? [];
                                
                                if (is_array($senderList)) {
                                    $options = [];
                                    foreach ($senderList as $sender) {
                                        if (is_array($sender) && isset($sender['sender'])) {
                                            $options[$sender['sender']] = $sender['sender'];
                                        }
                                    }
                                    return $options;
                                }
                                
                                return [];
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('installed', true);
                                }
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = app(VatanSmsSettings::class);

        $settings->api_id = $data['api_id'];
        $settings->api_key = $data['api_key'];
        $settings->sender = $data['sender'];
        $settings->endpoint = $data['endpoint'];
        $settings->installed = $data['installed'] ?? false;

        $settings->save();

        Notification::make()
            ->success()
            ->title('Ayarlar kaydedildi')
            ->body('SMS ayarları başarıyla güncellendi.')
            ->send();
    }

}
