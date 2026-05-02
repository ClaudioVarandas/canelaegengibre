@extends('layouts.app')

@section('title', '#' . $translation->name . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories ?? collect()" />

    <main class="mx-auto w-full min-w-0 max-w-7xl px-6 py-12 sm:py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10 text-center sm:mb-12">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-cinnamon sm:text-sm">{{ __('Tag') }}</p>
            <h1 class="mt-2 break-words font-display text-3xl text-ink sm:text-4xl lg:text-5xl">#{{ $translation->name }}</h1>
        </header>

        @if ($articles->isEmpty())
            <p class="text-center text-ink/60">{{ __('No articles tagged here yet.') }}</p>
        @else
            <ul class="grid gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3">
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
