<?php

declare(strict_types=1);

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;

it('returns url for url type items', function () {
    $item = MenuItem::factory()->create([
        'type' => 'url',
        'url' => '/about',
    ]);

    expect($item->getUrl())->toBe('/about');
});

it('returns absolute url for url type items', function () {
    $item = MenuItem::factory()->create([
        'type' => 'url',
        'url' => 'https://example.com/page',
    ]);

    expect($item->getUrl())->toBe('https://example.com/page');
});

it('returns hash when url is null', function () {
    $item = MenuItem::factory()->create([
        'type' => 'url',
        'url' => null,
    ]);

    expect($item->getUrl())->toBe('#');
});

it('returns hash for route type when route does not exist', function () {
    $item = MenuItem::factory()->create([
        'type' => 'route',
        'route' => 'nonexistent.route',
        'url' => null,
    ]);

    expect($item->getUrl())->toBe('#');
});

it('returns hash for mega menu type', function () {
    $item = MenuItem::factory()->megamenu()->create();

    expect($item->getUrl())->toBe('#');
});

it('returns allDescendants recursively', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Parent',
    ]);

    $child = MenuItem::factory()->childOf($parent)->create([
        'label' => 'Child',
    ]);

    $grandchild = MenuItem::factory()->childOf($child)->create([
        'label' => 'Grandchild',
    ]);

    $loaded = MenuItem::where('id', $parent->id)
        ->with('allDescendants')
        ->first();

    expect($loaded->allDescendants)->toHaveCount(1)
        ->and($loaded->allDescendants->first()->label)->toBe('Child')
        ->and($loaded->allDescendants->first()->allDescendants)->toHaveCount(1)
        ->and($loaded->allDescendants->first()->allDescendants->first()->label)->toBe('Grandchild');
});
