@extends('layouts.app')

@section('title', 'Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories" />

    {{-- HERO --}}
    <section class="mx-auto mt-8 max-w-7xl px-6">
        <div class="grid gap-2 lg:grid-cols-[3fr_2fr]">
            {{-- Left: featured article --}}
            @if ($heroMain)
                @php
                    $heroT = $heroMain->translate(app()->getLocale());
                    $heroCover = $heroMain->coverUrl();
                @endphp
                <a href="{{ locale_route('articles.show', $heroT?->slug) }}"
                   class="group relative flex aspect-[4/5] flex-col justify-between overflow-hidden rounded-3xl bg-gradient-to-br from-cinnamon via-cinnamon/90 to-ink p-8 text-oat sm:p-10 lg:aspect-auto lg:h-[400px]">
                    @if ($heroCover)
                        <img src="{{ $heroCover }}" alt="{{ $heroT?->title }}"
                             class="absolute inset-0 h-full w-full object-cover opacity-25 mix-blend-overlay transition duration-1000 group-hover:scale-105 group-hover:opacity-30">
                    @endif
                    <div class="relative">
                        <span class="inline-flex rounded-full bg-oat px-4 py-1 text-xs font-semibold uppercase tracking-widest text-ink">
                            {{ __('Featured') }}
                        </span>
                    </div>
                    <div class="relative">
                        <h1 class="font-serif text-4xl leading-tight sm:text-5xl">
                            {{ $heroT?->title }}
                        </h1>
                        @if ($heroT?->excerpt)
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
        <section class="mx-auto mt-24 max-w-7xl px-6"
                 x-data="{ active: {{ (int) $rootCategories->first()->id }} }">
            <h2 class="font-serif text-3xl text-ink">{{ __('Explore by category') }}</h2>

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

            <div class="mt-8">
                @foreach ($categoryArticles as $entry)
                    <div x-show="active === {{ $entry['category']->id }}" x-transition
                         class="rounded-3xl border border-ink/5 bg-white p-8 shadow-sm sm:p-10">
                        @if ($entry['translation'])
                            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cinnamon">
                                        {{ __('Active category') }}
                                    </p>
                                    <h3 class="mt-2 font-serif text-3xl font-semibold text-ink">
                                        {{ __('Latest articles in :category', ['category' => $entry['translation']->name]) }}
                                    </h3>
                                </div>
                                <a href="{{ locale_route('categories.show', $entry['translation']->slug) }}"
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
        <section class="mx-auto mt-24 max-w-7xl px-6">
            <h2 class="font-serif text-3xl text-ink">{{ __('Latest') }}</h2>
            <div class="carousel carousel-center mt-6 gap-4 rounded-box p-2">
                @foreach ($carousel as $article)
                    <div class="carousel-item w-64">
                        <x-article-card :article="$article" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <x-footer :root-categories="$rootCategories" />
@endsection
