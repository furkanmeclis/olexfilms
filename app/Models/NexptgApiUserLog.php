<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexptgApiUserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'nexptg_api_user_id',
        'type',
        'status_code',
        'message',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the API user that owns this log.
     */
    public function nexptgApiUser(): BelongsTo
    {
        return $this->belongsTo(NexptgApiUser::class);
    }
}
