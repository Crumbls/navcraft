@php
    $content = $item->content ?? [];
    $hasLayupContent = ! empty($content['rows']);
    $children = $item->children ?? collect();
    $columnCount = min($children->count(), 4);
    $gridCols = match($columnCount) {
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-3',
        default => 'grid-cols-4',
    };
@endphp

<div
    id="{{ $panelId }}"
    role="region"
    aria-labelledby="{{ $parentId }}"
    aria-label="{{ $item->label }} menu"
    x-show="openMenu === '{{ $parentId }}'"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    x-cloak
    class="absolute left-0 right-0 top-full mt-1 bg-white shadow-xl ring-1 ring-gray-950/5 rounded-lg dark:bg-gray-900 dark:ring-white/10"
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
                        echo '<p class="text-sm text-red-500">Layup render error: ' . e($e->getMessage()) . '</p>';
                    }
                }
            @endphp
        </div>
    @elseif($children->isNotEmpty())
        <div class="grid {{ $gridCols }} gap-6 p-6">
            @foreach($children as $child)
                <div>
                    @if($child->children->isNotEmpty())
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 pb-2 border-b border-gray-100 dark:border-gray-800">
                            {{ $child->label }}
                        </h3>
                        <ul class="space-y-1" role="menu">
                            @foreach($child->children as $grandchild)
                                <li role="none">
                                    <a
                                        href="{{ $grandchild->getUrl() }}"
                                        role="menuitem"
                                        class="block py-1.5 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50 rounded"
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
                            role="menuitem"
                            class="block py-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/50 rounded"
                            @if($child->getUrl() === request()->url()) aria-current="page" @endif
                        >
                            {{ $child->label }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="p-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">No content configured for this menu.</p>
        </div>
    @endif
</div>
