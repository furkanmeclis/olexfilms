<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'description',
        'warranty_duration',
        'micron_thickness',
        'price',
        'image_path',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'price',
    ];

    protected function casts(): array
    {
        return [
            'warranty_duration' => 'integer',
            'micron_thickness' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the stock items for the product.
     */
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    /**
     * Get the warranties for this product (through stock items).
     */
    public function warranties(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Warranty::class, StockItem::class);
    }

    /**
     * Scope a query to only include active products.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
