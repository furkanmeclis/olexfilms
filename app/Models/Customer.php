<?php

namespace App\Models;

use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'notification_settings',
    ];

    protected function casts(): array
    {
        return [
            'type' => CustomerTypeEnum::class,
            'notification_settings' => 'array',
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
     * Get all SMS logs for this customer.
     */
    public function smsLogs(): MorphMany
    {
        return $this->morphMany(SmsLog::class, 'notifiable');
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

    /**
     * Get services with formatted data for customer page.
     * 
     * @return array
     */
    public function getServices(): array
    {
        $services = $this->services()
            ->with(['carBrand', 'carModel'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $services->map(function ($service) {
            $carBrand = $service->carBrand;
            $carModel = $service->carModel;

            // Get products from warranties
            $products = [];
            foreach ($service->warranties()->with(['stockItem.product'])->get() as $warranty) {
                if ($warranty->stockItem && $warranty->stockItem->product) {
                    $startDate = $warranty->start_date;
                    $endDate = $warranty->end_date;
                    
                    // Calculate warranty rate (progress percentage)
                    $rate = 0;
                    if ($startDate && $endDate) {
                        $totalDays = $startDate->diffInDays($endDate);
                        $elapsedDays = $startDate->diffInDays(now());
                        if ($totalDays > 0) {
                            $rate = min(100, max(0, ($elapsedDays / $totalDays) * 100));
                        }
                    }

                    $products[] = [
                        'product' => [
                            'name' => $warranty->stockItem->product->name,
                        ],
                        'product_code' => $warranty->stockItem->barcode ?? $warranty->stockItem->sku ?? '',
                        'warranty' => [
                            'start_date' => $startDate ? $startDate->format('d.m.Y') : '',
                            'end_date' => $endDate ? $endDate->format('d.m.Y') : '',
                            'rate' => round($rate, 2),
                        ],
                        'car_plate' => $service->plate ?? '',
                    ];
                }
            }

            return [
                'car' => [
                    'brand' => $carBrand ? $carBrand->name : '',
                    'model' => $carModel ? $carModel->name : '',
                    'generation' => $carModel ? $carModel->name : '', // Generation same as model for now
                    'year' => $service->year ?? '',
                    'plate' => $service->plate ?? '', 
                    'brand_logo' => $carBrand && $carBrand->logo ? $carBrand->logo_url : 'https://www.carlogos.org/car-logos/bmw-logo-2020-blue-white.png',
                ],
                'created_at' => $service->created_at ? $service->created_at->toISOString() : now()->toISOString(),
                'service_no' => $service->service_no ?? '',
                'products' => $products,
            ];
        })->toArray();
    }

    /**
     * Get notification settings with default values.
     * 
     * @return array
     */
    public function getNotificationSettingsAttribute($value): array
    {
        if (empty($value)) {
            return [
                'sms' => false,
                'email' => false,
            ];
        }
        
        // If it's already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If it's a JSON string, decode it
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [
            'sms' => false,
            'email' => false,
        ];
    }
}
