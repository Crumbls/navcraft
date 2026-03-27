<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\View\Components;

use Crumbls\NavCraft\Models\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuComponent extends Component
{
    public Menu $menu;

    public function __construct(
        public string $slug,
        public ?string $label = null,
        public ?string $class = null,
        public ?string $breakpoint = null,
    ) {
        $this->menu = Menu::where('slug', $slug)
            ->published()
            ->firstOrFail();
    }

    public function render(): View
    {
        $settings = $this->menu->settings ?? [];
        $breakpoint = $this->breakpoint
            ?? $settings['breakpoint']
            ?? config('navcraft.frontend.breakpoint', 'lg');

        return view('navcraft::components.nav', [
            'menu' => $this->menu,
            'items' => $this->menu->items()->with('allDescendants')->get(),
            'ariaLabel' => $this->label ?? $this->menu->name,
            'navClass' => $this->class,
            'breakpoint' => $breakpoint,
        ]);
    }
}
