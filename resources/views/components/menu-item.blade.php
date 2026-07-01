@php
    $navPattern = $navPattern ?? 'disclosure';
    $isMenubar = $navPattern === 'menubar';

    $hasChildren = $item->children->isNotEmpty();
    $isMega = $item->type === 'mega';
    $hasSubmenu = $hasChildren || $isMega;
    $itemId = 'nc-menu-' . $item->id;
    $panelId = $itemId . '-panel';
    $isCurrent = $item->getUrl() === request()->url();
    $isOnTrail = $item->isOnActiveTrail();
    $target = $item->target ?? '_self';
    $isExternal = $target === '_blank';
    $isTopLevel = $depth === 0;
    $icon = $item->icon ?? null;
    $cssClass = $item->css_class ?? '';

    $liRole = $isMenubar ? 'none' : null;
    $itemRole = $isMenubar ? 'menuitem' : null;
    $submenuRole = $isMenubar ? 'menu' : null;

    $activeClasses = ($isCurrent || $isOnTrail) ? 'text-[var(--nc-accent)]' : '';
@endphp

<li @if($liRole) role="{{ $liRole }}" @endif class="static {{ $cssClass }}">
    @if($hasSubmenu)
        <button
            type="button"
            @if($itemRole) role="{{ $itemRole }}" @endif
            id="{{ $itemId }}"
            class="flex items-center gap-1.5 whitespace-nowrap px-3 py-2 text-sm font-semibold rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)] {{ $activeClasses }} {{ $isTopLevel ? 'text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)]' : 'w-full text-left text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)] hover:bg-[var(--nc-hover-bg)]' }}"
            :aria-expanded="(openMenu === '{{ $itemId }}').toString()"
            aria-haspopup="true"
            aria-controls="{{ $panelId }}"
            @click="toggle('{{ $itemId }}')"
            @mouseenter="hoverOpen('{{ $itemId }}')"
            @mouseleave="hoverClose('{{ $itemId }}')"
            @keydown.arrow-down.prevent="open('{{ $itemId }}'); focusFirst('{{ $panelId }}')"
            @keydown.arrow-up.prevent="close('{{ $itemId }}')"
        >
            @if($icon)
                <x-dynamic-component :component="$icon" class="w-4 h-4" aria-hidden="true" />
            @endif
            <span>{{ $item->label }}</span>
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-[var(--nc-accent)] transition-transform" :class="openMenu === '{{ $itemId }}' ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        @if($isMega)
            @include('navcraft::components.mega-panel', [
                'item' => $item,
                'panelId' => $panelId,
                'parentId' => $itemId,
                'navPattern' => $navPattern,
                'depth' => $depth,
            ])
        @else
            <ul
                @if($submenuRole) role="{{ $submenuRole }}" @endif
                id="{{ $panelId }}"
                aria-labelledby="{{ $itemId }}"
                x-show="openMenu === '{{ $itemId }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-cloak
                style="z-index: {{ 50 + ($depth * 10) }}"
                class="{{ $isTopLevel ? 'absolute left-0 top-full mt-1 w-max min-w-[12rem] max-w-[20rem]' : 'relative pl-4 mt-1' }} bg-[var(--nc-panel)] rounded-lg shadow-xl border border-[var(--nc-border)] py-1"
                @mouseenter="hoverOpen('{{ $itemId }}')"
                @mouseleave="hoverClose('{{ $itemId }}')"
                @keydown.escape.prevent="close('{{ $itemId }}'); focusTrigger('{{ $itemId }}')"
            >
                @foreach($item->children as $child)
                    @include('navcraft::components.menu-item', [
                        'item' => $child,
                        'depth' => $depth + 1,
                        'theme' => $theme ?? 'minimal',
                        'navPattern' => $navPattern,
                    ])
                @endforeach
            </ul>
        @endif
    @else
        <a
            href="{{ $item->getUrl() }}"
            @if($itemRole) role="{{ $itemRole }}" @endif
            class="flex items-center gap-1.5 whitespace-nowrap px-3 py-2 text-sm font-semibold rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)] {{ $activeClasses }} {{ $isTopLevel ? 'text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)]' : 'text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)] hover:bg-[var(--nc-hover-bg)]' }}"
            @if($isCurrent) aria-current="page" @endif
            @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
            @click="navigate('{{ $item->getUrl() }}', '{{ addslashes($item->label) }}', '{{ $itemId }}')"
        >
            @if($icon)
                <x-dynamic-component :component="$icon" class="w-4 h-4" aria-hidden="true" />
            @endif
            {{ $item->label }}
            @if($isExternal)
                <svg aria-hidden="true" class="w-3 h-3 ml-0.5 opacity-50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                <span class="sr-only">(opens in new window)</span>
            @endif
        </a>
    @endif
</li>
