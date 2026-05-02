@php
    $current = app()->getLocale();
    $locales = enabled_locales();
@endphp

@if (count($locales) > 1)
    <div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
        <button type="button" x-on:click="open = !open"
                class="btn btn-ghost btn-sm font-medium uppercase tracking-wide">
            {{ substr($current, 0, 2) }}
        </button>
        <ul x-show="open" x-transition x-cloak
            class="absolute right-0 mt-2 w-40 rounded-box border border-ink/10 bg-oat p-2 shadow-lg">
            @foreach ($locales as $locale => $meta)
                @php $name = str_replace('_', '-', strtolower($locale)); @endphp
                <li>
                    <a href="{{ route($name.'.home') }}"
                       class="block rounded px-3 py-2 text-sm hover:bg-sand {{ $locale === $current ? 'font-semibold text-cinnamon' : 'text-ink/80' }}">
                        {{ $meta['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
