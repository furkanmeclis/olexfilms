<?php

namespace App\Models;

use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dealer_id',
        'created_by',
        'type',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'district',
        'tc_no',
        'tax_no',
        'tax_office',
        'fcm_token',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomerTypeEnum::class,
        ];
    }

    /**
     * Get the dealer that owns the customer.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get the user who created the customer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all services for this customer.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope a query to only include customers for a specific dealer.
     */
    #[Scope]
    public function scopeForDealer(Builder $query, ?int $dealerId): void
    {
        if ($dealerId !== null) {
            $query->where('dealer_id', $dealerId);
        }
    }
}
