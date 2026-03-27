# NavCraft Customization Guide for AI Agents

This document is a complete reference for AI coding agents (Claude Code, Cursor, Copilot, etc.) to customize NavCraft menu rendering. Follow every rule precisely.

## Architecture Overview

NavCraft renders menus through a pipeline of Blade views. Every view is publishable and overridable. The rendering chain is:

```
Menu model -> MenuRenderer/MenuComponent -> nav.blade.php
  -> menu-item.blade.php (recursive, per item)
  -> mega-panel.blade.php (for mega menu items)
  -> menu-item-mobile.blade.php (recursive, mobile accordion)
  -> scripts.blade.php (Alpine.js component)
  -> breadcrumb.blade.php (optional breadcrumb component)
```

## File Locations

### Package views (defaults):
```
packages/navcraft/resources/views/components/
  nav.blade.php                 -- <nav> wrapper, desktop + mobile
  menu-item.blade.php           -- desktop item (recursive)
  menu-item-mobile.blade.php    -- mobile item (recursive)
  mega-panel.blade.php          -- mega menu content panel
  breadcrumb.blade.php          -- breadcrumb trail
  scripts.blade.php             -- Alpine.js navCraft component
```

### Published views (overrides):
```
resources/views/vendor/navcraft/components/
  nav.blade.php
  menu-item.blade.php
  menu-item-mobile.blade.php
  mega-panel.blade.php
  breadcrumb.blade.php
  scripts.blade.php
```

Publish with:
```bash
php artisan vendor:publish --tag=navcraft-views
```

## Quick Start: Custom Menu Rendering

### Step 1: Publish the views

```bash
php artisan vendor:publish --tag=navcraft-views
```

### Step 2: Edit the views

All views are in `resources/views/vendor/navcraft/components/`. Edit freely -- they are standard Blade templates using Tailwind CSS and Alpine.js.

### Step 3: Render the menu

```blade
{{-- In your layout --}}
@navCraftScripts
@navcraft('your-menu-slug')

{{-- Or as a component --}}
<x-navcraft-menu slug="your-menu-slug" label="Main Navigation" />

{{-- Breadcrumbs --}}
<x-navcraft-breadcrumb slug="your-menu-slug" />
```

## Available Variables in Each View

### nav.blade.php

| Variable | Type | Description |
|----------|------|-------------|
| `$menu` | `Menu` model | The menu being rendered |
| `$items` | `Collection<MenuItem>` | Top-level items with `allDescendants` eager-loaded |
| `$ariaLabel` | `string` | Label for the `<nav>` element |

### menu-item.blade.php / menu-item-mobile.blade.php

| Variable | Type | Description |
|----------|------|-------------|
| `$item` | `MenuItem` model | The current item |
| `$depth` | `int` | Nesting depth (0 = top level) |
| `$theme` | `string` | Theme preset name (desktop only) |

### mega-panel.blade.php

| Variable | Type | Description |
|----------|------|-------------|
| `$item` | `MenuItem` model | The mega menu item |
| `$panelId` | `string` | HTML id for the panel |
| `$parentId` | `string` | HTML id of the trigger button |

### breadcrumb.blade.php

| Variable | Type | Description |
|----------|------|-------------|
| `$breadcrumbs` | `array<MenuItem>` | Ordered ancestor trail to current page |

## MenuItem Model Methods

These methods are available on every `$item` in the views:

```php
$item->label           // Display text
$item->type            // 'url', 'route', or 'mega'
$item->url             // URL string (for url type)
$item->route           // Route name (for route type)
$item->target          // '_self' or '_blank'
$item->css_class       // Custom CSS classes
$item->icon            // Heroicon component name
$item->content         // Layup JSON (for mega type)
$item->settings        // Settings array (route_params, etc.)
$item->children        // Collection of child MenuItems
$item->parent          // Parent MenuItem (nullable)

$item->getUrl()        // Resolved URL (handles both url and route types)
$item->isOnActiveTrail()   // True if this item or any descendant matches current URL
$item->getAncestorTrail()  // Array of ancestors from root to this item
```

