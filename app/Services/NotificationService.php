<?php

namespace App\Services;

use App\Enums\NotificationEventEnum;
use App\Enums\NotificationPriorityEnum;
use App\Enums\UserRoleEnum;
use App\Models\NotificationSetting;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Send notification to a specific user
     */
    public static function send(string $event, array $data, ?User $user = null): void
    {
        if (! $user) {
            return;
        }

        $userRole = $user->roles->first()?->name;

        if (! $userRole) {
            return;
        }

        $setting = NotificationSetting::forEvent($event)
            ->forRole($userRole)
            ->active()
            ->first();

        if (! $setting) {
            return;
        }

        $title = self::getTitle($event, $setting->priority);
        $body = self::getMessageTemplate($event, $userRole, $data);

        $notification = Notification::make()
            ->title($title)
            ->body($body);

        // Priority'ye göre icon/color ayarla (toDatabase()'den önce)
        match ($setting->priority) {
            NotificationPriorityEnum::CRITICAL => $notification->danger(),
            NotificationPriorityEnum::HIGH => $notification->warning(),
            NotificationPriorityEnum::MEDIUM => $notification->info(),
            NotificationPriorityEnum::LOW => $notification->success(),
        };

        $user->notify($notification->toDatabase());
    }

    /**
     * Send notification to all users with a specific role
     */
    public static function sendToRole(string $event, string $role, array $data): void
    {
        $setting = NotificationSetting::forEvent($event)
            ->forRole($role)
            ->active()
            ->first();

        if (! $setting) {
            return;
        }

        $users = User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })
            ->where('is_active', true)
            ->get();

        // Bayi kullanıcıları için dealer kontrolü
        if (in_array($role, [UserRoleEnum::DEALER_OWNER->value, UserRoleEnum::DEALER_STAFF->value])) {
            $users = $users->filter(function ($user) use ($data) {
                // Eğer data'da dealer_id varsa, sadece o bayinin kullanıcılarına gönder
                if (isset($data['dealer_id'])) {
                    return $user->dealer_id === $data['dealer_id'] && $user->canAccess();
                }
                return $user->canAccess();
            });
        }

        foreach ($users as $user) {
            self::send($event, $data, $user);
        }
    }

    /**
     * Send notification to multiple roles
     */
    public static function sendToRoles(string $event, array $roles, array $data): void
    {
        foreach ($roles as $role) {
            self::sendToRole($event, $role, $data);
        }
    }

    /**
     * Get message template and replace variables
     */
    public static function getMessageTemplate(string $event, string $role, array $data): string
    {
        $setting = NotificationSetting::forEvent($event)
            ->forRole($role)
            ->active()
            ->first();

        if (! $setting) {
            return '';
        }

        $template = $setting->message_template;

        // Değişkenleri değiştir
        foreach ($data as $key => $value) {
            $placeholder = '{' . $key . '}';
            $template = Str::replace($placeholder, (string) $value, $template);
        }

        return $template;
    }

    /**
     * Check if notification should be sent
     */
    public static function shouldSend(string $event, string $role): bool
    {
        return NotificationSetting::forEvent($event)
            ->forRole($role)
            ->active()
            ->exists();
    }

    /**
     * Get notification title based on event and priority
     */
    private static function getTitle(string $event, NotificationPriorityEnum $priority): string
    {
        $eventLabels = NotificationEventEnum::getLabels();
        $eventEnum = NotificationEventEnum::tryFrom($event);

        if ($eventEnum && isset($eventLabels[$eventEnum->value])) {
            return $eventLabels[$eventEnum->value];
        }

        return 'Yeni Bildirim';
    }
}

