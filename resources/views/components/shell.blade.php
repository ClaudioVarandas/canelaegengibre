@props(['rootCategories' => null])

@php $categories = $rootCategories ?? ($rootCategories ?? collect()); @endphp

<x-header :root-categories="$categories" />

{{ $slot }}

<x-footer :root-categories="$categories" />
