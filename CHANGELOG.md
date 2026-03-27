# Changelog

All notable changes to NavCraft will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

#### Admin (Tree Builder)
- Drag-and-drop nested tree builder using SortableJS
- Filament slide-over form with Content and Appearance tabs
- Item types: URL (with validation), Route (with auto-detected parameters), Mega Menu (Layup builder)
- Inline label editing via double-click
- Duplicate items with deep clone of children
- Delete with Filament confirmation modal
- Collapse/expand children with chevron toggle and child count badge
- Drag depth enforcement: mega menus cannot receive children
- Search/filter toolbar to dim non-matching items
- Undo/redo history stack (30 snapshots) with toolbar buttons
- Empty state with illustration when no items exist
- Type badges (URL/Route/Mega) with color coding
- Target/new-tab toggle for URL and Route items
- CSS class and icon fields in Appearance tab
- Full DB persistence: create, edit, reorder, rename, delete, duplicate all write through
- `wire:ignore` for Alpine state preservation across Livewire renders
- `Alpine.data('ncTreeBuilder')` registered via `@script` block (Layup pattern)
- Hidden action mount points for Filament action lifecycle

#### Frontend (Renderer)
- `@navcraft('slug')` Blade directive
- `<x-navcraft-menu slug="..." />` Blade component
- `<x-navcraft-breadcrumb slug="..." />` breadcrumb component
- `@navCraftScripts` directive (includes Layup scripts when installed)
- Mobile responsive: hamburger toggle below `lg`, accordion submenus
- Desktop dropdowns with configurable hover or click mode
- Mega menu panels: Layup content rendering or auto-column grid fallback
- Four theme presets: minimal, bordered, pill, underline
- Sticky nav option via menu settings
- Active trail highlighting through parent chain
- Icon rendering via `<x-dynamic-component>` from Heroicon names
- Cached HTML rendering (1 hour TTL, timestamp-based key)
- Full ADA/WCAG 2.1 AA compliance:
  - `<nav>` landmark with `aria-label`
  - `role="menubar"` / `role="menuitem"` / `role="none"` structure
  - `aria-haspopup`, dynamic `aria-expanded`, `aria-controls`
  - `aria-current="page"` on matching URLs
  - `role="region"` with `aria-label` on mega panels
  - Keyboard: Tab, Enter/Space toggle, Escape close, Arrow keys
  - External links: `target="_blank"`, `rel="noopener"`, sr-only text
  - Visible focus indicators on all interactive elements

#### Models
- `Menu` model with name, slug, description, status, settings (JSON)
- `MenuItem` model with type, label, url, route, target, order, css_class, icon, content (JSON), settings (JSON)
- Self-referencing `parent_id` for tree structure
- `allDescendants()` recursive eager-loading relation
- `getUrl()` resolves URL for both url and route types
- `isOnActiveTrail()` recursive active state detection
- `getAncestorTrail()` for breadcrumb generation
- Configurable table names via config
- Soft deletes on both models
- Factories with states: published, draft, megamenu, divider, heading, childOf, withContent, withItems

#### Infrastructure
- `NavCraftPlugin` for Filament panel registration
- `NavCraftServiceProvider` with views, migrations, config, Blade directives and components
- `MenuRenderer` service class with caching
- 52 Pest tests covering models, tree building, rendering, cloning, and service provider
- Pint and Rector for code quality

## [0.1.0] - 2026-03-23

### Added
- Initial development release
- Menu and MenuItem models with migrations
- Basic Filament resource for menu management
