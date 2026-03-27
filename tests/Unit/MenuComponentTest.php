<?php

declare(strict_types=1);

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Crumbls\NavCraft\View\Components\MenuComponent;

it('creates a component from a published menu slug', function () {
    Menu::factory()->published()->create(['slug' => 'footer']);

    $component = new MenuComponent(slug: 'footer');

    expect($component->menu->slug)->toBe('footer');
});

it('throws when slug is not published', function () {
    Menu::factory()->draft()->create(['slug' => 'hidden']);

    new MenuComponent(slug: 'hidden');
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('uses custom aria label when provided', function () {
    Menu::factory()->published()->create(['slug' => 'main', 'name' => 'Main Nav']);

    $component = new MenuComponent(slug: 'main', label: 'Site Navigation');
    $view = $component->render();
    $html = $view->render();

    expect($html)->toContain('aria-label="Site Navigation"');
});

it('falls back to menu name for aria label', function () {
    Menu::factory()->published()->create(['slug' => 'primary', 'name' => 'Primary Menu']);

    $component = new MenuComponent(slug: 'primary');
    $view = $component->render();
    $html = $view->render();

    expect($html)->toContain('aria-label="Primary Menu"');
});

it('renders items from the menu', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'with-items']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Products',
        'type' => 'url',
        'url' => '/products',
        'order' => 0,
    ]);

    $component = new MenuComponent(slug: 'with-items');
    $html = $component->render()->render();

    expect($html)->toContain('Products');
});
