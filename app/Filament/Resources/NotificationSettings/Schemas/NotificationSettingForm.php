<?php

namespace App\Filament\Resources\NotificationSettings\Schemas;

use App\Enums\NotificationEventEnum;
use App\Enums\NotificationPriorityEnum;
use App\Enums\NotificationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\NotificationSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class NotificationSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bildirim Ayarları')
                    ->schema([
                        Select::make('event')
                            ->label('Event')
                            ->options(NotificationEventEnum::getLabels())
                            ->searchable()
                            ->required()
                            ->helperText('Hangi event için bildirim gönderilecek')
                            ->native(false)
                            ->rules([
                                function ($get, $livewire) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                        $role = $get('role');
                                        if ($role) {
                                            $query = NotificationSetting::where('event', $value)
                                                ->where('role', $role);
                                            
                                            // Edit sayfasında mevcut kaydı hariç tut
                                            if (isset($livewire->record)) {
                                                $query->where('id', '!=', $livewire->record->id);
                                            }
                                            
                                            if ($query->exists()) {
                                                $fail('Bu event ve rol kombinasyonu zaten mevcut.');
                                            }
                                        }
                                    };
                                },
                            ]),

                        Select::make('role')
                            ->label('Rol')
                            ->options([
                                UserRoleEnum::SUPER_ADMIN->value => 'Süper Admin',
                                UserRoleEnum::CENTER_STAFF->value => 'Merkez Çalışanı',
                                UserRoleEnum::DEALER_OWNER->value => 'Bayi Sahibi',
                                UserRoleEnum::DEALER_STAFF->value => 'Bayi Çalışanı',
                            ])
                            ->searchable()
                            ->required()
                            ->helperText('Hangi role bildirim gönderilecek')
                            ->native(false)
                            ->rules([
                                function ($get, $livewire) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $livewire) {
                                        $event = $get('event');
                                        if ($event) {
                                            $query = NotificationSetting::where('event', $event)
                                                ->where('role', $value);
                                            
                                            // Edit sayfasında mevcut kaydı hariç tut
                                            if (isset($livewire->record)) {
                                                $query->where('id', '!=', $livewire->record->id);
                                            }
                                            
                                            if ($query->exists()) {
                                                $fail('Bu event ve rol kombinasyonu zaten mevcut.');
                                            }
                                        }
                                    };
                                },
                            ]),

                        Select::make('priority')
                            ->label('Öncelik')
                            ->options(NotificationPriorityEnum::getLabels())
                            ->required()
                            ->helperText('Bildirimin öncelik seviyesi')
                            ->native(false),

                        Select::make('status')
                            ->label('Durum')
                            ->options(NotificationStatusEnum::getLabels())
                            ->default(NotificationStatusEnum::ACTIVE->value)
                            ->required()
                            ->helperText('Bildirimin aktif/pasif durumu')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Mesaj Şablonu')
                    ->schema([
                        Textarea::make('message_template')
                            ->label('Mesaj Şablonu')
                            ->required()
                            ->rows(5)
                            ->helperText('Mesaj şablonu. Değişkenler için {değişken_adı} formatını kullanın. Örnek: {order_id}, {dealer_name}, {user_name}')
                            ->columnSpanFull()
                            ->placeholder('Örnek: Yeni sipariş oluşturuldu: {dealer_name} - #{order_id}'),
                    ]),
            ]);
    }
}
