<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Http\Middleware\SetFilamentLocale;
use Filament\Forms\Components\Field;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Filament v3 的 PanelProvider 未声明 boot()，勿调用 parent::boot()。

        Filament::serving(function (): void {
            Field::configureUsing(function (Field $field): void {
                $n = $field->getName();
                $zh = config("filament_attribute_labels.{$n}");
                if (is_string($zh) && $zh !== '') {
                    $field->label($zh);
                }
            });

            TextColumn::configureUsing(function (TextColumn $column): void {
                $n = $column->getName();
                $zh = config("filament_attribute_labels.{$n}");
                if (is_string($zh) && $zh !== '') {
                    $column->label($zh);
                }
            });

            IconColumn::configureUsing(function (IconColumn $column): void {
                $n = $column->getName();
                $zh = config("filament_attribute_labels.{$n}");
                if (is_string($zh) && $zh !== '') {
                    $column->label($zh);
                }
            });
        });
    }

    public function panel(Panel $panel): Panel
    {
        $adminDomain = config('admin.domain');
        $frontDomain = config('app.front_domain');
        $prefix = trim((string) config('admin.path_prefix', 'admin'), '/');
        $useSplitHosts = is_string($adminDomain) && $adminDomain !== ''
            && is_string($frontDomain) && $frontDomain !== '';

        $panel = $panel
            ->default()
            ->id('admin')
            ->login()
            ->brandName(config('app.name').' 后台')
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetFilamentLocale::class,
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

        if ($useSplitHosts) {
            return $panel->domain($adminDomain)->path('');
        }

        return $panel->path($prefix !== '' ? $prefix : 'admin');
    }
}
