@php
    $ariaLabel = $ariaLabel ?? $menu->name;
    $settings = $menu->settings ?? [];
    $sticky = $settings['sticky'] ?? false;
    $theme = $settings['theme'] ?? 'minimal';
    $hoverMode = $settings['hover_mode'] ?? 'click';
@endphp

<nav
    aria-label="{{ $ariaLabel }}"
    role="navigation"
    class="relative bg-white border-b border-gray-200 dark:bg-gray-900 dark:border-gray-700 {{ $sticky ? 'sticky top-0 z-50 shadow-sm backdrop-blur-sm bg-white/95 dark:bg-gray-900/95' : '' }}"
    x-data="navCraft({ hoverMode: '{{ $hoverMode }}' })"
    @click.outside="openMenu = null"
    @keydown.escape.window="openMenu ? (openMenu = null) : (mobileOpen = false)"
    data-theme="{{ $theme }}"
>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
            {{-- Desktop menu --}}
            <ul role="menubar" class="hidden lg:flex items-center gap-1" aria-label="{{ $ariaLabel }}">
                @foreach($items as $item)
                    @include('navcraft::components.menu-item', [
                        'item' => $item,
                        'depth' => 0,
                        'theme' => $theme,
                    ])
                @endforeach
            </ul>

            {{-- Mobile hamburger --}}
            <button
                type="button"
                class="lg:hidden p-2 text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="nc-mobile-menu-{{ $menu->id }}"
                aria-label="Toggle navigation menu"
            >
                <svg x-show="!mobileOpen" aria-hidden="true" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                <svg x-show="mobileOpen" x-cloak aria-hidden="true" class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    {{-- Mobile slide-out menu --}}
    <div
        id="nc-mobile-menu-{{ $menu->id }}"
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 -translate-y-4"
        x-cloak
        class="lg:hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900"
        role="menu"
        aria-label="{{ $ariaLabel }} mobile"
    >
        <ul class="px-4 py-3 space-y-1">
            @foreach($items as $item)
                @include('navcraft::components.menu-item-mobile', [
                    'item' => $item,
                    'depth' => 0,
                ])
            @endforeach
        </ul>
    </div>
</nav>
