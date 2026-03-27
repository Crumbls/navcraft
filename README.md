# NavCraft

A visual mega menu builder for [Filament 5](https://filamentphp.com) with drag-and-drop nesting, ADA-compliant frontend rendering, and [Layup](https://github.com/Crumbls/layup)-powered mega menu panels.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- Filament 5
- [crumbls/layup](https://github.com/Crumbls/layup) ^1.0

## Installation

```bash
composer require crumbls/navcraft
```

Run the migrations:

```bash
php artisan migrate
```

Register the plugin in your Filament panel provider:

```php
use Crumbls\NavCraft\NavCraftPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            NavCraftPlugin::make(),
        ]);
}
```

Publish the config (optional):

```bash
php artisan vendor:publish --tag=navcraft-config
```

Publish the views for customization (optional):

```bash
php artisan vendor:publish --tag=navcraft-views
```

## Admin Usage

### Creating a Menu

Navigate to **Menus** in your Filament panel. Create a menu with a name, slug, and status.

### Building the Tree

On the edit page, the tree builder lets you:

- **Add items** -- click the dashed "Add item" button at the bottom
- **Edit items** -- click the pencil icon to open a slide-over with label, type, URL/route, appearance settings, and mega menu content
- **Inline rename** -- double-click any label to rename in place
- **Drag and drop** -- grab the handle to reorder or nest items under other items
- **Duplicate** -- click the copy icon to deep-clone an item and its children
- **Delete** -- click the trash icon for a confirmation dialog
- **Collapse/expand** -- click the chevron on items with children
- **Search** -- filter items by label using the search box
- **Undo/redo** -- toolbar buttons or use the history to step back

### Item Types

| Type | Description | Supports Children |
|------|-------------|:-:|
| **URL** | Absolute or relative link (`/about`, `https://example.com`) | Yes |
| **Route** | Named Laravel route with parameter support | Yes |
| **Mega Menu** | Layup page builder content panel | No |

### Slide-Over Form

The edit form is organized into two tabs:

**Content** -- label, type, URL or route (with auto-detected parameters), open-in-new-tab toggle, and Layup builder for mega menus.

**Appearance** -- CSS classes and icon (Heroicon name).

## Frontend Rendering

### Blade Directive

```blade
@navCraftScripts
@navcraft('main-navigation')
```

### Blade Component

```blade
@navCraftScripts
<x-navcraft-menu slug="main-navigation" label="Main Navigation" />
```

### Breadcrumbs

```blade
<x-navcraft-breadcrumb slug="main-navigation" />
```

Automatically generates breadcrumbs based on the current URL matched against the menu tree.

### Scripts

`@navCraftScripts` must be included once on the page (before `</body>`). It loads the Alpine.js navigation component and Layup scripts for mega menu content.

### Menu Settings

Store settings in the menu's `settings` JSON column to configure behavior:

```php
$menu->update(['settings' => [
    'sticky' => true,         // Sticky nav on scroll
    'theme' => 'pill',        // 'minimal', 'bordered', 'pill', 'underline'
    'hover_mode' => 'hover',  // 'click' or 'hover' for desktop dropdowns
]]);
```

### Theme Presets

| Theme | Description |
|-------|-------------|
| `minimal` | Clean, no decoration (default) |
| `bordered` | Border on hover and active items |
| `pill` | Background fill on hover and active |
| `underline` | Bottom border indicator |

### ADA Compliance

The rendered navigation follows WCAG 2.1 AA:

- `<nav>` landmark with `aria-label`
- `<ul role="menubar">` / `<li role="none">` / `role="menuitem"` structure
- `aria-haspopup="true"` and dynamic `aria-expanded` on parent items
- `aria-current="page"` on the link matching the current URL
- Active trail highlighting through the entire parent chain
- Keyboard navigation: Tab, Enter/Space to toggle, Escape to close, Arrow keys
- `role="region"` with `aria-label` on mega menu panels
- External links: `target="_blank"` with `rel="noopener noreferrer"` and sr-only "(opens in new window)"
- Visible focus indicators on all interactive elements
- Mobile: hamburger with `aria-expanded` and `aria-controls`, accordion submenus

### Mobile

The navigation automatically switches to a hamburger menu below the `lg` breakpoint. Submenus open as accordions. Mega menus render their full Layup content inline when expanded.

### Caching

The `MenuRenderer` caches rendered HTML for 1 hour, keyed by slug and `updated_at` timestamp. Disable caching:

```php
MenuRenderer::fromSlug('main-navigation', cached: false)->toHtml();
```

## Models

### Menu

| Column | Type | Description |
|--------|------|-------------|
| `name` | string | Display name |
| `slug` | string | Unique identifier for rendering |
| `description` | string | Optional description |
| `status` | string | `draft` or `published` |
| `settings` | json | Theme, sticky, hover mode, etc. |

### MenuItem

| Column | Type | Description |
|--------|------|-------------|
| `menu_id` | FK | Parent menu |
| `parent_id` | FK | Parent item (nullable, self-referencing) |
| `type` | string | `url`, `route`, or `mega` |
| `label` | string | Display text |
| `url` | string | URL for `url` type |
| `route` | string | Route name for `route` type |
| `target` | string | `_self` or `_blank` |
| `order` | integer | Sort position |
| `css_class` | string | Custom CSS classes |
| `icon` | string | Heroicon component name |
| `content` | json | Layup content for `mega` type |
| `settings` | json | Route params, etc. |

## Testing

```bash
cd packages/navcraft
vendor/bin/pest
```

52 tests covering models, tree building, rendering, service provider, and deep cloning.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Vision

See [VISION.md](VISION.md) for the roadmap.

## License

MIT -- see [LICENSE.md](LICENSE.md)
