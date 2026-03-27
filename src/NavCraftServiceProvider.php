<?php

declare(strict_types=1);

namespace Crumbls\NavCraft;

use Crumbls\NavCraft\Support\MenuRenderer;
use Crumbls\NavCraft\View\Components\BreadcrumbComponent;
use Crumbls\NavCraft\View\Components\MenuComponent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class NavCraftServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/navcraft.php', 'navcraft');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'navcraft');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Blade::component('navcraft-menu', MenuComponent::class);
        Blade::component('navcraft-breadcrumb', BreadcrumbComponent::class);

        Blade::directive('navcraft', fn (string $expression): string => "<?php echo \\Crumbls\\NavCraft\\Support\\MenuRenderer::fromSlug({$expression})->toHtml(); ?>");

        Blade::directive('navCraftScripts', fn (): string => "<?php echo view('navcraft::components.scripts')->render(); ?>");

        $this->publishes([
            __DIR__ . '/../config/navcraft.php' => config_path('navcraft.php'),
        ], 'navcraft-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/navcraft'),
        ], 'navcraft-views');
    }
}
