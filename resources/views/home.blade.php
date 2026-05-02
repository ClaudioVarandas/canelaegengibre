@extends('layouts.app')

@section('title', 'Canela e Gengibre')

@section('structured_data')
    @php
        $siteJsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    'name' => 'Canela e Gengibre',
                    'url' => url('/'),
                    'inLanguage' => str_replace('_', '-', app()->getLocale()),
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => locale_route('search').'?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'Organization',
                    'name' => 'Canela e Gengibre',
                    'url' => url('/'),
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($siteJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    <x-header :root-categories="$rootCategories" />

    {{-- HERO --}}
    <section class="mx-auto mt-12 max-w-7xl px-6">
        <div class="grid gap-2 lg:grid-cols-[3fr_2fr]">
            {{-- Left: featured article --}}
            @php
                $heroT = $heroMain?->translate(app()->getLocale());
                $heroImg = $heroMain?->coverImageAttrs('(min-width: 1024px) 60vw, 100vw');
            @endphp
            @if ($heroMain && $heroT)
                <a href="{{ locale_route_for($heroT->locale, 'articles.show', $heroT->slug) }}"
                   class="group relative flex aspect-[4/5] flex-col justify-between overflow-hidden rounded-3xl bg-gradient-to-br from-cinnamon via-cinnamon/90 to-ink p-8 text-oat sm:p-10 lg:aspect-auto lg:h-[400px]">
                    @if ($heroImg)
                        <img src="{{ $heroImg['src'] }}"
                             @if ($heroImg['srcset']) srcset="{{ $heroImg['srcset'] }}" sizes="{{ $heroImg['sizes'] }}" @endif
                             alt="{{ $heroT->title }}"
                             fetchpriority="high"
                             decoding="async"
                             class="absolute inset-0 h-full w-full object-cover opacity-25 mix-blend-overlay transition duration-1000 group-hover:scale-105 group-hover:opacity-30">
                    @endif
                    <div class="relative">
                        <span class="inline-flex rounded-full bg-oat px-4 py-1 text-xs font-semibold uppercase tracking-widest text-ink">
                            {{ __('Featured') }}
                        </span>
                    </div>
                    <div class="relative">
                        <h1 class="font-display text-4xl leading-tight sm:text-5xl">
                            {{ $heroT->title }}
                        </h1>
                        @if ($heroT->excerpt)
                            <p class="mt-5 max-w-xl text-base text-oat/80">
                                {{ Str::limit(strip_tags($heroT->excerpt), 160) }}
                            </p>
                        @endif
                        <span class="mt-8 inline-flex rounded-full bg-oat px-7 py-3 text-sm font-medium text-ink transition group-hover:bg-white">
                            {{ __('Read article') }}
                        </span>
                    </div>
                </a>
            @endif

            {{-- Right: 4 stacked cards, same height as left --}}
            <div class="grid grid-rows-4 gap-2 lg:h-[400px]">
                @foreach ($heroRight as $article)
                    <x-article-card :article="$article" compact />
                @endforeach
            </div>
        </div>
    </section>

    {{-- CATEGORIES --}}
    @if ($rootCategories->isNotEmpty())
        <section class="mx-auto mt-12 max-w-7xl px-6"
                 x-data="{ active: {{ (int) $rootCategories->first()->id }} }">
            <h2 class="font-display text-3xl text-ink">{{ __('Explore by category') }}</h2>

            <div class="mt-6 flex flex-wrap gap-3">
                @foreach ($rootCategories as $category)
                    @php $t = $category->translate(app()->getLocale()); @endphp
                    @if ($t)
                        <button type="button"
                                x-on:click="active = {{ $category->id }}"
                                :aria-pressed="active === {{ $category->id }} ? 'true' : 'false'"
                                :class="active === {{ $category->id }} ? 'bg-cinnamon text-oat ring-cinnamon' : 'bg-white text-ink ring-ink/10 hover:ring-ink/30'"
                                class="rounded-full px-6 py-2.5 text-sm font-medium ring-1 transition">
                            {{ $t->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="mt-8 grid">
                @foreach ($categoryArticles as $entry)
                    <div :class="active === {{ $entry['category']->id }} ? 'translate-y-0 opacity-100' : 'invisible translate-y-2 opacity-0'"
                         class="col-start-1 row-start-1 rounded-3xl border border-ink/5 bg-white p-8 shadow-sm transition-all duration-500 ease-out sm:p-10">
                        @if ($entry['translation'])
                            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cinnamon">
                                        {{ __('Active category') }}
                                    </p>
                                    <h3 class="mt-2 font-display text-3xl font-semibold text-ink">
                                        {{ __('Latest articles in :category', ['category' => $entry['translation']->name]) }}
                                    </h3>
                                </div>
                                <a href="{{ locale_route_for($entry['translation']->locale, 'categories.show', $entry['translation']->slug) }}"
                                   class="text-sm font-medium text-cinnamon hover:underline">
                                    {{ __('See all in this category') }} →
                                </a>
                            </div>
                        @endif
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($entry['articles'] as $article)
                                <x-article-card :article="$article" />
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- LATEST CAROUSEL --}}
    @if ($carousel->isNotEmpty())
        <section class="mx-auto mt-12 mb-12 w-full min-w-0 max-w-7xl px-6"
                 x-data="carousel({{ $carousel->count() }})">
            <h2 class="mb-8 font-display text-3xl text-ink">{{ __('Latest') }}</h2>

            <div class="relative">
                <div x-ref="track"
                     class="flex snap-x snap-mandatory scroll-smooth gap-4 overflow-x-auto pb-4 [&::-webkit-scrollbar]:hidden [scrollbar-width:none]">
                    @foreach ($carousel as $article)
                        <div class="snap-start shrink-0 w-[78%] sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-3rem)/4)]">
                            <x-article-card :article="$article" />
                        </div>
                    @endforeach
                </div>

                <button type="button"
                        x-on:click="goto(active - 1)"
                        :disabled="active === 0"
                        :class="active === 0 ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                        class="absolute left-2 top-1/2 hidden h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink shadow-md ring-1 ring-ink/10 transition hover:bg-cinnamon hover:text-oat md:grid"
                        aria-label="{{ __('Previous') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <button type="button"
                        x-on:click="goto(active + 1)"
                        :disabled="active >= pages - 1"
                        :class="active >= pages - 1 ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                        class="absolute right-2 top-1/2 hidden h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white text-ink shadow-md ring-1 ring-ink/10 transition hover:bg-cinnamon hover:text-oat md:grid"
                        aria-label="{{ __('Next') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>

            <div class="mt-6 flex items-center justify-center gap-2">
                <template x-for="i in pages" :key="i">
                    <button type="button"
                            x-on:click="goto(i - 1)"
                            :class="active === i - 1 ? 'w-6 bg-cinnamon' : 'w-2 bg-ink/20 hover:bg-ink/40'"
                            class="h-2 rounded-full transition-all"
                            aria-label="{{ __('Go to slide') }}"></button>
                </template>
            </div>
        </section>
    @endif

    <x-footer :root-categories="$rootCategories" />
@endsection
