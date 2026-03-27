# Vision

## What NavCraft Is

NavCraft is a **visual menu builder for Filament 5** that gives developers and content managers full control over site navigation -- from simple link lists to rich mega menus with images, columns, and interactive content.

It pairs with [Layup](https://github.com/Crumbls/layup) to let mega menu panels contain anything Layup can build: text, images, buttons, accordions, grids, and more.

## Principles

**ADA first.** Every piece of rendered HTML meets WCAG 2.1 AA. Semantic landmarks, ARIA attributes, keyboard navigation, focus management, and screen reader support are not afterthoughts -- they are the foundation.

**Filament native.** The admin UI is a Filament form field that feels like it shipped with the framework. Actions, slide-overs, modals, and dark mode all use Filament's own component system.

**Zero frontend lock-in.** The rendered nav uses Tailwind utilities and Alpine.js. No custom CSS framework. No build step. Views are publishable and fully overridable.

**Performance by default.** Rendered menus are cached. Eager loading prevents N+1. The tree builder uses Alpine state with Livewire event bridges, not full page re-renders.

## Current State

### Admin (Tree Builder)
- Drag-and-drop nested tree with SortableJS
- Inline label editing (double-click)
- Slide-over form with tabs: Content (label, type, URL/route/mega) and Appearance (CSS, icon)
- Item types: URL, Route (with auto-detected parameters), Mega Menu (Layup builder)
- Duplicate, delete with confirmation, collapse/expand
- Search/filter, undo/redo history
- Drag depth enforcement (mega menus cannot have children)
- Empty state
- Full DB persistence (create, edit, reorder, delete all write through)

### Frontend (Renderer)
- `@navcraft('slug')` directive and `<x-navcraft-menu>` component
- `<x-navcraft-breadcrumb>` auto-generated from menu tree
- Mobile responsive: hamburger toggle, accordion submenus
- Desktop dropdowns with hover or click mode
- Mega panels: Layup content or auto-column grid layout
- Four theme presets: minimal, bordered, pill, underline
- Sticky nav option
- Active trail highlighting
- Icon rendering from Heroicon names
- Cached rendering with timestamp-based invalidation
- Full ADA compliance

## Roadmap

### Near Term
- **Bulk actions** -- checkbox selection, bulk delete, bulk move-to-parent
- **Internationalization** -- translatable labels per locale, language switcher in toolbar
- **Max depth enforcement** -- configurable nesting limit with visual indicator
- **Import/export** -- JSON import/export for menu trees
- **Menu item status** -- per-item draft/published toggle

### Medium Term
- **Scheduled visibility** -- publish_at/unpublish_at per item for time-sensitive changes
- **Keyboard shortcuts** -- Ctrl+Z undo, Delete key, Ctrl+D duplicate, arrow navigation
- **Item badges** -- visual indicators for broken routes, missing URLs, draft status
- **Menu versioning** -- named snapshots with restore capability
- **Conditional visibility** -- show/hide items based on auth state, roles, or custom rules

### Long Term
- **Link health checker** -- background job validating URL items return 200
- **Analytics integration** -- click tracking per menu item
- **A/B testing** -- variant menus with traffic splitting
- **Multi-site** -- menu sharing and overrides across tenants
- **Visual preview** -- live preview of the rendered nav inside the admin

## Non-Goals

- NavCraft is not a CMS. It builds navigation. Page content belongs in Layup or your app.
- NavCraft does not replace Laravel's router. Routes are referenced by name, not defined here.
- NavCraft does not include a CSS framework. It outputs Tailwind utilities. Bring your own design system.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). The best way to help right now:

1. Test the admin tree builder with real menu structures
2. Run accessibility audits on the rendered frontend
3. Report edge cases with mega menu content rendering
4. Propose API design for the roadmap features above
