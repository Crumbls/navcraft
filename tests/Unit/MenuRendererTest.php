<?php

declare(strict_types=1);

use Crumbls\NavCraft\Models\Menu;
use Crumbls\NavCraft\Models\MenuItem;
use Crumbls\NavCraft\Support\MenuRenderer;

it('creates a renderer from a published menu slug', function () {
    Menu::factory()->published()->create(['slug' => 'main']);

    $renderer = MenuRenderer::fromSlug('main');

    expect($renderer)->toBeInstanceOf(MenuRenderer::class);
});

it('throws when slug does not exist', function () {
    MenuRenderer::fromSlug('nonexistent');
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('throws when menu is not published', function () {
    Menu::factory()->draft()->create(['slug' => 'draft-menu']);

    MenuRenderer::fromSlug('draft-menu');
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

it('renders nav html with aria-label', function () {
    $menu = Menu::factory()->published()->create([
        'slug' => 'test-nav',
        'name' => 'Test Navigation',
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Home',
        'type' => 'url',
        'url' => '/',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('test-nav')->toHtml();

    expect($html)->toContain('aria-label="Test Navigation"')
        ->and($html)->toContain('role="navigation"')
        ->and($html)->toContain('role="menubar"')
        ->and($html)->toContain('Home');
});

it('renders links with role menuitem', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'links']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'type' => 'url',
        'url' => '/about',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('links')->toHtml();

    expect($html)->toContain('role="menuitem"')
        ->and($html)->toContain('href="/about"')
        ->and($html)->toContain('About');
});

it('renders nested items with aria-haspopup', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'nested']);

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Services',
        'type' => 'url',
        'url' => '/services',
        'order' => 0,
    ]);

    MenuItem::factory()->childOf($parent)->create([
        'label' => 'Consulting',
        'type' => 'url',
        'url' => '/consulting',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('nested')->toHtml();

    expect($html)->toContain('aria-haspopup="true"')
        ->and($html)->toContain('Services')
        ->and($html)->toContain('Consulting');
});

it('renders external links with target blank and sr-only text', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'external']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'GitHub',
        'type' => 'url',
        'url' => 'https://github.com',
        'target' => '_blank',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('external')->toHtml();

    expect($html)->toContain('target="_blank"')
        ->and($html)->toContain('rel="noopener noreferrer"')
        ->and($html)->toContain('(opens in new window)');
});

it('renders empty menu without errors', function () {
    Menu::factory()->published()->create(['slug' => 'empty']);

    $html = MenuRenderer::fromSlug('empty')->toHtml();

    expect($html)->toContain('role="menubar"');
});
