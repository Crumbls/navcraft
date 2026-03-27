<?php

declare(strict_types=1);

use Crumbls\NavCraft\Forms\Components\TreeRelationField;
use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;

it('deep clones an item with no children', function () {
    $menu = Menu::factory()->create();

    $original = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'type' => 'url',
        'url' => '/about',
        'order' => 0,
    ]);

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $clone = (new ReflectionMethod($field, 'deepCloneItem'))
        ->invoke($field, $original, $original->parent_id);

    expect($clone->id)->not->toBe($original->id)
        ->and($clone->label)->toBe('About (copy)')
        ->and($clone->url)->toBe('/about')
        ->and($clone->type)->toBe('url')
        ->and($clone->menu_id)->toBe($menu->id);
});

it('deep clones an item with nested children', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Services',
        'type' => 'url',
        'order' => 0,
    ]);

    $child = MenuItem::factory()->childOf($parent)->create([
        'label' => 'Consulting',
        'type' => 'url',
        'order' => 0,
    ]);

    $grandchild = MenuItem::factory()->childOf($child)->create([
        'label' => 'Strategy',
        'type' => 'route',
        'order' => 0,
    ]);

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $clone = (new ReflectionMethod($field, 'deepCloneItem'))
        ->invoke($field, $parent, $parent->parent_id);

    $clonedChildren = MenuItem::where('parent_id', $clone->id)->get();
    expect($clonedChildren)->toHaveCount(1)
        ->and($clonedChildren->first()->label)->toBe('Consulting (copy)');

    $clonedGrandchildren = MenuItem::where('parent_id', $clonedChildren->first()->id)->get();
    expect($clonedGrandchildren)->toHaveCount(1)
        ->and($clonedGrandchildren->first()->label)->toBe('Strategy (copy)');

    expect(MenuItem::count())->toBe(6);
});

it('preserves content and settings during clone', function () {
    $menu = Menu::factory()->create();

    $original = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Mega',
        'type' => 'mega',
        'content' => ['rows' => [['id' => 'r1']]],
        'settings' => ['route_params' => ['id' => '5']],
        'css_class' => 'special',
        'icon' => 'heroicon-o-star',
        'order' => 0,
    ]);

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $clone = (new ReflectionMethod($field, 'deepCloneItem'))
        ->invoke($field, $original, $original->parent_id);

    $clone->refresh();

    expect($clone->content)->toBe(['rows' => [['id' => 'r1']]])
        ->and($clone->settings)->toBe(['route_params' => ['id' => '5']])
        ->and($clone->css_class)->toBe('special')
        ->and($clone->icon)->toBe('heroicon-o-star');
});
