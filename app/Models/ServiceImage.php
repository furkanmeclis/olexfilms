<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'image_path',
        'title',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($serviceImage) {
            // Eğer order değeri yoksa, otomatik ata
            if ($serviceImage->order === null) {
                $maxOrder = static::where('service_id', $serviceImage->service_id)
                    ->max('order') ?? -1;
                $serviceImage->order = $maxOrder + 1;
            }
        });
    }

    /**
     * Get the service that owns this image.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

