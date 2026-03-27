<?php

declare(strict_types=1);

namespace Crumbls\NavCraft\Support;

use Crumbls\NavCraft\Models\Menu;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;

class MenuRenderer implements Htmlable
{
    public function __construct(
        protected Menu $menu,
        protected bool $cached = true,
    ) {}

    public static function fromSlug(string $slug, bool $cached = true): static
    {
        $menu = Menu::where('slug', $slug)
            ->published()
            ->firstOrFail();

        return new static($menu, $cached);
    }

    public function toHtml(): string
    {
        if (! $this->cached) {
            return $this->render();
        }

        $cacheKey = "navcraft:menu:{$this->menu->slug}:{$this->menu->updated_at?->timestamp}";

        return Cache::remember($cacheKey, now()->addHours(1), fn (): string => $this->render());
    }

    protected function render(): string
    {
        $items = $this->menu
            ->items()
            ->with('allDescendants')
            ->get();

        return view('navcraft::components.nav', [
            'menu' => $this->menu,
            'items' => $items,
        ])->render();
    }

    public static function clearCache(string $slug): void
    {
        $pattern = "navcraft:menu:{$slug}:";

        Cache::forget($pattern);
    }
}
