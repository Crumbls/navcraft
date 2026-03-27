<?php

declare(strict_types=1);

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;

it('creates a menu with all fields', function () {
    $menu = Menu::create([
        'name' => 'Main Navigation',
        'slug' => 'main-navigation',
        'description' => 'Primary site menu',
        'status' => 'published',
        'settings' => ['orientation' => 'horizontal'],
    ]);

    expect($menu)->toBeInstanceOf(Menu::class)
        ->and($menu->name)->toBe('Main Navigation')
        ->and($menu->slug)->toBe('main-navigation')
        ->and($menu->description)->toBe('Primary site menu')
        ->and($menu->status)->toBe('published')
        ->and($menu->settings)->toBe(['orientation' => 'horizontal']);
});

it('casts settings to array', function () {
    $menu = Menu::factory()->create(['settings' => ['key' => 'value']]);

    $fresh = Menu::find($menu->id);

    expect($fresh->settings)->toBeArray()
        ->and($fresh->settings['key'])->toBe('value');
});

it('scopes published menus', function () {
    Menu::factory()->published()->create();
    Menu::factory()->draft()->create();

    expect(Menu::published()->count())->toBe(1);
});

it('scopes draft menus', function () {
    Menu::factory()->published()->create();
    Menu::factory()->draft()->create();

    expect(Menu::draft()->count())->toBe(1);
});

it('has items relationship returning top-level items only', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'order' => 0,
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'order' => 0,
    ]);

    expect($menu->items)->toHaveCount(1)
        ->and($menu->allItems)->toHaveCount(2);
});

it('enforces unique slugs', function () {
    Menu::factory()->create(['slug' => 'main']);

    Menu::factory()->create(['slug' => 'main']);
})->throws(\Illuminate\Database\QueryException::class);

it('soft deletes a menu', function () {
    $menu = Menu::factory()->create();

    $menu->delete();

    expect(Menu::count())->toBe(0)
        ->and(Menu::withTrashed()->count())->toBe(1);
});

it('creates a menu via factory', function () {
    $menu = Menu::factory()->create();

    expect($menu->name)->toBeString()
        ->and($menu->slug)->toBeString()
        ->and($menu->status)->toBeIn(['draft', 'published']);
});

it('creates a menu with items via factory', function () {
    $menu = Menu::factory()->withItems(3)->create();

    expect($menu->items)->toHaveCount(3);
});
