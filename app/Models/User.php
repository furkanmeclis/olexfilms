<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

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

    public function getFilamentAvatarUrl(): ?string
    {
        // Önce avatar_url'i kontrol et (Breezy için)
        if ($this->avatar_url) {
            try {
                // Temporary URL kullan (1 saat geçerli)
                return Storage::disk(config('filesystems.default'))->temporaryUrl(
                    $this->avatar_url,
                    now()->addHour()
                );
            } catch (\Exception $e) {
                // Eğer temporary URL oluşturulamazsa, normal URL'i dene
                return Storage::disk(config('filesystems.default'))->url($this->avatar_url);
            }
        }

        // Fallback olarak avatar'ı kullan
        if ($this->avatar) {
            try {
                // Temporary URL kullan (1 saat geçerli)
                return Storage::disk(config('filesystems.default'))->temporaryUrl(
                    $this->avatar,
                    now()->addHour()
                );
            } catch (\Exception $e) {
                // Eğer temporary URL oluşturulamazsa, normal URL'i dene
                return Storage::disk(config('filesystems.default'))->url($this->avatar);
            }
        }

        return asset('images/placeholder.png');
    }
}
