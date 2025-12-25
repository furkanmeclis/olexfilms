<?php

namespace App\Filament\Resources\Services\Pages;

use App\Enums\ServiceReportMatchTypeEnum;
use App\Enums\ServiceStatusEnum;
use App\Enums\UserRoleEnum;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Services\Widgets\ServiceReportsWidget;
use App\Models\NexptgReport;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $isAdmin = $user && ($user->hasRole(UserRoleEnum::SUPER_ADMIN->value) || $user->hasRole(UserRoleEnum::CENTER_STAFF->value));
        $isDealer = $user && ($user->hasRole(UserRoleEnum::DEALER_OWNER->value) || $user->hasRole(UserRoleEnum::DEALER_STAFF->value));
        $record = $this->record;
        $currentStatus = $record->status;

        $actions = [];

        // DRAFT -> PENDING (Taslak -> Bekliyor)
        if ($currentStatus === ServiceStatusEnum::DRAFT) {
            if ($isDealer || $isAdmin) {
                $actions[] = Actions\Action::make('setPending')
                    ->label('Beklemeye Al')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti beklemeye almak istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update(['status' => ServiceStatusEnum::PENDING->value]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet beklemeye alındı.')
                            ->success()
                            ->send();
                    });
            }
        }

        // PENDING -> PROCESSING (Bekliyor -> İşlemde)
        if ($currentStatus === ServiceStatusEnum::PENDING) {
            if ($isDealer || $isAdmin) {
                $actions[] = Actions\Action::make('setProcessing')
                    ->label('İşleme Al')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti işleme almak istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update(['status' => ServiceStatusEnum::PROCESSING->value]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet işleme alındı.')
                            ->success()
                            ->send();
                    });
            }
        }

        // PROCESSING -> READY (İşlemde -> Hazır)
        if ($currentStatus === ServiceStatusEnum::PROCESSING) {
            if ($isDealer || $isAdmin) {
                $actions[] = Actions\Action::make('setReady')
                    ->label('Hazır Yap')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti hazır olarak işaretlemek istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update(['status' => ServiceStatusEnum::READY->value]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet hazır olarak işaretlendi.')
                            ->success()
                            ->send();
                    });
            }
        }

        // READY -> COMPLETED (Hazır -> Tamamlandı)
        if ($currentStatus === ServiceStatusEnum::READY) {
            if ($isDealer || $isAdmin) {
                $actions[] = Actions\Action::make('setCompleted')
                    ->label('Tamamla')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti tamamlandı olarak işaretlemek istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update([
                            'status' => ServiceStatusEnum::COMPLETED->value,
                            'completed_at' => $record->completed_at ?? now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet tamamlandı olarak işaretlendi.')
                            ->success()
                            ->send();
                    });
            }
        }

        // Herhangi bir durumdan -> CANCELLED (İptal Edildi) - Sadece Admin
        if ($isAdmin && $currentStatus !== ServiceStatusEnum::COMPLETED && $currentStatus !== ServiceStatusEnum::CANCELLED) {
            $actions[] = Actions\Action::make('setCancelled')
                ->label('İptal Et')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Durum Güncelleme')
                ->modalDescription('Hizmeti iptal etmek istediğinizden emin misiniz?')
                ->action(function () use ($record) {
                    $record->update(['status' => ServiceStatusEnum::CANCELLED->value]);

                    \Filament\Notifications\Notification::make()
                        ->title('Durum güncellendi')
                        ->body('Hizmet iptal edildi.')
                        ->success()
                        ->send();
                });
        }

        // Admin için tüm durumlar arasında geçiş yapabilme (opsiyonel - direkt geçişler için)
        if ($isAdmin) {
            // DRAFT -> PROCESSING (Admin direkt geçiş yapabilir)
            if ($currentStatus === ServiceStatusEnum::DRAFT) {
                $actions[] = Actions\Action::make('setProcessingFromDraft')
                    ->label('Direkt İşleme Al')
                    ->icon('heroicon-o-arrow-right')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti direkt işleme almak istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update(['status' => ServiceStatusEnum::PROCESSING->value]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet direkt işleme alındı.')
                            ->success()
                            ->send();
                    });
            }

            // PENDING -> READY (Admin direkt geçiş yapabilir)
            if ($currentStatus === ServiceStatusEnum::PENDING) {
                $actions[] = Actions\Action::make('setReadyFromPending')
                    ->label('Direkt Hazır Yap')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti direkt hazır olarak işaretlemek istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update(['status' => ServiceStatusEnum::READY->value]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet direkt hazır olarak işaretlendi.')
                            ->success()
                            ->send();
                    });
            }

            // PROCESSING -> COMPLETED (Admin direkt geçiş yapabilir)
            if ($currentStatus === ServiceStatusEnum::PROCESSING) {
                $actions[] = Actions\Action::make('setCompletedFromProcessing')
                    ->label('Direkt Tamamla')
                    ->icon('heroicon-o-arrow-right')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Durum Güncelleme')
                    ->modalDescription('Hizmeti direkt tamamlandı olarak işaretlemek istediğinizden emin misiniz?')
                    ->action(function () use ($record) {
                        $record->update([
                            'status' => ServiceStatusEnum::COMPLETED->value,
                            'completed_at' => $record->completed_at ?? now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Durum güncellendi')
                            ->body('Hizmet direkt tamamlandı olarak işaretlendi.')
                            ->success()
                            ->send();
                    });
            }
        }

        // Admin için hizmet düzenleme
        if ($isAdmin) {
            $actions[] = Actions\Action::make('edit')
                ->label('Düzenle')
                ->icon('heroicon-o-pencil')
                ->url(fn () => ServiceResource::getUrl('edit', ['record' => $record]));
        }

        // Galeri yönetimi
        $actions[] = Actions\Action::make('manageImages')
            ->label('Galeri Yönetimi')
            ->icon('heroicon-o-photo')
            ->color('success')
            ->url(fn () => ServiceResource::getUrl('manage-images', ['record' => $record]));

        // Rapor Eşleştir
        $actions[] = Actions\Action::make('attachReport')
            ->label('Rapor Eşleştir')
            ->icon('heroicon-o-link')
            ->color('info')
            ->form([
                Select::make('nexptg_report_id')
                    ->label('Rapor')
                    ->options(function () {
                        // Eşleşmemiş raporları getir
                        $matchedReportIds = DB::table('service_nexptg_report')
                            ->pluck('nexptg_report_id')
                            ->toArray();

                        return NexptgReport::query()
                            ->whereNotIn('id', $matchedReportIds)
                            ->orderBy('date', 'desc')
                            ->get()
                            ->mapWithKeys(function ($report) {
                                $label = $report->name.' - '.($report->date ? $report->date->format('d.m.Y H:i') : 'Tarih yok');

                                return [$report->id => $label];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->required()
                    ->placeholder('Rapor seçin...'),

                Select::make('match_type')
                    ->label('Eşleşme Tipi')
                    ->options(ServiceReportMatchTypeEnum::getLabels())
                    ->required()
                    ->default(ServiceReportMatchTypeEnum::BEFORE->value),
            ])

            ->action(function (array $data) use ($record) {
                $report = NexptgReport::find($data['nexptg_report_id']);

                if ($report && ! $report->isMatched()) {
                    $record->reports()->attach($report->id, [
                        'match_type' => $data['match_type'],
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Rapor eşleştirildi')
                        ->success()
                        ->send();
                } else {
                    \Filament\Notifications\Notification::make()
                        ->title('Hata')
                        ->body('Bu rapor zaten bir hizmete eşleştirilmiş.')
                        ->danger()
                        ->send();
                }
            })
            ->requiresConfirmation();
        $actions[] = Actions\Action::make('viewWarrantyPage')
            ->label('Garanti Görüntüle')
            ->icon('heroicon-o-eye')
            ->color('warning')
            ->url(fn () => route('warranty.show', ['serviceNo' => $record->service_no]))
            ->openUrlInNewTab();
        return $actions;
    }

    protected function getFooterWidgets(): array
    {
        return [
            ServiceReportsWidget::class,
        ];
    }
}
