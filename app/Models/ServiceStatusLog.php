<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'from_dealer_id',
        'to_dealer_id',
        'user_id',
        'notes',
    ];

    /**
     * Get the service that owns the status log.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the dealer where the service came from.
     */
    public function fromDealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'from_dealer_id');
    }

    /**
     * Get the dealer where the service went to.
     */
    public function toDealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'to_dealer_id');
    }

    /**
     * Get the user who created the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
