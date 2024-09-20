<?php

namespace Modules\Filament\Providers;

use use App\Enums\LanguageEnum;
use Awcodes\FilamentStickyHeader\StickyHeaderPlugin;
use Awcodes\FilamentVersions\VersionsPlugin;
use Awcodes\FilamentVersions\VersionsWidget;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\Overlook\OverlookPlugin;
use Awcodes\Overlook\Widgets\OverlookWidget;
use BetterFuturesStudio\FilamentLocalLogins\LocalLogins;
use BezhanSalleh\FilamentExceptions\FilamentExceptionsPlugin;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Brickx\MaintenanceSwitch\MaintenanceSwitchPlugin;
use ChrisReedIO\Socialment\SocialmentPlugin;
use Croustibat\FilamentJobsMonitor\FilamentJobsMonitorPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use HusamTariq\FilamentDatabaseSchedule\FilamentDatabaseSchedulePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use pxlrbt\FilamentEnvironmentIndicator\EnvironmentIndicatorPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use SolutionForest\FilamentFirewall\FilamentFirewallPanel;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->spa()
            ->id('super-admin')
            ->path('super-admin')
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+i', 'ctrl+i'])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors($this->getColors())
            ->discoverResources(in: app_path('Filament/SuperAdmin/Resources'), for: 'App\\Filament\\SuperAdmin\\Resources')
            ->discoverPages(in: app_path('Filament/SuperAdmin/Pages'), for: 'App\\Filament\\SuperAdmin\\Pages')
            ->pages($this->getPages())
            ->discoverWidgets(in: app_path('Filament/SuperAdmin/Widgets'), for: 'App\\Filament\\SuperAdmin\\Widgets')
            ->widgets($this->getWidgets())
            ->middleware($this->getMiddlewares())
            ->authMiddleware($this->getAuthMiddlewares())
            ->navigationItems($this->getNavItems())
            ->plugins($this->getPlugins());
    }

    /**
     * @return array
     */
    private function getColors(): array
    {
        return [
            'primary' => Color::Amber,
        ];
    }

    /**
     * @return class-string[]
     */
    private function getPages(): array
    {
        return [
            Pages\Dashboard::class,
        ];
    }

    /**
     * @return string[]
     */
    private function getWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
            VersionsWidget::class,
            OverlookWidget::class,
        ];
    }

    /**
     * @return string[]
     */
    private function getMiddlewares(): array
    {
        return [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
            SetTheme::class,
        ];
    }

    /**
     * @return class-string[]
     */
    private function getAuthMiddlewares(): array
    {
        return [
            Authenticate::class,
        ];
    }

    private function getNavItems(): array
    {
        return [
            NavigationItem::make()
                ->label(fn(): string => __('Api Routes'))
                ->url('/api/' . config('l5-swagger.defaults.routes.docs'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-presentation-chart-line')
                ->group('Tools')
                ->sort(0),
            NavigationItem::make()
                ->label(fn(): string => __('Telescope'))
                ->url('/' . config('telescope.path'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-magnifying-glass-circle')
                ->group('Tools')
                ->sort(2),
            NavigationItem::make()
                ->label(fn(): string => __('Log viewer'))
                ->url('/' . config('log-viewer.route_path'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-exclamation-triangle')
                ->group('Tools')
                ->sort(1),
            NavigationItem::make()
                ->label(fn(): string => __('Pulse'))
                ->url('/' . config('pulse.path'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-arrow-trending-up')
                ->group('Tools')
                ->sort(3),
            NavigationItem::make()
                ->label(fn(): string => __('Horizon'))
                ->url('/' . config('horizon.path'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-queue-list')
                ->group('Tools')
                ->sort(4),
        ];
    }

    private function getPlugins(): array
    {
        return [
//            ResourceLockPlugin::make(),
            new LocalLogins,
//            FilamentSpatieRolesPermissionsPlugin::make(),
            SocialmentPlugin::make(),
            FilamentFirewallPanel::make(),
            LightSwitchPlugin::make(),
            FilamentProgressbarPlugin::make()
                ->color('#29b'),
//            SpatieLaravelTranslatablePlugin::make()
//                ->defaultLocales(LanguageEnum::getDefaultLanguages()),
            SpotlightPlugin::make(),
            ThemesPlugin::make(),
//            TranslationManagerPlugin::make(),
//            new Lockscreen,
            StickyHeaderPlugin::make()
                ->floating()
                ->colored(),
            VersionsPlugin::make()
                ->widgetColumnSpan('full')
                ->widgetSort(2),
            EnvironmentIndicatorPlugin::make(),
            FilamentSpatieLaravelBackupPlugin::make(),
            FilamentSpatieLaravelHealthPlugin::make(),
//            FilamentPeekPlugin::make(),
            FilamentBackgroundsPlugin::make(),
            FilamentExceptionsPlugin::make(),
            FilamentShieldPlugin::make(),
            MaintenanceSwitchPlugin::make(),
            FilamentApexChartsPlugin::make(),
            OverlookPlugin::make()
                ->sort(2)
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 3,
                    'lg' => 4,
                    'xl' => 5,
                    '2xl' => null,
                ]),
            FilamentDatabaseSchedulePlugin::make(),
            FilamentJobsMonitorPlugin::make()
                ->label('Job')
                ->pluralLabel('Jobs')
                ->enableNavigation()
                ->navigationIcon('heroicon-o-cpu-chip')
                ->navigationGroup('Settings')
                ->navigationSort(5)
                ->navigationCountBadge()
                ->enablePruning()
                ->pruningRetention(7),
//            CuratorPlugin::make()
//                ->label('Media')
//                ->pluralLabel('Media')
//                ->navigationIcon('heroicon-o-photo')
//                ->navigationGroup('Media')
//                ->navigationSort(3)
//                ->navigationCountBadge(),
//            FilamentFullCalendarPlugin::make()
//                ->selectable()
//                ->editable()
//                ->timezone('Asia/Tehran')
//                ->locale(LanguageEnum::farsi->shortName()),
//            FilamentChainedTranslationManagerPlugin::make(),
//            TableLayoutTogglePlugin::make()
//                ->shareLayoutBetweenPages(false) // allow all tables to share the layout option (requires persistLayoutInLocalStorage to be true)
//                ->displayToggleAction() // used to display the toggle button automatically, on the desired filament hook (defaults to table bar)
//                ->listLayoutButtonIcon()
//                ->gridLayoutButtonIcon(),
//            SimpleLightBoxPlugin::make(),
        ];
    }

    public function boot(): void
    {
        $this->getConfigurations();
    }

    private function getConfigurations(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(LanguageEnum::getDefaultLanguages());
        });
        PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch) {
            $panelSwitch
                ->modalHeading('Available Panels')
                ->modalWidth('sm')
                ->simple()
                ->icons([
                    'admin' => 'heroicon-o-square-2-stack',
                    'super-admin' => 'heroicon-o-star',
                    'user' => 'heroicon-o-star',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Admin Panel',
                    'super-admin' => 'Super Admin Panel',
                    'user' => 'User Panel',
                ]);

        });
    }
}
