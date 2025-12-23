<?php

namespace App\Filament\Resources\SmsLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('SMS Bilgileri')
                    ->schema([
                        TextEntry::make('phone')
                            ->label('Telefon'),

                        TextEntry::make('message')
                            ->label('Mesaj')
                            ->columnSpanFull(),

                        TextEntry::make('sender')
                            ->label('Gönderici'),

                        TextEntry::make('status')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'pending' => 'Beklemede',
                                'sent' => 'Gönderildi',
                                'failed' => 'Başarısız',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'pending' => 'warning',
                                'sent' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('message_type')
                            ->label('Mesaj Tipi'),

                        TextEntry::make('message_content_type')
                            ->label('İçerik Tipi'),

                        TextEntry::make('sent_at')
                            ->label('Gönderim Zamanı')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),

                Section::make('API Yanıt Bilgileri')
                    ->schema([
                        TextEntry::make('response_id')
                            ->label('Yanıt ID'),

                        TextEntry::make('quantity')
                            ->label('SMS Adedi')
                            ->numeric(),

                        TextEntry::make('amount')
                            ->label('Kredi Tutarı')
                            ->money('TRY'),

                        TextEntry::make('number_count')
                            ->label('Numara Sayısı')
                            ->numeric(),

                        TextEntry::make('description')
                            ->label('Açıklama')
                            ->columnSpanFull(),

                        TextEntry::make('invalid_phones')
                            ->label('Geçersiz Telefonlar')
                            ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : '')
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record->invalid_phones)),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->status === 'sent'),

                Section::make('İlişkili Bilgiler')
                    ->schema([
                        TextEntry::make('notifiable_type')
                            ->label('Alıcı Tipi')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                \App\Models\User::class => 'Kullanıcı',
                                \App\Models\Customer::class => 'Müşteri',
                                default => $state,
                            }),

                        TextEntry::make('notifiable.name')
                            ->label('Alıcı')
                            ->visible(fn ($record) => $record->notifiable),

                        TextEntry::make('sentBy.name')
                            ->label('Gönderen Kullanıcı'),

                        TextEntry::make('bulkSms.name')
                            ->label('Toplu SMS')
                            ->visible(fn ($record) => $record->bulkSms),
                    ])
                    ->columns(2),
            ]);
    }
}
