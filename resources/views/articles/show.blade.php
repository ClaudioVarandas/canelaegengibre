@extends('layouts.app')

@section('title', ($translation->meta_title ?: $translation->title) . ' · Canela e Gengibre')
@section('meta_description', $translation->meta_description ?: Str::limit(strip_tags($translation->excerpt ?? ''), 160))
@section('og_type', 'article')
@if ($cover = $article->coverUrl())
    @section('og_image', $cover)
@endif

@section('structured_data')
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $translation->title,
            'description' => $translation->meta_description ?: Str::limit(strip_tags($translation->excerpt ?? ''), 160),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
            'image' => $article->coverUrl(),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ];
        $schema = array_filter($schema, fn ($v) => $v !== null);
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
    <x-header :root-categories="$rootCategories" />

    <article class="mx-auto max-w-3xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10">
            @if ($article->category)
                @php $categoryT = $article->category->translate(app()->getLocale()); @endphp
                @if ($categoryT)
                    <a href="{{ locale_route('categories.show', $categoryT->slug) }}"
                       class="text-xs font-semibold uppercase tracking-widest text-cinnamon hover:underline">
                        {{ $categoryT->name }}
                    </a>
                @endif
            @endif

            <h1 class="mt-3 font-display text-5xl leading-tight text-ink">{{ $translation->title }}</h1>

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
            {!! str($translation->content)->sanitizeHtml() !!}
        </div>

        @if ($article->tags->isNotEmpty())
            <footer class="mt-12 flex flex-wrap gap-2 border-t border-ink/10 pt-8">
                @foreach ($article->tags as $tag)
                    @php $tt = $tag->translate(app()->getLocale()); @endphp
                    @if ($tt)
                        <a href="{{ locale_route_for($tt->locale, 'tags.show', $tt->slug) }}"
                           class="rounded-full bg-cinnamon px-3 py-1 text-xs font-medium text-oat transition hover:bg-ink">
                            #{{ $tt->name }}
                        </a>
                    @endif
                @endforeach
            </footer>
        @endif
    </article>

    <x-footer :root-categories="$rootCategories" />
@endsection
