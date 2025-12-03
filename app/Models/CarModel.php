<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'name',
        'external_id',
        'last_update',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_update' => 'datetime',
        ];
    }

    /**
     * Get the brand that owns the model.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(CarBrand::class, 'brand_id');
    }

    /**
     * Scope a query to only include active models.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
