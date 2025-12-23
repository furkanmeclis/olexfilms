<?php

namespace App\Filament\Resources\NexptgApiUsers\Schemas;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NexptgApiUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('API Kullanıcı Bilgileri')
                    ->schema([
                        Select::make('user_id')
                            ->label('Kullanıcı')
                            ->relationship('user', 'name', modifyQueryUsing: function ($query, $record) {
                                // Edit modunda mevcut user'ı da dahil et
                                if ($record) {
                                    return $query->whereDoesntHave('nexptgApiUser')
                                        ->orWhere('id', $record->user_id);
                                }

                                // Create modunda sadece API user'ı olmayanları göster
                                return $query->whereDoesntHave('nexptgApiUser');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Her kullanıcı sadece bir API kullanıcısına sahip olabilir'),
                        TextInput::make('username')
                            ->label('Kullanıcı Adı')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('generateUsername')
                                    ->label('Rastgele Üret')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function ($set) {
                                        $set('username', strtolower(env('APP_NAME')).'_'.Str::random(16));
                                    })
                            ),

                        TextInput::make('password')
                            ->label('Şifre')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->minLength(8)
                            ->maxLength(255)
                            ->suffixAction(
                                Action::make('generatePassword')
                                    ->label('Rastgele Üret')
                                    ->icon('heroicon-m-sparkles')
                                    ->action(function ($set) {
                                        $password = Str::random(16);
                                        $set('password', $password);
                                    })
                            )
                            ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Boş bırakılırsa şifre değiştirilmez' : '')
                            ->visibleOn(['create', 'edit']),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }
}
