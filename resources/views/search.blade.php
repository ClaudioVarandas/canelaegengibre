@extends('layouts.app')

@section('title', __('Search') . ' · Canela e Gengibre')
@section('robots', 'noindex, follow')

@section('content')
    <x-header :root-categories="$rootCategories" />

    <main class="mx-auto max-w-7xl px-6 py-16">
        <header class="mb-10">
            <p class="text-sm uppercase tracking-widest text-cinnamon">{{ __('Search') }}</p>
            <h1 class="mt-2 font-display text-5xl text-ink">{{ __('Search articles') }}</h1>

            <form method="GET" action="{{ locale_route('search') }}" class="mt-8 max-w-xl">
                <label class="input input-bordered flex items-center gap-3 bg-oat/80">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input type="search" name="q" value="{{ $q }}"
                           placeholder="{{ __('Search recipes, ideas…') }}"
                           aria-label="{{ __('Search articles') }}"
                           class="grow bg-transparent">
                    <button type="submit" class="btn btn-primary btn-sm">{{ __('Search') }}</button>
                </label>
            </form>
        </header>

        @if ($q === '')
            <p class="text-ink/60">{{ __('Type a query above to find articles.') }}</p>
        @elseif ($tooShort ?? false)
            <p class="text-ink/60">{{ __('Please enter at least :min characters.', ['min' => $minLength]) }}</p>
        @elseif ($results->isEmpty())
            <p class="text-ink/60">{{ __('No results for ":query".', ['query' => $q]) }}</p>
        @else
            <p class="mb-8 text-sm text-ink/60">
                {{ trans_choice(':count result for ":query"|:count results for ":query"', $results->total(), ['count' => $results->total(), 'query' => $q]) }}
            </p>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($results as $article)
                    <x-article-card :article="$article" />
                @endforeach
            </div>
            <div class="mt-10">
                {{ $results->links() }}
            </div>
        @endif
    </main>

    <x-footer :root-categories="$rootCategories" />
@endsection
