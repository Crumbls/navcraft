@php
    $ariaLabel = $ariaLabel ?? $menu->name;
    $settings = $menu->settings ?? [];
    $sticky = $settings['sticky'] ?? false;
    $theme = $settings['theme'] ?? 'minimal';
    $hoverMode = $settings['hover_mode'] ?? 'click';
    $navPattern = $settings['nav_pattern'] ?? 'disclosure';
    $breakpoint = $breakpoint ?? $settings['breakpoint'] ?? config('navcraft.frontend.breakpoint', 'lg');
    $navClass = $navClass ?? '';

    $bpShow = "{$breakpoint}:flex";
    $bpHide = "{$breakpoint}:hidden";

    $menubarRole = $navPattern === 'menubar' ? 'menubar' : null;
@endphp

<nav
    aria-label="{{ $ariaLabel }}"
    role="navigation"
    class="relative bg-[var(--nc-bg)] text-[var(--nc-fg)] border-b border-[var(--nc-border)] {{ $sticky ? 'sticky top-0 z-50 shadow-sm' : '' }} {{ $navClass }}"
    x-data="navCraft({ hoverMode: '{{ $hoverMode }}', menuId: '{{ $menu->slug }}' })"
    @click.outside="openMenu = null"
    @keydown.escape.window="openMenu ? (openMenu = null) : (mobileOpen = false)"
    data-theme="{{ $theme }}"
    data-nav-pattern="{{ $navPattern }}"
>
    <div class="relative max-w-7xl mx-auto px-6 md:px-0">
        <div class="flex items-center justify-between gap-6">
            {{-- Logo slot --}}
            @if(isset($logo))
                <div class="shrink-0">
                    {{ $logo }}
                </div>
            @endif

            {{-- Desktop menu --}}
            <ul
                @if($menubarRole) role="{{ $menubarRole }}" @endif
                class="hidden {{ $bpShow }} items-center gap-1"
                aria-label="{{ $ariaLabel }}"
            >
                @foreach($items as $item)
                    @include('navcraft::components.menu-item', [
                        'item' => $item,
                        'depth' => 0,
                        'theme' => $theme,
                        'navPattern' => $navPattern,
                        'menuSlug' => $menu->slug,
                    ])
                @endforeach
            </ul>

            {{-- Search slot --}}
            @if(isset($search))
                <div class="hidden {{ $bpShow }} items-center">
                    {{ $search }}
                </div>
            @endif

            {{-- Actions slot --}}
            @if(isset($actions))
                <div class="hidden {{ $bpShow }} items-center gap-3">
                    {{ $actions }}
                </div>
            @endif

            {{-- Mobile hamburger --}}
            <button
                type="button"
                class="{{ $bpHide }} p-2 text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)] rounded-md focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)]"
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

    {{-- Mobile menu --}}
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
        class="{{ $bpHide }} border-t border-[var(--nc-border)] bg-[var(--nc-bg)]"
        aria-label="{{ $ariaLabel }} mobile"
    >
        <ul class="px-6 py-3 space-y-1">
            @foreach($items as $item)
                @include('navcraft::components.menu-item-mobile', [
                    'item' => $item,
                    'depth' => 0,
                ])
            @endforeach
        </ul>

        {{-- Mobile actions slot --}}
        @if(isset($mobileActions))
            <div class="px-6 py-3 border-t border-[var(--nc-border)]">
                {{ $mobileActions }}
            </div>
        @endif
    </div>

    {{-- Default slot for extra content --}}
    {{ $slot ?? '' }}
</nav>
