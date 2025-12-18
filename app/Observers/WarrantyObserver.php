<?php

namespace App\Observers;

use App\Models\Warranty;

class WarrantyObserver
{
    /**
     * Handle the Warranty "retrieved" event.
     * Otomatik olarak süresi dolmuş garantileri pasifleştir.
     */
    public function retrieved(Warranty $warranty): void
    {
        // Eğer garanti aktifse ve süresi dolmuşsa, pasifleştir
        if ($warranty->is_active && $warranty->is_expired) {
            // Model event'lerini tetiklememek için updateQuietly kullan
            $warranty->updateQuietly(['is_active' => false]);
        }
    }

    /**
     * Handle the Warranty "updating" event.
     * Bitiş tarihi kontrolü yap.
     */
    public function updating(Warranty $warranty): void
    {
        // Eğer bitiş tarihi değiştiriliyorsa ve yeni tarih geçmişse, otomatik pasifleştir
        if ($warranty->isDirty('end_date')) {
            $newEndDate = $warranty->end_date;
            if ($newEndDate && now()->startOfDay()->greaterThan($newEndDate->startOfDay())) {
                $warranty->is_active = false;
            }
        }
    }
}

