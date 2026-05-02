@extends('layouts.app')

@section('title', ($translation->meta_title ?: $translation->title) . ' · Canela e Gengibre')
@section('meta_description', $translation->meta_description ?: Str::limit(strip_tags($translation->excerpt ?? ''), 160))
@section('og_type', 'article')
@php
    $ogImage = $article->coverUrl('hero') ?: $article->coverUrl();
@endphp
@if ($ogImage)
    @section('og_image', $ogImage)
    @section('og_image_alt', $translation->title)
@endif

@section('structured_data')
    @php
        $categoryT = $article->category?->translate(app()->getLocale());
        $tagNames = $article->tags
            ->map(fn ($t) => $t->translate(app()->getLocale())?->name)
            ->filter()
            ->values()
            ->all();

        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
            'headline' => $translation->title,
            'description' => $translation->meta_description ?: Str::limit(strip_tags($translation->excerpt ?? ''), 160),
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at?->toIso8601String(),
            'author' => $article->author ? ['@type' => 'Person', 'name' => $article->author->name] : null,
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Canela e Gengibre',
                'url' => url('/'),
            ],
            'image' => $ogImage ? [$ogImage] : null,
            'articleSection' => $categoryT?->name,
            'keywords' => $tagNames !== [] ? implode(', ', $tagNames) : null,
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ];
        $articleSchema = array_filter($articleSchema, fn ($v) => $v !== null && $v !== '');

        $breadcrumbItems = [
            ['name' => 'Canela e Gengibre', 'url' => url('/')],
        ];
        if ($categoryT) {
            $breadcrumbItems[] = [
                'name' => $categoryT->name,
                'url' => locale_route('categories.show', $categoryT->slug),
            ];
        }
        $breadcrumbItems[] = ['name' => $translation->title, 'url' => url()->current()];

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (int $i, array $item) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], array_keys($breadcrumbItems), $breadcrumbItems),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
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

        @php $heroImg = $article->coverImageAttrs('(min-width: 768px) 768px, 100vw'); @endphp
        @if ($heroImg)
            <figure class="-mx-6 mb-10 overflow-hidden sm:mx-0 sm:rounded-3xl">
                <img src="{{ $heroImg['src'] }}"
                     @if ($heroImg['srcset']) srcset="{{ $heroImg['srcset'] }}" sizes="{{ $heroImg['sizes'] }}" @endif
                     alt="{{ $translation->title }}"
                     decoding="async"
                     class="w-full h-auto">
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
