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
                    $heroCover = $heroMain->coverUrl();
                @endphp
                <a href="{{ locale_route('articles.show', $heroT?->slug) }}"
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
                                :aria-pressed="active === {{ $category->id }} ? 'true' : 'false'"
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
                                <a href="{{ locale_route('categories.show', $entry['translation']->slug) }}"
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

    <x-footer :root-categories="$rootCategories" />
@endsection
