<?php

namespace Modules\Filament\Providers;

use App\Enums\LanguageEnum;
use Awcodes\FilamentStickyHeader\StickyHeaderPlugin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Hasnayeen\Themes\Http\Middleware\SetTheme;
use Hasnayeen\Themes\ThemesPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->spa()
            ->id('user')
            ->path('user')
            ->colors($this->getColors())
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+i', 'ctrl+i'])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources/User'), for: 'App\\Filament\\Resources\\User')
            ->discoverPages(in: app_path('Filament/Pages/User'), for: 'App\\Filament\\Pages\\User')
            ->pages($this->getPages())
            ->discoverWidgets(in: app_path('Filament/Widgets/User'), for: 'App\\Filament\\Widgets\\User')
            ->widgets($this->getWidgets())
            ->middleware($this->getMiddlewares())
            ->authMiddleware($this->getAuthMiddlewares())
            ->plugins($this->getPlugins());
    }

    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(LanguageEnum::getDefaultLanguages());
        });
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

    private function getPlugins(): array
    {
        return [
            GlobalSearchModalPlugin::make(),
            LightSwitchPlugin::make(),
            FilamentProgressbarPlugin::make()
                ->color('#29b'),
            ThemesPlugin::make(),
            StickyHeaderPlugin::make()
                ->floating()
                ->colored(),
            FilamentBackgroundsPlugin::make(),
        ];
    }
}
