@extends('layouts.app')

@section('title', __('Articles') . ' · Canela e Gengibre')

@section('content')
    <main class="mx-auto max-w-6xl px-6 py-16">
        <h1 class="font-display text-4xl text-ink mb-8">{{ __('Articles') }}</h1>

        <ul class="grid gap-8 md:grid-cols-2 lg:grid-cols-3" data-locale="{{ app()->getLocale() }}">
            @foreach ($articles as $article)
                @php($t = $article->translations->first())
                <li class="rounded-2xl bg-base-100 p-6 shadow-sm">
                    <h2 class="font-display text-2xl text-ink">{{ $t?->title }}</h2>
                    <p class="mt-2 text-sm text-ink/70">{{ $t?->excerpt }}</p>
                </li>
            @endforeach
        </ul>

        <div class="mt-12">{{ $articles->links() }}</div>
    </main>
@endsection
