@props(['rootCategories' => collect()])

<header class="sticky top-0 z-40 bg-oat/70 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <a href="{{ locale_route('home') }}"
           class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-full bg-cinnamon text-oat font-serif text-xl">C</span>
            <span class="font-serif text-2xl text-ink">Canela e Gengibre</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
            @foreach ($rootCategories as $category)
                @php $t = $category->translate(app()->getLocale()); @endphp
                @if ($t)
                    <a href="{{ locale_route('categories.show', $t->slug) }}"
                       class="text-sm font-medium text-ink/80 transition hover:text-cinnamon">
                        {{ $t->name }}
                    </a>
                @endif
            @endforeach
            <a href="{{ locale_route('about') }}"
               class="text-sm font-medium text-ink/80 transition hover:text-cinnamon">
                {{ __('About') }}
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <x-locale-switcher />
            <button type="button" aria-label="Search" class="btn btn-ghost btn-circle btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </button>
        </div>
    </div>
</header>
