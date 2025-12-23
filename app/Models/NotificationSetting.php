<?php

namespace App\Models;

use App\Enums\NotificationEventEnum;
use App\Enums\NotificationPriorityEnum;
use App\Enums\NotificationStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'role',
        'message_template',
        'priority',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event' => NotificationEventEnum::class,
            'priority' => NotificationPriorityEnum::class,
            'status' => NotificationStatusEnum::class,
        ];
    }

    /**
     * Scope a query to only include active settings.
     */
    #[Scope]
    public function scopeActive(Builder $query): void
    {
        $query->where('status', NotificationStatusEnum::ACTIVE->value);
    }

    /**
     * Scope a query to only include settings for a specific role.
     */
    #[Scope]
    public function scopeForRole(Builder $query, string $role): void
    {
        $query->where('role', $role);
    }

    /**
     * Scope a query to only include settings for a specific event.
     */
    #[Scope]
    public function scopeForEvent(Builder $query, string $event): void
    {
        $query->where('event', $event);
    }
}
