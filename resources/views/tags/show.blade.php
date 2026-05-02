@extends('layouts.app')

@section('title', '#' . $translation->name . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories ?? collect()" />

    <main class="mx-auto max-w-7xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-12 text-center">
            <p class="text-sm uppercase tracking-widest text-cinnamon">{{ __('Tag') }}</p>
            <h1 class="mt-2 font-display text-5xl text-ink">#{{ $translation->name }}</h1>
        </header>

        @if ($articles->isEmpty())
            <p class="text-center text-ink/60">{{ __('No articles tagged here yet.') }}</p>
        @else
            <ul class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <li>
                        <x-article-card :article="$article" />
                    </li>
                @endforeach
            </ul>

            <div class="mt-12">{{ $articles->links() }}</div>
        @endif
    </main>

    <x-footer :root-categories="$rootCategories ?? collect()" />
@endsection
