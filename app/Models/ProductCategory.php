<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'available_parts',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'available_parts' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all products for this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
