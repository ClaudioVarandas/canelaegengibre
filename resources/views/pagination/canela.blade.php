@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" role="navigation" aria-label="{{ __('Pagination') }}">
        @if ($paginator->onFirstPage())
            <span aria-hidden="true"
                  class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink/25 ring-1 ring-ink/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}"
               class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink ring-1 ring-ink/10 transition hover:bg-cinnamon hover:text-oat hover:ring-cinnamon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="m15 18-6-6 6-6" />
                </svg>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-sm text-ink/40" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="grid h-10 min-w-10 place-items-center rounded-full bg-cinnamon px-3 text-sm font-medium text-oat">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="grid h-10 min-w-10 place-items-center rounded-full bg-white px-3 text-sm font-medium text-ink ring-1 ring-ink/10 transition hover:bg-cinnamon hover:text-oat hover:ring-cinnamon">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}"
               class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink ring-1 ring-ink/10 transition hover:bg-cinnamon hover:text-oat hover:ring-cinnamon">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </a>
        @else
            <span aria-hidden="true"
                  class="grid h-10 w-10 place-items-center rounded-full bg-white text-ink/25 ring-1 ring-ink/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="m9 18 6-6-6-6" />
                </svg>
            </span>
        @endif
    </nav>
@endif
