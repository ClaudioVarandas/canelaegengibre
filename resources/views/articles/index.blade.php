@extends('layouts.app')

@section('title', __('Articles') . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories ?? collect()" />

    <main class="mx-auto max-w-7xl px-6 py-16">
        <h1 class="font-display text-4xl text-ink mb-8">{{ __('Articles') }}</h1>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-locale="{{ app()->getLocale() }}">
            @foreach ($articles as $article)
                <x-article-card :article="$article" />
            @endforeach
        </div>

        <div class="mt-12">{{ $articles->links() }}</div>
    </main>

    <x-footer :root-categories="$rootCategories ?? collect()" />
@endsection
