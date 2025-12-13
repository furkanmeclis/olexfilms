<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexptgApiUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'password',
        'is_active',
        'last_used_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user that owns this API user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this API user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reports associated with this API user.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(NexptgReport::class, 'api_user_id');
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsedAt(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
