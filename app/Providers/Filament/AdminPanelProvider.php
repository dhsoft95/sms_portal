<?php

namespace App\Providers\Filament;

use App\Filament\Resources\MembersResource;
use App\Filament\Resources\SmsResource\Widgets\StatsOverview;
use App\Filament\Widgets\MemberChart;
use App\Filament\Widgets\SmsChart;
use Awcodes\Overlook\OverlookPlugin;
use Chiiya\FilamentAccessControl\FilamentAccessControlPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Auth\EditProfile;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentLaravelLog\FilamentLaravelLogPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
             ->spa()
            ->login()
            ->registration()
            ->passwordReset()
            ->emailVerification()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => '#03cffc',
            ])->sidebarCollapsibleOnDesktop()->databaseNotifications()
            ->databaseNotificationsPolling('30s')->profile(EditProfile::class)->profile(isSimple: false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
//            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                MemberChart::class,
                SmsChart::class
            ])->plugins([
//                FilamentAccessControlPlugin::make(),
                FilamentBackgroundsPlugin::make() ->showAttribution(false)->remember(100),
                FilamentLaravelLogPlugin::make()
                    ->navigationGroup('System Tools')
                    ->navigationLabel('Logs')
                    ->navigationIcon('heroicon-o-bug-ant')
                    ->navigationSort(1)
                    ->slug('logs'),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()

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
            ]);
    }
}
