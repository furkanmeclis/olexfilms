<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexptgHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
    ];

    /**
     * Get all measurements for this history.
     */
    public function measurements(): HasMany
    {
        return $this->hasMany(NexptgHistoryMeasurement::class);
    }
}
