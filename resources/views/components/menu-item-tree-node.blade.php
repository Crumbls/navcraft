<li class="navcraft-tree-item" data-id="{{ $item->getKey() }}">
    <div class="group flex items-center gap-3 px-4 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5">
        {{-- Drag handle --}}
        <button type="button" data-sortable-handle class="cursor-grab text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
            <x-heroicon-m-bars-2 class="h-4 w-4" />
        </button>

        {{-- Type icon --}}
        <div class="text-gray-400 dark:text-gray-500">
            @switch($item->type)
                @case('megamenu')
                    <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                    @break
                @case('heading')
                    <x-heroicon-o-hashtag class="h-4 w-4" />
                    @break
                @case('divider')
                    <x-heroicon-o-minus class="h-4 w-4" />
                    @break
                @default
                    <x-heroicon-o-link class="h-4 w-4" />
            @endswitch
        </div>

        {{-- Label --}}
        <span class="flex-1 truncate text-sm font-medium text-gray-950 dark:text-white">
            {{ $item->label }}
        </span>

        {{-- Type badge --}}
        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
            @switch($item->type)
                @case('megamenu')
                    bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-400 dark:ring-purple-400/30
                    @break
                @case('heading')
                    bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-400 dark:ring-blue-400/30
                    @break
                @case('divider')
                    bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20
                    @break
                @default
                    bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-400/10 dark:text-green-400 dark:ring-green-400/30
            @endswitch
        ">
            {{ ucfirst($item->type ?? 'link') }}
        </span>

        {{-- Actions --}}
        <div class="flex items-center gap-1 opacity-0 transition group-hover:opacity-100">
            {{ ($this->editItemAction)(['item' => $item->getKey()]) }}
            {{ ($this->deleteItemAction)(['item' => $item->getKey()]) }}
        </div>
    </div>

    {{-- Children drop zone --}}
    <ul class="navcraft-tree-children pl-6" role="list">
        @foreach($item->children as $child)
            @include('navcraft::components.menu-item-tree-node', ['item' => $child])
        @endforeach
    </ul>
</li>
