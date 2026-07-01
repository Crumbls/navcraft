@php
    $hasChildren = $item->children->isNotEmpty();
    $isMega = $item->type === 'mega';
    $megaContent = $item->content ?? [];
    $hasLayupContent = ! empty($megaContent['rows']);
    $megaLinks = collect($megaContent['links'] ?? []);
    $hasSubmenu = $hasChildren || $isMega;
    $itemId = 'nc-mobile-' . $item->id;
    $isCurrent = $item->getUrl() === request()->url();
    $isOnTrail = $item->isOnActiveTrail();
    $target = $item->target ?? '_self';
    $isExternal = $target === '_blank';
    $icon = $item->icon ?? null;
    $indent = $depth * 0.75;
@endphp

<li role="none" style="{{ $depth > 0 ? "padding-left: {$indent}rem;" : '' }}">
    @if($hasSubmenu)
        <button
            type="button"
            class="flex items-center justify-between w-full gap-2 px-3 py-2.5 text-sm font-semibold rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)] {{ ($isCurrent || $isOnTrail) ? 'text-[var(--nc-accent)]' : 'text-[var(--nc-fg)]' }}"
            :aria-expanded="(mobileExpanded === '{{ $itemId }}').toString()"
            @click="mobileExpanded = mobileExpanded === '{{ $itemId }}' ? null : '{{ $itemId }}'"
        >
            <span class="flex items-center gap-1.5">
                @if($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4" aria-hidden="true" />
                @endif
                {{ $item->label }}
            </span>
            <svg aria-hidden="true" class="w-4 h-4 transition-transform" :class="mobileExpanded === '{{ $itemId }}' ? 'rotate-180' : ''" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
        </button>

        <div
            x-show="mobileExpanded === '{{ $itemId }}'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            class="mt-0.5"
            @if($isMega) role="region" aria-label="{{ $item->label }} menu" @endif
        >
            @if($isMega && $hasLayupContent)
                <div class="px-3 py-2">
                    @php
                        try {
                            echo (new \Crumbls\Layup\Support\LayupContent($megaContent))->toHtml();
                        } catch (\Throwable $e) {
                            if (config('app.debug')) {
                                echo '<p class="text-sm text-[var(--nc-accent)]">Layup render error: ' . e($e->getMessage()) . '</p>';
                            }
                        }
                    @endphp
                </div>
            @elseif($isMega && $megaLinks->isNotEmpty())
                <ul class="space-y-0.5">
                    @foreach($megaLinks as $link)
                        <li>
                            <a href="{{ $link['url'] ?? '#' }}" class="block px-3 py-2 text-sm text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)] hover:bg-[var(--nc-hover-bg)] rounded transition">
                                {{ $link['label'] ?? '' }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @elseif($hasChildren)
                <ul class="space-y-0.5">
                    @foreach($item->children as $child)
                        @include('navcraft::components.menu-item-mobile', [
                            'item' => $child,
                            'depth' => $depth + 1,
                        ])
                    @endforeach
                </ul>
            @elseif($isMega)
                <p class="px-3 py-2 text-xs text-[var(--nc-fg-muted)]">No content configured.</p>
            @endif
        </div>
    @else
        <a
            href="{{ $item->getUrl() }}"
            class="flex items-center gap-1.5 px-3 py-2.5 text-sm font-semibold rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)] {{ $isCurrent ? 'text-[var(--nc-accent)] bg-[var(--nc-hover-bg)]' : 'text-[var(--nc-fg)] hover:text-[var(--nc-fg-strong)] hover:bg-[var(--nc-hover-bg)]' }}"
            @if($isCurrent) aria-current="page" @endif
            @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
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
