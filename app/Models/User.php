<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dealer_id',
        'phone',
        'avatar_url',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the dealer that owns the user.
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * Get all customers created by this user.
     */
    public function createdCustomers(): HasMany
    {
        return $this->hasMany(Customer::class, 'created_by');
    }

    /**
     * Get all services created by this user.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'user_id');
    }

    /**
     * Get all SMS logs for this user.
     */
    public function smsLogs(): MorphMany
    {
        return $this->morphMany(SmsLog::class, 'notifiable');
    }

    /**
     * Get the NexPTG API user for this user.
     */
    public function nexptgApiUser(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NexptgApiUser::class);
    }

    /**
     * Scope a query to only include active users.
     */
    #[Scope]
    protected function isActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Check if user can access the system.
     * Returns false if user is inactive or if dealer is inactive.
     */
    public function canAccess(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->dealer_id && $this->dealer) {
            return $this->dealer->is_active;
        }

        return true;
    }

    /**
     * Get the Filament avatar URL for the user.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        $disk = config('filesystems.default');
        if ($disk !== 's3') {
            return $this->avatar_url ? Storage::disk($disk)->url($this->avatar_url) : null;
        }
        try {
            return $this->avatar_url ? Storage::disk($disk)->temporaryUrl($this->avatar_url, now()->addHours(1)) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
