<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dealer extends Model
{
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Dealer $dealer) {
            if (empty($dealer->dealer_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (static::where('dealer_code', $code)->exists());

                $dealer->dealer_code = $code;
            }
        });

        static::updating(function (Dealer $dealer) {
            // dealer_code değiştirilemez
            if ($dealer->isDirty('dealer_code') && $dealer->getOriginal('dealer_code')) {
                $dealer->dealer_code = $dealer->getOriginal('dealer_code');
            }
        });
    }

    protected $fillable = [
        'dealer_code',
        'name',
        'email',
        'phone',
        'address',
        'logo_path',
        'is_active',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'linkedin_url',
        'website_url',
        'city',
        'district',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users for this dealer.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all orders for this dealer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all stock items for this dealer.
     */
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    /**
     * Get all services for this dealer.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Scope a query to only include active dealers.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
