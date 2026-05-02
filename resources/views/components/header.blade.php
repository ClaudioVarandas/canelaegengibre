@props(['rootCategories' => collect()])

<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <a href="{{ locale_route('home') }}"
           class="flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-full bg-cinnamon text-oat font-display text-xl">C</span>
            <span class="font-display text-2xl text-ink">Canela e Gengibre</span>
        </a>

        <nav class="hidden items-center gap-8 md:flex">
            @foreach ($rootCategories as $category)
                @php $t = $category->translate(app()->getLocale()); @endphp
                @continue (! $t)

                @if ($category->children->isNotEmpty())
                    <div x-data="{ open: false }"
                         x-on:mouseenter="open = true"
                         x-on:mouseleave="open = false"
                         x-on:click.outside="open = false"
                         class="relative">
                        <a href="{{ locale_route_for($t->locale, 'categories.show', $t->slug) }}"
                           x-on:click="open = false"
                           class="flex items-center gap-1 text-sm font-medium text-ink/80 transition hover:text-cinnamon">
                            {{ $t->name }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5" aria-hidden="true"
                                 :class="{ 'rotate-180': open }" style="transition: transform 150ms;">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </a>
                        <ul x-show="open" x-transition x-cloak
                            class="absolute left-1/2 top-full mt-2 w-56 -translate-x-1/2 rounded-2xl border border-ink/5 bg-white p-2 shadow-soft">
                            @foreach ($category->children as $child)
                                @php $ct = $child->translate(app()->getLocale()); @endphp
                                @continue (! $ct)
                                <li>
                                    <a href="{{ locale_route_for($ct->locale, 'categories.show', $ct->slug) }}"
                                       class="block rounded-xl px-3 py-2 text-sm text-ink/80 transition hover:bg-sand hover:text-cinnamon">
                                        {{ $ct->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <a href="{{ locale_route_for($t->locale, 'categories.show', $t->slug) }}"
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

            <form method="GET" action="{{ locale_route('search') }}"
                  class="hidden md:flex" role="search">
                <label class="input input-sm input-bordered flex items-center gap-2 bg-oat/80">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="search" name="q"
                           placeholder="{{ __('Search recipes, ideas…') }}"
                           aria-label="{{ __('Search') }}"
                           class="grow bg-transparent w-44">
                </label>
            </form>

            <a href="{{ locale_route('search') }}" aria-label="{{ __('Search') }}"
               class="btn btn-ghost btn-circle btn-sm md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </a>
        </div>
    </div>
</header>
