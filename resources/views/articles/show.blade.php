@extends('layouts.app')

@section('title', $translation->title . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories" />

    <article class="mx-auto max-w-3xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10">
            @if ($article->category)
                @php $categoryT = $article->category->translate(app()->getLocale()); @endphp
                @if ($categoryT)
                    <a href="{{ route(str_replace('_', '-', strtolower(app()->getLocale())).'.categories.show', $categoryT->slug) }}"
                       class="text-xs font-semibold uppercase tracking-widest text-cinnamon hover:underline">
                        {{ $categoryT->name }}
                    </a>
                @endif
            @endif

            <h1 class="mt-3 font-serif text-5xl leading-tight text-ink">{{ $translation->title }}</h1>

            @if ($translation->excerpt)
                <p class="mt-6 text-xl text-ink/70">{{ $translation->excerpt }}</p>
            @endif

            <p class="mt-6 text-sm text-ink/50">
                @if ($article->published_at)
                    <time datetime="{{ $article->published_at->toIso8601String() }}">
                        {{ $article->published_at->translatedFormat('d \d\e F \d\e Y') }}
                    </time>
                @endif
                @if ($article->author)
                    · {{ $article->author->name }}
                @endif
            </p>
        </header>

        @if ($cover = $article->coverUrl())
            <figure class="-mx-6 mb-10 overflow-hidden sm:mx-0 sm:rounded-3xl">
                <img src="{{ $cover }}" alt="{{ $translation->title }}" class="w-full h-auto">
            </figure>
        @endif

        <div class="prose prose-lg max-w-none text-ink/90 [&_img]:rounded-2xl">
            {!! $translation->content !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <footer class="mt-12 flex flex-wrap gap-2 border-t border-ink/10 pt-8">
                @foreach ($article->tags as $tag)
                    @php $tt = $tag->translate(app()->getLocale()); @endphp
                    @if ($tt)
                        <span class="badge badge-outline">{{ $tt->name }}</span>
                    @endif
                @endforeach
            </footer>
        @endif
    </article>

    <x-footer :root-categories="$rootCategories" />
@endsection
