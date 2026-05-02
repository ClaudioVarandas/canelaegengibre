@props(['article', 'compact' => false])

@php
    $t = $article->translate(app()->getLocale());
    $category = $article->category?->translate(app()->getLocale());
    $cover = $article->coverUrl();
@endphp

@if ($t)
<a href="{{ locale_route('articles.show', $t->slug) }}"
   class="group flex {{ $compact ? 'gap-4' : 'flex-col gap-3' }} rounded-2xl bg-base-100/60 p-3 transition hover:bg-base-100">
    <div class="{{ $compact ? 'h-20 w-24 shrink-0' : 'aspect-[4/3] w-full' }} overflow-hidden rounded-xl bg-sand">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $t->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @endif
    </div>
    <div class="flex-1">
        @if ($category)
            <span class="text-[11px] font-semibold uppercase tracking-widest text-cinnamon">{{ $category->name }}</span>
        @endif
        <h3 class="mt-1 {{ $compact ? 'text-base' : 'text-lg' }} font-serif leading-tight text-ink">
            {{ $t->title }}
        </h3>
        @if (! $compact && $t->excerpt)
            <p class="mt-2 line-clamp-2 text-sm text-ink/60">{{ Str::limit(strip_tags($t->excerpt), 120) }}</p>
        @endif
    </div>
</a>
@endif
