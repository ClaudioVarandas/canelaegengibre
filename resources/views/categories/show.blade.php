@extends('layouts.app')

@section('title', $translation->name . ' · Canela e Gengibre')

@section('content')
    <main class="mx-auto max-w-6xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10">
            <h1 class="font-serif text-4xl text-ink">{{ $translation->name }}</h1>
            @if ($translation->description)
                <p class="mt-3 text-ink/70">{{ $translation->description }}</p>
            @endif
        </header>

        <ul class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                @php($t = $article->translations->first())
                <li class="rounded-2xl bg-base-100 p-6 shadow-sm">
                    <h2 class="font-serif text-2xl text-ink">{{ $t?->title }}</h2>
                </li>
            @endforeach
        </ul>

        <div class="mt-12">{{ $articles->links() }}</div>
    </main>
@endsection
