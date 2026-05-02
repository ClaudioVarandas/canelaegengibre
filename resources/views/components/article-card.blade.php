@props(['article', 'compact' => false])

@php
    $t = $article->translate(app()->getLocale());
    $category = $article->category?->translate(app()->getLocale());
    $cover = $article->coverUrl();
@endphp

@if ($t)
<a href="{{ locale_route('articles.show', $t->slug) }}"
   class="group flex {{ $compact ? 'items-center gap-4 p-4' : 'flex-col gap-4 p-4' }} rounded-2xl border border-ink/5 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
    <div class="{{ $compact ? 'h-16 w-16 shrink-0 rounded-xl' : 'aspect-square w-full rounded-2xl' }} overflow-hidden bg-sand">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $t->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @endif
    </div>
    <div class="flex-1 min-w-0">
        @if ($category)
            <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cinnamon">{{ $category->name }}</span>
        @endif
        <h3 class="mt-1 {{ $compact ? 'text-base' : 'text-lg' }} font-serif font-semibold leading-tight text-ink">
            {{ $t->title }}
        </h3>
        @if ($t->excerpt)
            <p class="mt-1 line-clamp-2 text-sm text-ink/55">
                {{ Str::limit(strip_tags($t->excerpt), $compact ? 50 : 120) }}
            </p>
        @endif
    </div>
</a>
@endif
