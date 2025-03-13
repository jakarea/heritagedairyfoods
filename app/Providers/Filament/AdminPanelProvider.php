<?php

namespace App\Providers\Filament;

use App\Models\Attendance;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
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
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ])
            ->navigationItems($this->getNavigationItems())
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }

    protected function getNavigationItems(): array
    {
        if (!Auth::check()) {
            return []; // If the user is not logged in, show nothing
        }

        $user = Auth::user();
        $latestAttendance = Attendance::where('user_id', $user->id)->latest()->first();

        $isOnBreak = $latestAttendance && $latestAttendance->break_in && !$latestAttendance->break_out;
        $hasNotCheckedIn = !$latestAttendance || !$latestAttendance->check_in;

        if ($isOnBreak || $hasNotCheckedIn) {
            return [
                [
                    'label' => 'Attendance',
                    'url' => route('filament.admin.resources.attendances.index'),
                    'icon' => 'heroicon-o-clock',
                ],
            ];
        }

        // Normal navigation when user is checked in and break-out is done
        return [
            [
                'label' => 'Dashboard',
                'url' => route('filament.admin.pages.dashboard'),
                'icon' => 'heroicon-o-home',
            ],
            [
                'label' => 'Attendance',
                'url' => route('filament.admin.resources.attendances.index'),
                'icon' => 'heroicon-o-clock',
            ],
            [
                'label' => 'Users',
                'url' => route('filament.admin.resources.users.index'),
                'icon' => 'heroicon-o-user-group',
            ],
        ];
    }
}
