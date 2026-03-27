<?php

declare(strict_types=1);

use Crumbls\NavCraft\Forms\Components\TreeRelationField;
use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;

function buildTreeViaReflection(array $items, ?int $parentId = null): array
{
    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $collection = MenuItem::hydrate(
        collect($items)->map(fn ($i) => (array) $i)->all()
    );

    return (new ReflectionMethod($field, 'buildTree'))
        ->invoke($field, $collection, $parentId);
}

it('builds a flat tree from menu items', function () {
    $menu = Menu::factory()->create();

    $a = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Home',
        'type' => 'url',
        'url' => '/',
        'order' => 0,
    ]);

    $b = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'type' => 'url',
        'url' => '/about',
        'order' => 1,
    ]);

    $items = $menu->allItems()->orderBy('order')->get();

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $tree = (new ReflectionMethod($field, 'buildTree'))
        ->invoke($field, $items);

    expect($tree)->toHaveCount(2)
        ->and($tree[0]['label'])->toBe('Home')
        ->and($tree[1]['label'])->toBe('About')
        ->and($tree[0]['children'])->toBeEmpty()
        ->and($tree[1]['children'])->toBeEmpty();
});

it('builds a nested tree from parent-child items', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Services',
        'type' => 'url',
        'order' => 0,
    ]);

    MenuItem::factory()->childOf($parent)->create([
        'label' => 'Consulting',
        'type' => 'url',
        'order' => 0,
    ]);

    MenuItem::factory()->childOf($parent)->create([
        'label' => 'Development',
        'type' => 'route',
        'order' => 1,
    ]);

    $items = $menu->allItems()->orderBy('order')->get();

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $tree = (new ReflectionMethod($field, 'buildTree'))
        ->invoke($field, $items);

    expect($tree)->toHaveCount(1)
        ->and($tree[0]['label'])->toBe('Services')
        ->and($tree[0]['children'])->toHaveCount(2)
        ->and($tree[0]['children'][0]['label'])->toBe('Consulting')
        ->and($tree[0]['children'][1]['label'])->toBe('Development');
});

it('includes all item fields in tree output', function () {
    $menu = Menu::factory()->create();

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Test',
        'type' => 'route',
        'url' => null,
        'route' => 'dashboard',
        'target' => '_blank',
        'css_class' => 'text-bold',
        'icon' => 'heroicon-o-home',
        'content' => null,
        'settings' => ['route_params' => ['id' => '1']],
        'order' => 0,
    ]);

    $items = $menu->allItems()->orderBy('order')->get();

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $tree = (new ReflectionMethod($field, 'buildTree'))
        ->invoke($field, $items);

    expect($tree[0])
        ->toHaveKey('id')
        ->toHaveKey('label', 'Test')
        ->toHaveKey('type', 'route')
        ->toHaveKey('route_name', 'dashboard')
        ->toHaveKey('target', '_blank')
        ->toHaveKey('css_class', 'text-bold')
        ->toHaveKey('icon', 'heroicon-o-home')
        ->toHaveKey('route_params', ['id' => '1'])
        ->toHaveKey('children');
});

it('builds empty tree when no items', function () {
    $menu = Menu::factory()->create();

    $items = $menu->allItems()->orderBy('order')->get();

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $tree = (new ReflectionMethod($field, 'buildTree'))
        ->invoke($field, $items);

    expect($tree)->toBeEmpty();
});

it('persists tree order and parent relationships', function () {
    $menu = Menu::factory()->create();

    $a = MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'A', 'order' => 0]);
    $b = MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'B', 'order' => 1]);
    $c = MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'C', 'order' => 2]);

    $field = (new ReflectionClass(TreeRelationField::class))
        ->newInstanceWithoutConstructor();

    $order = 0;
    $items = [
        ['id' => $b->id, 'children' => [
            ['id' => $a->id, 'children' => []],
        ]],
        ['id' => $c->id, 'children' => []],
    ];
    $parentId = null;

    (new ReflectionMethod($field, 'persistTree'))
        ->invokeArgs($field, [$items, $parentId, &$order]);

    $b->refresh();
    $a->refresh();
    $c->refresh();

    expect($b->order)->toBe(0)
        ->and($b->parent_id)->toBeNull()
        ->and($a->order)->toBe(1)
        ->and($a->parent_id)->toBe($b->id)
        ->and($c->order)->toBe(2)
        ->and($c->parent_id)->toBeNull();
});

it('returns named routes excluding framework routes', function () {
    $routes = TreeRelationField::getNamedRoutes();

    foreach (array_keys($routes) as $name) {
        expect($name)->not->toStartWith('filament.')
            ->and($name)->not->toStartWith('livewire.')
            ->and($name)->not->toStartWith('ignition.');
    }
});

it('returns empty array for route parameters of non-existent route', function () {
    $params = TreeRelationField::getRouteParameters('totally.fake.route');

    expect($params)->toBeEmpty();
});

it('returns empty array for null route name', function () {
    $params = TreeRelationField::getRouteParameters(null);

    expect($params)->toBeEmpty();
});
