<?php

namespace App\Filament\Resources\BulkSms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkSmsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        TextInput::make('name')
                            ->label('Toplu SMS Adı')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('message')
                            ->label('SMS İçeriği')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Select::make('sender')
                            ->label('Gönderici Adı')
                            ->options(function () {
                                return [
                                    app(\App\Settings\VatanSmsSettings::class)->sender => app(\App\Settings\VatanSmsSettings::class)->sender,
                                ];
                            })
                            ->searchable()
                            ->disabled()
                            ->required()
                            ->default(fn () => app(\App\Settings\VatanSmsSettings::class)->sender),

                        Select::make('message_type')
                            ->label('Mesaj Tipi')
                            ->options([
                                'normal' => 'Normal',
                                'turkce' => 'Türkçe',
                            ])
                            ->default('normal')
                            ->required(),

                        Select::make('message_content_type')
                            ->label('İçerik Tipi')
                            ->options([
                                'bilgi' => 'Bilgi',
                                'ticari' => 'Ticari',
                            ])
                            ->default('bilgi')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Hedef Seçimi')
                    ->schema([
                        Toggle::make('send_to_all')
                            ->label('Tümüne Gönder')
                            ->helperText('Tüm müşterilere ve bayilere gönderilecek')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('include_customers', false);
                                    $set('include_dealers', false);
                                }
                            })
                            ->columnSpanFull(),

                        Toggle::make('include_customers')
                            ->label('Müşteriler')
                            ->helperText('Tüm müşterilere gönderilecek')
                            ->default(false)
                            ->reactive()
                            ->disabled(fn (callable $get) => $get('send_to_all')),

                        Toggle::make('include_dealers')
                            ->label('Bayiler')
                            ->helperText('Tüm bayi sahipleri ve çalışanlarına gönderilecek')
                            ->default(false)
                            ->reactive()
                            ->disabled(fn (callable $get) => $get('send_to_all')),
                    ])
                    ->columns(2),
            ]);
    }
}
