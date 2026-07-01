{{--
    NavCraft theme tokens.

    Every color in the frontend views is driven by one of these CSS custom
    properties, so a site can rebrand the entire menu by overriding a handful
    of variables -- no need to publish or fork the Blade views.

    Defaults below reproduce NavCraft's original neutral look. Override them in
    your own stylesheet at :root (or scoped to a wrapper) and, because your
    rules are unlayered, they win over these @layer defaults:

        :root {
            --nc-accent: var(--color-brand-red);
            --nc-bg: var(--color-surface);
            --nc-panel: var(--color-paper);
            --nc-border: var(--color-cinnamon);
        }
--}}
<style>
@layer navcraft {
    :root {
        --nc-fg: #374151;
        --nc-fg-strong: #111827;
        --nc-fg-muted: #6b7280;
        --nc-heading: #6b7280;
        --nc-accent: #2563eb;
        --nc-bg: #ffffff;
        --nc-panel: #ffffff;
        --nc-panel-soft: #f9fafb;
        --nc-border: #e5e7eb;
        --nc-hover-bg: #f9fafb;
        --nc-ring: rgb(59 130 246 / 0.5);
    }

    .dark {
        --nc-fg: #e5e7eb;
        --nc-fg-strong: #ffffff;
        --nc-fg-muted: #9ca3af;
        --nc-heading: #9ca3af;
        --nc-accent: #60a5fa;
        --nc-bg: #111827;
        --nc-panel: #111827;
        --nc-panel-soft: #1f2937;
        --nc-border: #374151;
        --nc-hover-bg: #1f2937;
        --nc-ring: rgb(96 165 250 / 0.5);
    }
}
</style>
