<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\View\Components;

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BreadcrumbComponent extends Component
{
    public array $breadcrumbs = [];

    public function __construct(
        public string $slug,
        public ?string $url = null,
    ) {
        $currentUrl = $this->url ?? request()->url();

        $menu = Menu::where('slug', $slug)
            ->published()
            ->first();

        if (! $menu) {
            return;
        }

        $items = $menu->allItems()
            ->with('parent')
            ->orderBy('order')
            ->get();

        $match = $items->first(fn (MenuItem $item): bool => $item->getUrl() === $currentUrl);

        if (! $match) {
            return;
        }

        $this->breadcrumbs = $match->getAncestorTrail();
    }

    public function render(): View
    {
        return view('navcraft::components.breadcrumb');
    }
}