## Menu Settings

The `$menu->settings` array can contain:

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `sticky` | bool | `false` | Sticky nav on scroll |
| `theme` | string | `'minimal'` | Theme preset: `minimal`, `bordered`, `pill`, `underline` |
| `hover_mode` | string | `'click'` | Desktop dropdown trigger: `'click'` or `'hover'` |

Access in views: `$menu->settings['sticky'] ?? false`

## Alpine.js Component API

The `navCraft` Alpine component is available on the `<nav>` element. It provides:

```js
// State
openMenu      // Currently open desktop dropdown ID (string or null)
mobileOpen    // Mobile menu visible (boolean)
mobileExpanded // Currently expanded mobile accordion ID (string or null)

// Methods
toggle(id)        // Toggle desktop dropdown
open(id)          // Open desktop dropdown
close(id)         // Close specific dropdown
closeAll()        // Close all dropdowns and mobile menu
hoverOpen(id)     // Open on hover (respects hoverMode setting)
hoverClose(id)    // Close on hover leave (150ms delay)
focusFirst(panelId) // Focus first link/button in panel
focusTrigger(triggerId) // Return focus to trigger button
```

## Customization Patterns

### Pattern 1: Change the nav container

Edit `nav.blade.php`. The outer `<nav>` must keep `x-data="navCraft(...)"` and the ARIA attributes. Everything else is yours.

```blade
<nav
    aria-label="{{ $ariaLabel }}"
    role="navigation"
    class="your-custom-classes"
    x-data="navCraft({ hoverMode: '{{ $hoverMode }}' })"
    @click.outside="openMenu = null"
    @keydown.escape.window="openMenu ? (openMenu = null) : (mobileOpen = false)"
>
    {{-- Your layout here --}}
</nav>
```

### Pattern 2: Custom item rendering per type

In `menu-item.blade.php`, switch on `$item->type`:

```blade
@switch($item->type)
    @case('mega')
        {{-- Custom mega menu trigger --}}
        @break
    @case('route')
        {{-- Custom route link --}}
        @break
    @default
        {{-- Standard link --}}
@endswitch
```

### Pattern 3: Add a logo and actions to the nav

Edit `nav.blade.php` to add slots around the menu list:

```blade
<div class="flex items-center justify-between h-16">
    {{-- Logo --}}
    <a href="/" class="shrink-0">
        <img src="/logo.svg" alt="Home" class="h-8">
    </a>

    {{-- Desktop menu --}}
    <ul role="menubar" class="hidden lg:flex items-center gap-1">
        @foreach($items as $item)
            @include('navcraft::components.menu-item', ['item' => $item, 'depth' => 0, 'theme' => $theme])
        @endforeach
    </ul>

    {{-- Actions --}}
    <div class="hidden lg:flex items-center gap-3">
        <a href="/login" class="text-sm">Log in</a>
        <a href="/signup" class="btn-primary">Sign up</a>
    </div>

    {{-- Mobile hamburger --}}
    {{-- ... --}}
</div>
```

### Pattern 4: Custom mega menu panel

Edit `mega-panel.blade.php`. The wrapper must keep `id`, `role="region"`, `x-show`, and `x-cloak`:

```blade
<div
    id="{{ $panelId }}"
    role="region"
    aria-labelledby="{{ $parentId }}"
    x-show="openMenu === '{{ $parentId }}'"
    x-cloak
    class="your-panel-classes"
    @keydown.escape.prevent="close('{{ $parentId }}'); focusTrigger('{{ $parentId }}')"
>
    {{-- Your mega menu layout --}}
    @if(! empty($item->content['rows']))
        @layup($item->content)
    @else
        {{-- Fallback content --}}
    @endif
</div>
```

### Pattern 5: Completely custom rendering without views

Use `MenuRenderer` directly or query the models:

