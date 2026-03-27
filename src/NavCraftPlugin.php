<?php

declare(strict_types=1);

namespace Crumbls\NavCraft;

use Crumbls\NavCraft\Resources\MenuResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class NavCraftPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'navcraft';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            MenuResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
