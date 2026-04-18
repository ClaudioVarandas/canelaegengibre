@extends('layouts.app')

@section('title', __('About') . ' · Canela e Gengibre')

@section('content')
    <main class="mx-auto max-w-3xl px-6 py-16" data-locale="{{ app()->getLocale() }}">
        <h1 class="font-serif text-5xl text-ink">{{ __('About') }}</h1>
        <p class="mt-6 text-ink/70">{{ __('About the blog') }}</p>
    </main>
@endsection