```php
use Crumbls\NavCraft\Models\Menu;

$menu = Menu::where('slug', 'main')->published()->first();
$items = $menu->items()->with('allDescendants')->get();

// Now render however you want
foreach ($items as $item) {
    // $item->label, $item->getUrl(), $item->children, etc.
}
```

### Pattern 6: Register a custom Alpine component

Override `scripts.blade.php` to extend or replace the `navCraft` Alpine component:

```blade
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('navCraft', (config = {}) => ({
        // Your custom implementation
        openMenu: null,
        mobileOpen: false,
        // ... add your own methods
    }));
});
</script>
```

## ADA Requirements (Do Not Remove)

When customizing views, these ARIA attributes and patterns are **required** for WCAG 2.1 AA compliance. Removing them will break accessibility:

### On the `<nav>` element:
- `aria-label="..."` -- descriptive name for the navigation
- `role="navigation"` -- landmark role

### On the desktop menu list:
- `role="menubar"` on the `<ul>`

### On each `<li>`:
- `role="none"` -- removes implicit list item role

### On links and buttons:
- `role="menuitem"` -- identifies as a menu item
- `aria-current="page"` -- on the link matching current URL

### On items with submenus:
- `aria-haspopup="true"` -- indicates a popup will appear
- `:aria-expanded="..."` -- dynamic true/false via Alpine
- `aria-controls="panel-id"` -- links button to its panel

### On dropdown panels:
- `role="menu"` and `aria-labelledby="trigger-id"` for regular submenus
- `role="region"` and `aria-label="..."` for mega menu panels
- `@keydown.escape.prevent` -- close on Escape and return focus

### On external links:
- `target="_blank"` and `rel="noopener noreferrer"`
- `<span class="sr-only">(opens in new window)</span>`

### On mobile:
- Hamburger: `aria-expanded`, `aria-controls`, `aria-label`
- Mobile menu: `role="menu"` with `aria-label`

### Focus management:
- All interactive elements must have visible focus indicators (`focus:outline-none focus:ring-2 focus:ring-*`)
- Escape closes dropdowns and returns focus to the trigger
- Arrow-down opens dropdown and focuses first item

## Testing Custom Menus

After customizing, verify:

1. **Keyboard navigation** -- Tab through all items, Enter/Space to toggle dropdowns, Escape to close
2. **Screen reader** -- VoiceOver/NVDA announces navigation landmark, menu structure, expanded state
3. **Mobile** -- Hamburger toggles, accordion expands, mega content renders
4. **Dark mode** -- All text/backgrounds have dark: variants
5. **Focus indicators** -- Visible ring on every focusable element
6. **Current page** -- `aria-current="page"` appears on the matching link
7. **External links** -- "(opens in new window)" announced by screen reader

Run automated checks:
```bash
# Lighthouse
npx lighthouse http://localhost:8000 --only-categories=accessibility

# axe-core
npx @axe-core/cli http://localhost:8000
```

## Checklist for Custom Menu Views

- [ ] `<nav>` has `aria-label` and `role="navigation"`
- [ ] Desktop `<ul>` has `role="menubar"`
- [ ] Every `<li>` has `role="none"`
- [ ] Every `<a>` and `<button>` inside has `role="menuitem"`
- [ ] Items with children have `aria-haspopup="true"` and dynamic `aria-expanded`
- [ ] Dropdown panels have `role="menu"` or `role="region"` with labeling
- [ ] Current page link has `aria-current="page"`
- [ ] External links have `target="_blank"`, `rel="noopener noreferrer"`, and sr-only text
- [ ] Hamburger has `aria-expanded`, `aria-controls`, and `aria-label`
- [ ] Escape key closes dropdowns and returns focus
- [ ] All interactive elements have visible focus indicators
- [ ] Mobile menu is fully navigable by keyboard
- [ ] Dark mode classes present on all color utilities
- [ ] `@navCraftScripts` is included on the page
- [ ] `x-data="navCraft(...)"` is on the `<nav>` element
- [ ] `x-cloak` is on all hidden panels to prevent flash
