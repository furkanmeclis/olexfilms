<?php

namespace App\Providers\Filament;

use App\Http\Middleware\CheckDealerActive;
use Cmsmaxinc\FilamentErrorPages\FilamentErrorPagesPlugin;
use Filament\Forms\Components\FileUpload;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->passwordReset()
            ->colors([
                'primary' => Color::Yellow,
            ])
            ->brandLogo(asset('images/olex-logo-yatay-dark.svg'))
            ->brandLogoHeight('3rem')
            ->darkModeBrandLogo(asset('images/olex-logo-yatay.svg'))
            ->favicon(asset('images/logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckDealerActive::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('SMS')
                    ->label('SMS'),
                NavigationGroup::make('NexPTG')
                    ->label('NexPTG'),
                NavigationGroup::make('Araç Bölümü')
                    ->label('Araç Yönetimi'),
                NavigationGroup::make('Sistem Yönetimi')
                    ->label('Sistem Yönetimi'),
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->plugins([
                FilamentErrorPagesPlugin::make()
                    ->routes(['admin/*']),
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        hasAvatars: true,
                        slug: 'my-profile'
                    )
                    ->avatarUploadComponent(fn () => FileUpload::make('avatar_url')
                        ->label('Avatar')
                        ->avatar()
                        ->image()
                        ->disk(config('filesystems.default'))
                        ->directory('avatars')
                        ->visibility('public')
                        ->imageEditor()
                        ->circleCropper()
                    )
                    ->enableBrowserSessions()
                    ->enableTwoFactorAuthentication(),
            ]);
    }
}
