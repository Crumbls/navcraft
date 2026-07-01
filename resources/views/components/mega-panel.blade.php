@php
    $navPattern = $navPattern ?? 'disclosure';
    $isMenubar = $navPattern === 'menubar';
    $linkRole = $isMenubar ? 'menuitem' : null;
    $depth = $depth ?? 0;

    $content = $item->content ?? [];
    $hasLayupContent = ! empty($content['rows']);
    $children = $item->children ?? collect();

    $settings = $item->settings ?? [];
    $featured = $settings['featured'] ?? null;
    $hasFeatured = is_array($featured) && ! empty($featured['title']);

    $columnCount = min($children->count(), 4);
    $gridCols = $hasFeatured
        ? 'md:grid-cols-[2fr_1fr]'
        : match($columnCount) {
            1 => 'md:grid-cols-1',
            2 => 'md:grid-cols-2',
            3 => 'md:grid-cols-3',
            default => 'md:grid-cols-4',
        };
@endphp

<div
    id="{{ $panelId }}"
    role="region"
    aria-labelledby="{{ $parentId }}"
    aria-label="{{ $item->label }} menu"
    x-show="openMenu === '{{ $parentId }}'"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    style="z-index: {{ 50 + ($depth * 10) }}"
    class="absolute left-0 right-0 top-full mt-1 bg-[var(--nc-panel)] border border-[var(--nc-border)] shadow-xl rounded-lg"
    @mouseenter="hoverOpen('{{ $parentId }}')"
    @mouseleave="hoverClose('{{ $parentId }}')"
    @keydown.escape.prevent="close('{{ $parentId }}'); focusTrigger('{{ $parentId }}')"
>
    @if($hasLayupContent)
        <div class="p-6">
            @php
                try {
                    echo (new \Crumbls\Layup\Support\LayupContent($content))->toHtml();
                } catch (\Throwable $e) {
                    if (config('app.debug')) {
                        echo '<p class="text-sm text-[var(--nc-accent)]">Layup render error: ' . e($e->getMessage()) . '</p>';
                    }
                }
            @endphp
        </div>
    @elseif($children->isNotEmpty() || $hasFeatured)
        <div class="grid gap-8 p-6 {{ $gridCols }}">
            @if($children->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 {{ $hasFeatured ? '' : 'md:col-span-full md:grid-cols-'.max($columnCount, 1) }}">
                    @foreach($children as $child)
                        <div>
                            @if($child->children->isNotEmpty())
                                <h3 class="mb-3 pb-2 border-b border-[var(--nc-border)] text-xs font-bold uppercase tracking-widest text-[var(--nc-heading)]">
                                    {{ $child->label }}
                                </h3>
                                <ul class="space-y-1" @if($isMenubar) role="menu" @endif>
                                    @foreach($child->children as $grandchild)
                                        <li @if($isMenubar) role="none" @endif>
                                            <a
                                                href="{{ $grandchild->getUrl() }}"
                                                @if($linkRole) role="{{ $linkRole }}" @endif
                                                class="block rounded-md px-2 py-1.5 text-sm text-[var(--nc-fg)] transition hover:bg-[var(--nc-hover-bg)] hover:text-[var(--nc-fg-strong)] focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)]"
                                                @if($grandchild->getUrl() === request()->url()) aria-current="page" @endif
                                            >
                                                {{ $grandchild->label }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <a
                                    href="{{ $child->getUrl() }}"
                                    @if($linkRole) role="{{ $linkRole }}" @endif
                                    class="block rounded-md px-2 py-1.5 text-sm font-semibold text-[var(--nc-fg)] transition hover:bg-[var(--nc-hover-bg)] hover:text-[var(--nc-fg-strong)] focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)]"
                                    @if($child->getUrl() === request()->url()) aria-current="page" @endif
                                >
                                    {{ $child->label }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if($hasFeatured)
                <aside class="rounded-md bg-[var(--nc-panel-soft)] p-5" aria-label="{{ $featured['title'] }}">
                    <h3 class="text-base font-black text-[var(--nc-fg-strong)]">{{ $featured['title'] }}</h3>
                    @if(! empty($featured['body']))
                        <p class="mt-2 text-sm text-[var(--nc-fg-muted)]">{{ $featured['body'] }}</p>
                    @endif
                    @if(! empty($featured['cta_url']))
                        <a
                            href="{{ $featured['cta_url'] }}"
                            @if($linkRole) role="{{ $linkRole }}" @endif
                            class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-[var(--nc-accent)] px-4 py-2 text-sm font-bold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)]"
                        >
                            {{ $featured['cta_label'] ?? 'Learn more' }}
                            <span aria-hidden="true">&rsaquo;</span>
                        </a>
                    @endif
                </aside>
            @endif
        </div>
    @else
        <div class="p-6">
            <p class="text-sm text-[var(--nc-fg-muted)]">No content configured for this menu.</p>
        </div>
    @endif
</div>
