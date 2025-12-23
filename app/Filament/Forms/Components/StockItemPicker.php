<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Get;

class StockItemPicker extends Field
{
    protected string $view = 'filament.forms.components.stock-item-picker';

    protected array|Closure|null $appliedParts = null;

    protected int|Closure|null $dealerId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(null);
        $this->dehydrated(true);

        // Reactive güncelleme için live() modifier
        $this->live(onBlur: false);

        // State değişikliklerini dinle
        // Not: dispatch() metodu form component'lerinde yok, Livewire watch ile dinleniyor

    }

    public function appliedParts(array|Closure|null $appliedParts): static
    {
        $this->appliedParts = $appliedParts;

        return $this;
    }

    public function getAppliedParts(?Get $get = null): array
    {
        if ($get === null) {
            return [];
        }

        // Wizard içinde Repeater içindeyken state erişimi
        // Önce wizard root'a çıkmayı dene
        $appliedParts = null;

        // Farklı yolları dene
        $paths = [
            'applied_parts',           // Direkt erişim
            '../applied_parts',        // Repeater parent
            '../../applied_parts',     // Wizard step
            '../../../applied_parts',  // Wizard root (fallback)
        ];

        foreach ($paths as $path) {
            try {
                $value = $get($path);

                if ($value !== null) {
                    $appliedParts = $value;
                    break;
                }
            } catch (\Exception $e) {
                // Path geçersizse devam et
                continue;
            }
        }

        // Eğer hala null ise, closure'ı evaluate et
        if ($appliedParts === null && $this->appliedParts !== null) {
            $appliedParts = $this->evaluate($this->appliedParts, [
                'get' => $get,
            ]);
        }

        // String ise JSON decode et
        if (is_string($appliedParts)) {
            $appliedParts = json_decode($appliedParts, true) ?? [];
        }

        return is_array($appliedParts) ? $appliedParts : [];
    }

    public function dealerId(int|Closure|null $dealerId): static
    {
        $this->dealerId = $dealerId;

        return $this;
    }

    public function getDealerId(?Get $get = null): ?int
    {
        if ($get === null) {
            return null;
        }

        // Wizard içinde Repeater içindeyken state erişimi
        $dealerId = null;

        // Farklı yolları dene
        $paths = [
            'dealer_id',           // Direkt erişim (normal form)
            '../dealer_id',        // Repeater parent
            '../../dealer_id',     // Wizard step
            '../../../dealer_id',  // Wizard root (fallback)
        ];

        foreach ($paths as $path) {
            try {
                $value = $get($path);

                if ($value !== null) {
                    $dealerId = $value;
                    break;
                }
            } catch (\Exception $e) {
                // Path geçersizse devam et
                continue;
            }
        }

        // Eğer hala null ise, closure'ı evaluate et
        if ($dealerId === null && $this->dealerId !== null) {
            try {
                $dealerId = $this->evaluate($this->dealerId, [
                    'get' => $get,
                ]);
            } catch (\Exception $e) {
                // Hata durumunda devam et
            }
        }

        // Record'dan al (edit sayfası için)
        if ($dealerId === null) {
            $record = $this->getRecord();
            if ($record && isset($record->dealer_id)) {
                $dealerId = $record->dealer_id;
            }
        }

        return is_numeric($dealerId) ? (int) $dealerId : null;
    }
}
