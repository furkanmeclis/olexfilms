<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class CarBrand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'external_id',
        'logo',
        'last_update',
        'is_active',
        'show_name',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_update' => 'datetime',
            'show_name' => 'boolean',
        ];
    }

    /**
     * Get all models for this brand.
     */
    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class, 'brand_id');
    }

    /**
     * Get all services for this brand.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'car_brand_id');
    }

    /**
     * Scope a query to only include active brands.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function getLogoUrlAttribute(): string
    {
        $disk = config('filesystems.default');
        if ($disk === 's3') {
            try {
                return Storage::disk($disk)->temporaryUrl($this->logo, now()->addHours(5));
            } catch (\Exception $e) {
                return Storage::disk($disk)->url($this->logo);
            }
        } else {
            return Storage::disk($disk)->url($this->logo);
        }
    }
}
