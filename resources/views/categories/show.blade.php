@extends('layouts.app')

@section('title', $translation->name . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories" />

    <main class="mx-auto max-w-7xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-12 text-center">
            <p class="text-sm uppercase tracking-widest text-cinnamon">{{ __('Category') }}</p>
            <h1 class="mt-2 font-serif text-5xl text-ink">{{ $translation->name }}</h1>
            @if ($translation->description)
                <p class="mt-4 mx-auto max-w-2xl text-ink/70">{{ $translation->description }}</p>
            @endif
        </header>

        <ul class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <li>
                    <x-article-card :article="$article" />
                </li>
            @endforeach
        </ul>

        <div class="mt-12">{{ $articles->links() }}</div>
    </main>

    <x-footer :root-categories="$rootCategories" />
@endsection
