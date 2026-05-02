@extends('layouts.app')

@section('title', __('About') . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories" />

    <main class="mx-auto max-w-3xl px-6 py-20" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10">
            <p class="text-sm uppercase tracking-widest text-cinnamon">{{ __('Our story') }}</p>
            <h1 class="mt-2 font-display text-5xl text-ink">{{ __('About') }}</h1>
        </header>

        <div class="prose prose-lg max-w-none text-ink/80">
            <p>{{ __('Canela e Gengibre is a slow-food, slow-living journal.') }}</p>
            <p>{{ __('We write about honest recipes, seasonal tables, and the small rituals that make a home.') }}</p>
        </div>
    </main>

    <x-footer :root-categories="$rootCategories" />
@endsection
