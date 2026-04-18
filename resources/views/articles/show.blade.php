@extends('layouts.app')

@section('title', $translation->title . ' · Canela e Gengibre')

@section('content')
    <article class="mx-auto max-w-3xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10">
            <h1 class="font-serif text-5xl text-ink leading-tight">{{ $translation->title }}</h1>
            @if ($translation->excerpt)
                <p class="mt-6 text-lg text-ink/70">{{ $translation->excerpt }}</p>
            @endif
            <p class="mt-6 text-sm text-ink/50">
                {{ $article->published_at?->translatedFormat('d \d\e F \d\e Y') }}
                · {{ $article->author?->name }}
            </p>
        </header>

        <div class="prose prose-ink max-w-none">
            {!! $translation->content !!}
        </div>
    </article>
@endsection
