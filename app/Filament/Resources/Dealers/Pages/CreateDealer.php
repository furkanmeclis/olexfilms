<?php

namespace App\Filament\Resources\Dealers\Pages;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\Dealers\DealerResource;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Hash;

class CreateDealer extends CreateRecord
{
    use HasWizard;

    protected static string $resource = DealerResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make('Bayi Bilgileri')
                ->description('Bayi kurumsal bilgilerini girin')
                ->schema([
                    Section::make('Bayi Bilgileri')
                        ->schema([
                            TextInput::make('name')
                                ->label('Bayi Adı')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('email')
                                ->label('E-posta')
                                ->email()
                                ->required()
                                ->unique()
                                ->maxLength(255),

                            TextInput::make('phone')
                                ->label('Telefon')
                                ->required()
                                ->tel()
                                ->maxLength(255),

                            Textarea::make('address')
                                ->label('Adres')
                                ->required()
                                ->rows(3)
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Section::make('Logo')
                        ->schema([
                            FileUpload::make('logo_path')
                                ->label('Logo')
                                ->image()
                                ->directory('dealers/logos')
                                ->visibility('public')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->nullable()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Durum')
                        ->schema([
                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true)
                                ->required(),
                        ]),
                ]),

            Step::make('Bayi Yöneticisi')
                ->description('Bayi yöneticisi oluşturun veya mevcut kullanıcıyı seçin')
                ->schema([
                    Section::make('Yönetici Seçimi')
                        ->schema([
                            Radio::make('owner_type')
                                ->label('Yönetici Seçimi')
                                ->options([
                                    'new' => 'Yeni Kullanıcı Oluştur',
                                    'existing' => 'Mevcut Kullanıcıyı Seç',
                                ])
                                ->default('new')
                                ->required()
                                ->live()
                                ->columnSpanFull(),

                            Select::make('existing_user_id')
                                ->label('Mevcut Kullanıcı')
                                ->options(fn () => User::whereNull('dealer_id')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->visible(fn ($get) => $get('owner_type') === 'existing')
                                ->required(fn ($get) => $get('owner_type') === 'existing')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Yeni Kullanıcı Bilgileri')
                        ->schema([
                            TextInput::make('owner_name')
                                ->label('Ad Soyad')
                                ->required(fn ($get) => $get('owner_type') === 'new')
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('owner_type') === 'new'),

                            TextInput::make('owner_email')
                                ->label('E-posta')
                                ->email()
                                ->required(fn ($get) => $get('owner_type') === 'new')
                                ->unique(User::class, 'email')
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('owner_type') === 'new'),

                            TextInput::make('owner_phone')
                                ->label('Telefon')
                                ->required(fn ($get) => $get('owner_type') === 'new')
                                ->tel()
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('owner_type') === 'new'),

                            TextInput::make('owner_password')
                                ->label('Şifre')
                                ->password()
                                ->required(fn ($get) => $get('owner_type') === 'new')
                                ->minLength(8)
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('owner_type') === 'new')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->visible(fn ($get) => $get('owner_type') === 'new'),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove owner fields from dealer data
        unset($data['owner_type'], $data['existing_user_id'], $data['owner_name'], $data['owner_email'], $data['owner_phone'], $data['owner_password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $dealer = $this->record;

        if ($data['owner_type'] === 'existing') {
            // Assign existing user as dealer owner
            $user = User::find($data['existing_user_id']);
            if ($user) {
                $user->update(['dealer_id' => $dealer->id]);
                $user->syncRoles([UserRoleEnum::DEALER_OWNER->value]);
            }
        } elseif ($data['owner_type'] === 'new') {
            // Create new user as dealer owner
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => $data['owner_phone'],
                'password' => Hash::make($data['owner_password']),
                'dealer_id' => $dealer->id,
                'is_active' => true,
            ]);

            $user->assignRole(UserRoleEnum::DEALER_OWNER->value);
        }
    }
}
