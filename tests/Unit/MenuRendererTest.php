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
        ->and($html)->toContain('Home');
});

it('uses the disclosure pattern by default (no menubar roles)', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'disclosure']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'type' => 'url',
        'url' => '/about',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('disclosure')->toHtml();

    expect($html)->not->toContain('role="menubar"')
        ->and($html)->not->toContain('role="menuitem"')
        ->and($html)->toContain('href="/about"')
        ->and($html)->toContain('About');
});

it('renders menubar roles when nav_pattern is menubar', function () {
    $menu = Menu::factory()->published()->create([
        'slug' => 'menubar',
        'settings' => ['nav_pattern' => 'menubar'],
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'About',
        'type' => 'url',
        'url' => '/about',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('menubar')->toHtml();

    expect($html)->toContain('role="menubar"')
        ->and($html)->toContain('role="menuitem"')
        ->and($html)->toContain('href="/about"');
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

    expect($html)->toContain('role="navigation"');
});

it('renders a featured card on a mega item from settings', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'featured']);

    $section = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Visit',
        'type' => 'mega',
        'url' => '/visit',
        'order' => 0,
        'settings' => ['featured' => [
            'title' => 'Free admission, always',
            'body' => 'Step into the story.',
            'cta_label' => 'Plan a visit',
            'cta_url' => '/plan',
        ]],
    ]);

    MenuItem::factory()->childOf($section)->create([
        'label' => 'Hours',
        'type' => 'url',
        'url' => '/hours',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('featured')->toHtml();

    expect($html)->toContain('Free admission, always')
        ->and($html)->toContain('Plan a visit')
        ->and($html)->toContain('href="/plan"');
});

it('renders a mega panel link list from content.links', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'links-panel']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Visit',
        'type' => 'mega',
        'url' => '/visit',
        'order' => 0,
        'content' => ['links' => [
            ['label' => 'Hours & Admission', 'url' => '/visit/hours'],
            ['label' => 'Directions', 'url' => '/visit/directions'],
        ]],
    ]);

    $html = MenuRenderer::fromSlug('links-panel')->toHtml();

    expect($html)->toContain('Hours &amp; Admission')
        ->and($html)->toContain('href="/visit/hours"')
        ->and($html)->toContain('href="/visit/directions"');
});

it('drives colors through CSS variables', function () {
    $menu = Menu::factory()->published()->create(['slug' => 'themed']);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'label' => 'Home',
        'type' => 'url',
        'url' => '/',
        'order' => 0,
    ]);

    $html = MenuRenderer::fromSlug('themed')->toHtml();

    expect($html)->toContain('var(--nc-fg)')
        ->and($html)->toContain('bg-[var(--nc-bg)]');
});
