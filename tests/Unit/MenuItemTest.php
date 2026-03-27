<?php

declare(strict_types=1);

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;

it('creates a menu item with all fields', function () {
    $menu = Menu::factory()->create();

    $item = MenuItem::create([
        'menu_id' => $menu->id,
        'type' => 'link',
        'label' => 'About Us',
        'url' => '/about',
        'route' => null,
        'target' => '_self',
        'order' => 0,
        'css_class' => 'nav-about',
        'icon' => 'heroicon-o-information-circle',
        'content' => null,
        'settings' => ['visible' => true],
    ]);

    expect($item)->toBeInstanceOf(MenuItem::class)
        ->and($item->label)->toBe('About Us')
        ->and($item->url)->toBe('/about')
        ->and($item->target)->toBe('_self')
        ->and($item->css_class)->toBe('nav-about')
        ->and($item->settings)->toBe(['visible' => true]);
});

it('belongs to a menu', function () {
    $item = MenuItem::factory()->create();

    expect($item->menu)->toBeInstanceOf(Menu::class);
});

it('supports parent-child nesting', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Parent',
    ]);

    $child = MenuItem::factory()->childOf($parent)->create([
        'label' => 'Child',
    ]);

    expect($parent->children)->toHaveCount(1)
        ->and($parent->children->first()->label)->toBe('Child')
        ->and($child->parent->label)->toBe('Parent');
});

it('casts content to array', function () {
    $item = MenuItem::factory()->withContent(['rows' => []])->create();

    $fresh = MenuItem::find($item->id);

    expect($fresh->content)->toBeArray()
        ->and($fresh->content)->toBe(['rows' => []]);
});

it('casts settings to array', function () {
    $item = MenuItem::factory()->create(['settings' => ['key' => 'value']]);

    $fresh = MenuItem::find($item->id);

    expect($fresh->settings)->toBeArray();
});

it('orders children by order column', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create(['menu_id' => $menu->id]);

    MenuItem::factory()->childOf($parent)->create(['label' => 'Second', 'order' => 2]);
    MenuItem::factory()->childOf($parent)->create(['label' => 'First', 'order' => 1]);
    MenuItem::factory()->childOf($parent)->create(['label' => 'Third', 'order' => 3]);

    $children = $parent->children()->get();

    expect($children->pluck('label')->all())->toBe(['First', 'Second', 'Third']);
});

it('soft deletes a menu item', function () {
    $item = MenuItem::factory()->create();

    $item->delete();

    expect(MenuItem::count())->toBe(0)
        ->and(MenuItem::withTrashed()->count())->toBe(1);
});

it('creates megamenu type via factory', function () {
    $item = MenuItem::factory()->megamenu()->create();

    expect($item->type)->toBe('megamenu')
        ->and($item->content)->toBe(['rows' => []]);
});

it('creates divider type via factory', function () {
    $item = MenuItem::factory()->divider()->create();

    expect($item->type)->toBe('divider');
});

it('cascades delete from menu to items', function () {
    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON');

    $menu = Menu::factory()->withItems(3)->create();

    expect(MenuItem::count())->toBe(3);

    $menu->forceDelete();

    expect(MenuItem::count())->toBe(0);
});
