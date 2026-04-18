@extends('layouts.app')

@section('title', 'Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories" />

    {{-- HERO --}}
    <section class="mx-auto mt-8 max-w-7xl px-6">
        <div class="grid gap-2 lg:grid-cols-2">
            {{-- Left: featured article --}}
            @if ($heroMain)
                @php
                    $heroT = $heroMain->translate(app()->getLocale());
                    $heroCategory = $heroMain->category?->translate(app()->getLocale());
                    $heroRoute = str_replace('_', '-', strtolower(app()->getLocale())).'.articles.show';
                    $heroCover = $heroMain->coverUrl();
                @endphp
                <a href="{{ route($heroRoute, $heroT?->slug) }}"
                   class="group relative overflow-hidden rounded-3xl aspect-[4/5] lg:aspect-auto lg:h-[600px]">
                    @if ($heroCover)
                        <img src="{{ $heroCover }}" alt="{{ $heroT?->title }}"
                             class="absolute inset-0 h-full w-full object-cover transition duration-1000 group-hover:scale-105">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-sand via-sage to-moss"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/30 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-8 text-oat sm:p-12">
                        @if ($heroCategory)
                            <span class="badge badge-accent badge-lg font-semibold uppercase tracking-widest">
                                {{ $heroCategory->name }}
                            </span>
                        @endif
                        <h1 class="mt-4 font-serif text-4xl leading-tight sm:text-5xl">
                            {{ $heroT?->title }}
                        </h1>
                        @if ($heroT?->excerpt)
                            <p class="mt-4 max-w-xl text-base text-oat/80">
                                {{ Str::limit(strip_tags($heroT->excerpt), 160) }}
                            </p>
                        @endif
                        <span class="btn btn-primary mt-6">{{ __('Read article') }}</span>
                    </div>
                </a>
            @endif

            {{-- Right: 4 stacked cards, same height as left --}}
            <div class="grid grid-rows-4 gap-2 lg:h-[600px]">
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

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($rootCategories as $category)
                    @php $t = $category->translate(app()->getLocale()); @endphp
                    @if ($t)
                        <button type="button"
                                x-on:click="active = {{ $category->id }}"
                                :class="active === {{ $category->id }} ? 'bg-cinnamon text-oat' : 'bg-base-100 text-ink hover:bg-sand'"
                                class="rounded-full px-5 py-2 text-sm font-medium transition">
                            {{ $t->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="mt-8">
                @foreach ($categoryArticles as $entry)
                    <div x-show="active === {{ $entry['category']->id }}" x-transition>
                        @if ($entry['translation'])
                            <div class="mb-5 flex items-center justify-between">
                                <h3 class="font-serif text-2xl text-ink">{{ $entry['translation']->name }}</h3>
                                <a href="{{ route(str_replace('_', '-', strtolower(app()->getLocale())).'.categories.show', $entry['translation']->slug) }}"
                                   class="text-sm font-medium text-cinnamon hover:underline">
                                    {{ __('See all') }} →
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

    {{-- FEATURED EDITORIAL --}}
    @if ($editorialMain)
        <section class="mx-auto mt-24 max-w-7xl px-6">
            <div class="grid gap-6 lg:grid-cols-3">
                @php
                    $emT = $editorialMain->translate(app()->getLocale());
                    $emCover = $editorialMain->coverUrl();
                @endphp
                <a href="{{ route(str_replace('_', '-', strtolower(app()->getLocale())).'.articles.show', $emT?->slug) }}"
                   class="group relative col-span-2 overflow-hidden rounded-3xl bg-gradient-to-br from-sage via-moss to-ink p-10 text-oat">
                    @if ($emCover)
                        <img src="{{ $emCover }}" class="absolute inset-0 h-full w-full object-cover opacity-30 transition duration-1000 group-hover:scale-105 group-hover:opacity-40" alt="">
                    @endif
                    <div class="relative">
                        <span class="badge badge-accent uppercase tracking-widest">{{ __('Editor’s pick') }}</span>
                        <h2 class="mt-4 font-serif text-4xl leading-tight sm:text-5xl">{{ $emT?->title }}</h2>
                        @if ($emT?->excerpt)
                            <p class="mt-4 max-w-xl text-oat/80">{{ Str::limit(strip_tags($emT->excerpt), 200) }}</p>
                        @endif
                    </div>
                </a>
                <div class="grid gap-4">
                    @foreach ($editorialSide as $article)
                        <x-article-card :article="$article" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ABOUT + UTILITY --}}
    <section class="mx-auto mt-24 grid max-w-7xl gap-10 px-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="font-serif text-3xl text-ink">{{ __('About the journal') }}</h2>
            <p class="mt-4 max-w-2xl text-ink/70">
                {{ __('Canela e Gengibre is a slow-food, slow-living journal.') }}
            </p>
            <a href="{{ route(str_replace('_', '-', strtolower(app()->getLocale())).'.about') }}"
               class="btn btn-outline btn-secondary mt-6">
                {{ __('Read more') }}
            </a>
        </div>
        <div class="space-y-6">
            <label class="input input-bordered flex items-center gap-2 bg-oat/80">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input type="search" placeholder="{{ __('Search recipes, ideas…') }}" class="grow bg-transparent">
            </label>
            @if ($tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        @php $tt = $tag->translate(app()->getLocale()); @endphp
                        @if ($tt)
                            <span class="badge badge-outline badge-lg">{{ $tt->name }}</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <x-newsletter />

    <x-footer :root-categories="$rootCategories" />
@endsection
