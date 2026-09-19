@props(['name' => null])

@php
    $href = \App\Support\AppBrand::faviconHref($name);
@endphp

<link rel="icon" href="{{ $href }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ $href }}">
