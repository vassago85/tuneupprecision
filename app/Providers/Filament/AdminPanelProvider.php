<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->brandName('Tune Up')
            ->favicon(asset('favicon.svg'))
            ->colors([
                // Precision Copper reserved for primary actions & high-value emphasis.
                'primary' => Color::hex('#D45B2E'),
                // True neutral ramp — kills the pervasive pale-blue tint the old
                // charcoal (slate-navy) gray caused across surfaces.
                'gray' => Color::Zinc,
                'info' => Color::hex('#2A78D6'),
                'success' => Color::hex('#1BAF7A'),
                'warning' => Color::hex('#B5790B'),
                'danger' => Color::hex('#C9433F'),
            ])
            // Compact SaaS shell: narrower sidebar, capped content width.
            ->sidebarWidth('14rem')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::SevenExtraLarge)
            ->navigationGroups([
                'Training',
                'Commerce',
                'System',
            ])
            // Inject the design-system stylesheet (semantic tokens + component
            // overrides). Inlined so it always loads with no Vite/asset-URL step.
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => file_exists($path = public_path('css/admin-theme.css'))
                    ? '<style>'.file_get_contents($path).'</style>'
                    : '',
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\KpiStatsWidget::class,
                \App\Filament\Widgets\UpcomingTrainingWidget::class,
                \App\Filament\Widgets\PaymentsAttentionWidget::class,
                \App\Filament\Widgets\LowStockProductsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
