@props(['article', 'compact' => false])

@php
    $t = $article->translate(app()->getLocale());
    $category = $article->category?->translate(app()->getLocale());
    $cover = $article->coverUrl();
@endphp

@if ($t)
<a href="{{ locale_route('articles.show', $t->slug) }}"
   class="group flex {{ $compact ? 'items-center gap-4 p-4' : 'flex-col gap-4 p-4' }} rounded-2xl bg-white shadow-[0_2px_4px_rgba(31,41,55,0.04),0_14px_32px_-10px_rgba(31,41,55,0.22)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_4px_8px_rgba(31,41,55,0.06),0_28px_56px_-16px_rgba(31,41,55,0.32)]">
    <div class="{{ $compact ? 'h-16 w-16 shrink-0 rounded-xl' : 'aspect-square w-full rounded-2xl' }} overflow-hidden bg-sand">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $t->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
        @endif
    </div>
    <div class="flex-1 min-w-0">
        @if ($category)
            <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-cinnamon">{{ $category->name }}</span>
        @endif
        <h3 class="mt-1 {{ $compact ? 'text-base' : 'text-lg' }} font-display font-semibold leading-tight text-ink">
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
