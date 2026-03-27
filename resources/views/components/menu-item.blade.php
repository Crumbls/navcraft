@php
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
    $theme = $theme ?? 'minimal';

    $themeClasses = match($theme) {
        'bordered' => $isTopLevel
            ? 'border border-transparent hover:border-gray-200 dark:hover:border-gray-700'
            : '',
        'pill' => $isTopLevel
            ? 'hover:bg-gray-100 dark:hover:bg-gray-800'
            : '',
        'underline' => $isTopLevel
            ? 'border-b-2 border-transparent hover:border-gray-900 dark:hover:border-white rounded-none'
            : '',
        default => '',
    };

    $activeClasses = ($isCurrent || $isOnTrail) ? match($theme) {
        'underline' => 'border-blue-600 dark:border-blue-400 text-blue-600 dark:text-blue-400',
        'pill' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'bordered' => 'border-blue-200 bg-blue-50/50 text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
        default => 'text-blue-600 dark:text-blue-400',
    } : '';
@endphp

<li role="none" class="static {{ $cssClass }}">
    @if($hasSubmenu)
        <button
            type="button"
            role="menuitem"
            id="{{ $itemId }}"
            class="flex items-center gap-1.5 whitespace-nowrap px-3 py-2 text-sm font-medium rounded-md transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ $themeClasses }} {{ $activeClasses }} {{ $isTopLevel ? 'text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white' : 'w-full text-left text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800' }}"
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
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 transition-transform" :class="openMenu === '{{ $itemId }}' ? 'rotate-180' : ''">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        @if($isMega)
            @include('navcraft::components.mega-panel', [
                'item' => $item,
                'panelId' => $panelId,
                'parentId' => $itemId,
            ])
        @else
            <ul
                role="menu"
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
                class="{{ $isTopLevel ? 'absolute left-0 top-full mt-1 w-max min-w-[12rem] max-w-[20rem]' : 'pl-4 mt-1' }} bg-white rounded-lg shadow-lg ring-1 ring-gray-950/5 py-1 dark:bg-gray-900 dark:ring-white/10"
                @mouseenter="hoverOpen('{{ $itemId }}')"
                @mouseleave="hoverClose('{{ $itemId }}')"
                @keydown.escape.prevent="close('{{ $itemId }}'); focusTrigger('{{ $itemId }}')"
            >
                @foreach($item->children as $child)
                    @include('navcraft::components.menu-item', [
                        'item' => $child,
                        'depth' => $depth + 1,
                        'theme' => $theme,
                    ])
                @endforeach
            </ul>
        @endif
    @else
        <a
            href="{{ $item->getUrl() }}"
            role="menuitem"
            class="flex items-center gap-1.5 whitespace-nowrap px-3 py-2 text-sm font-medium rounded-md transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/50 {{ $themeClasses }} {{ $activeClasses }} {{ $isTopLevel ? 'text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800' }}"
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
