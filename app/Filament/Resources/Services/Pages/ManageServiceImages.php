<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class ManageServiceImages extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public ?string $heading = 'Galeri Yönetimi';

    public ?string $subheading = 'Hizmet görsellerini yönetin';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Geri')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => ServiceResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $record = $this->record;
        $currentStatus = $record?->status ?? null;

        return $schema
            ->components([
                ...ServiceForm::getGalleryStep($currentStatus),
            ]);
    }

    protected function afterSave(): void
    {
        // Repeater orderColumn kullandığı için order değerleri otomatik güncellenir
        // Ancak yeni eklenen görseller için order değerlerini kontrol et
        $images = $this->record->images()->orderBy('order')->get();
        $needsUpdate = false;

        foreach ($images as $index => $image) {
            if ($image->order !== $index) {
                $image->update(['order' => $index]);
                $needsUpdate = true;
            }
        }

        // Eğer order değerleri güncellendiyse, tekrar yükle
        if ($needsUpdate) {
            $this->record->refresh();
        }
    }
}
