<?php

namespace App\Filament\Resources\BulkSms\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkSmsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temel Bilgiler')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Ad'),

                        TextEntry::make('message')
                            ->label('SMS İçeriği')
                            ->columnSpanFull(),

                        TextEntry::make('sender')
                            ->label('Gönderici'),

                        TextEntry::make('target_type')
                            ->label('Hedef Tipi')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'all' => 'Tümüne Gönder',
                                'customers' => 'Sadece Müşteriler',
                                'dealers' => 'Sadece Bayiler',
                                'both' => 'Müşteriler ve Bayiler',
                                'custom' => 'Özel Seçim',
                                default => $state,
                            })
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Durum')
                            ->badge()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'draft' => 'Taslak',
                                'sending' => 'Gönderiliyor',
                                'completed' => 'Tamamlandı',
                                'failed' => 'Başarısız',
                                default => $state,
                            })
                            ->color(fn ($state) => match ($state) {
                                'draft' => 'gray',
                                'sending' => 'warning',
                                'completed' => 'success',
                                'failed' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('total_recipients')
                            ->label('Toplam Alıcı')
                            ->numeric(),

                        TextEntry::make('sent_count')
                            ->label('Gönderilen')
                            ->numeric(),

                        TextEntry::make('failed_count')
                            ->label('Başarısız')
                            ->numeric(),

                        TextEntry::make('createdBy.name')
                            ->label('Oluşturan'),

                        TextEntry::make('sent_at')
                            ->label('Gönderim Başlangıç')
                            ->dateTime('d.m.Y H:i')
                            ->visible(fn ($record) => $record->sent_at),

                        TextEntry::make('completed_at')
                            ->label('Tamamlanma')
                            ->dateTime('d.m.Y H:i')
                            ->visible(fn ($record) => $record->completed_at),

                        TextEntry::make('created_at')
                            ->label('Oluşturulma')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
