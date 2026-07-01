@if(count($breadcrumbs) > 0)
<nav aria-label="Breadcrumb" class="py-3">
    <ol class="flex items-center gap-1.5 text-sm">
        @foreach($breadcrumbs as $crumb)
            @if(! $loop->last)
                <li class="flex items-center gap-1.5">
                    <a
                        href="{{ $crumb->getUrl() }}"
                        class="text-[var(--nc-fg-muted)] hover:text-[var(--nc-fg-strong)] transition-colors focus:outline-none focus:ring-2 focus:ring-[color:var(--nc-ring)] rounded"
                    >
                        {{ $crumb->label }}
                    </a>
                    <svg aria-hidden="true" class="w-3.5 h-3.5 text-[var(--nc-fg-muted)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </li>
            @else
                <li aria-current="page" class="text-[var(--nc-fg-strong)] font-medium">
                    {{ $crumb->label }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
