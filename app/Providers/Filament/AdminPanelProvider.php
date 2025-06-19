<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Enums\MaxWidth;
use Filament\Navigation\MenuItem;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\URL;
use Filament\Navigation\NavigationItem;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin') 
            ->font('Nunito')
            ->path('admin')
            // ->unsavedChangesAlerts()
            ->brandName('Ecommerce')
            // ->brandLogo(asset('images/logo.svg'))
            // ->brandLogoHeight('2.5rem')
            // ->favicon(asset('images/favicon.png'))
            ->maxContentWidth(MaxWidth::Full)
            ->login()
            // ->spa()
            ->authGuard('web')
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->authPasswordBroker('users')
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4rem')
            ->sidebarWidth('17rem')
            ->profile(EditProfile::class)
            ->profile(isSimple: false)
            ->userMenuItems([
                'profile' => MenuItem::make()->label('User Profile'),
                'logout' => MenuItem::make()->label('Log out'),
            ])
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Yellow,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
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
            ])
            ->navigationGroups([
                'Products Management',
                'Order Management',
                'User Registry',
                'Filament Shield',
                'Account',
            ])
            ->navigationItems([
                NavigationItem::make('Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn() => URL::route('filament.admin.auth.profile'))
                    ->sort(998)
                    ->group('Account'),
                NavigationItem::make()
                    ->label('Logout')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->url(fn() => URL::route('filament.admin.auth.logout'))
                    ->sort(999)
                    ->group('Account')
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            // OrderStatusWidget::class,
        ];
    }
}
