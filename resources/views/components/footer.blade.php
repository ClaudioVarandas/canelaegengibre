@props(['rootCategories' => collect()])

<footer class="mt-auto bg-white py-12">
    <div class="mx-auto flex max-w-7xl flex-col items-center gap-6 px-6 text-sm text-ink/70 md:flex-row md:justify-between">
        <div class="flex items-center gap-3">
            <span class="grid h-8 w-8 place-items-center rounded-full bg-cinnamon text-oat font-display text-xs">CG</span>
            <span class="font-display text-lg text-ink">Canela e Gengibre</span>
        </div>

        <nav class="flex flex-wrap items-center justify-center gap-6 text-center">
            @foreach ($rootCategories as $category)
                @php $t = $category->translate(app()->getLocale()); @endphp
                @if ($t)
                    <a href="{{ locale_route_for($t->locale, 'categories.show', $t->slug) }}"
                       class="hover:text-cinnamon">{{ $t->name }}</a>
                @endif
            @endforeach
            <a href="{{ locale_route('about') }}"
               class="hover:text-cinnamon">{{ __('About') }}</a>
        </nav>

        <p class="text-xs text-ink/50">© {{ now()->year }} Canela e Gengibre</p>
    </div>
</footer>
