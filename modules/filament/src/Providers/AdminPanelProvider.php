<?php

namespace Modules\Filament\Providers;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Enums\LanguageEnum;
use Awcodes\Curator\CuratorPlugin;
use Awcodes\FilamentStickyHeader\StickyHeaderPlugin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Hydrat\TableLayoutToggle\TableLayoutTogglePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Kenepa\ResourceLock\ResourceLockPlugin;
use Kenepa\TranslationManager\TranslationManagerPlugin;
use lockscreen\FilamentLockscreen\Lockscreen;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Pboivin\FilamentPeek\FilamentPeekPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;
use Statikbe\FilamentTranslationManager\FilamentChainedTranslationManagerPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->spa()
//            ->default()
            ->login()
            ->id('admin')
            ->path('admin')
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+i', 'ctrl+i'])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors($this->getColors())
            ->discoverResources(in: app_path('Filament/Resources/Admin'), for: 'App\\Filament\\Resources\\Admin')
            ->discoverPages(in: app_path('Filament/Pages/Admin'), for: 'App\\Filament\\Pages\\Admin')
            ->pages($this->getPages())
            ->discoverWidgets(in: app_path('Filament/Widgets/Admin'), for: 'App\\Filament\\Widgets\\Admin')
            ->widgets($this->getWidgets())
            ->middleware($this->getMiddlewares())
            ->authMiddleware($this->getAuthMiddlewares())
            ->plugins($this->getPlugins());
    }

    public function boot(): void
    {
        $this->getConfigurations();
    }

    private function getPlugins(): array
    {
        return [
//            ResourceLockPlugin::make(),
//            new LocalLogins,
//            FilamentSpatieRolesPermissionsPlugin::make(),
//            SocialmentPlugin::make(),
//            FilamentFirewallPanel::make(),
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
//            VersionsPlugin::make()
//                ->widgetColumnSpan('full')
//                ->widgetSort(2),
//            EnvironmentIndicatorPlugin::make(),
//            FilamentSpatieLaravelBackupPlugin::make(),
//            FilamentSpatieLaravelHealthPlugin::make(),
//            FilamentPeekPlugin::make(),
            FilamentBackgroundsPlugin::make(),
//            FilamentExceptionsPlugin::make(),
            FilamentShieldPlugin::make(),
//            MaintenanceSwitchPlugin::make(),
//            FilamentApexChartsPlugin::make(),
//            OverlookPlugin::make()
//                ->sort(2)
//                ->columns([
//                    'default' => 1,
//                    'sm' => 2,
//                    'md' => 3,
//                    'lg' => 4,
//                    'xl' => 5,
//                    '2xl' => null,
//                ]),
//            FilamentDatabaseSchedulePlugin::make(),
//            FilamentJobsMonitorPlugin::make()
//                ->label('Job')
//                ->pluralLabel('Jobs')
//                ->enableNavigation()
//                ->navigationIcon('heroicon-o-cpu-chip')
//                ->navigationGroup('Settings')
//                ->navigationSort(5)
//                ->navigationCountBadge()
//                ->enablePruning()
//                ->pruningRetention(7),
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
            SimpleLightBoxPlugin::make(),
            GlobalSearchModalPlugin::make(),
        ];
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
                    'super-admin' => 'heroicon-o-square-2-stack',
                    'users' => 'heroicon-o-star',
                ])
                ->iconSize(16)
                ->labels([
                    'admin' => 'Admin Panel',
                    'super-admin' => 'Super Admin Panel',
                    'user' => 'User Panel',
                ]);

        });
    }

    /**
     * @return string[]
     */
    private function getWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
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
    public function getPages(): array
    {
        return [
            Pages\Dashboard::class,
        ];
    }

    /**
     * @return class-string[]
     */
    public function getAuthMiddlewares(): array
    {
        return [
            Authenticate::class,
        ];
    }

    /**
     * @return array
     */
    public function getColors(): array
    {
        return [
            'primary' => Color::Amber,
        ];
    }
}
