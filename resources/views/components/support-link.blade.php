@props([
    'url' => null,
    'variant' => 'link',
])

@php $url = $url ?? ($supportGmailUrl ?? null); @endphp

@if ($url && $variant === 'button')
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" {{ $attributes->merge(['class' => 'btn-primary']) }}>
        Support
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5M15 3h6m0 0v6m0-6L10 14"/></svg>
    </a>
@elseif ($variant === 'card')
    @if ($url)
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" {{ $attributes->merge(['class' => 'mt-4 flex items-center gap-4 rounded-[28px] bg-white p-5 shadow-sm']) }}>
            {{ $slot }}
        </a>
    @else
        <section {{ $attributes->merge(['class' => 'mt-4 flex items-center gap-4 rounded-[28px] bg-white p-5 shadow-sm']) }}>
            {{ $slot }}
        </section>
    @endif
@elseif ($url)
    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" {{ $attributes->merge(['class' => 'font-bold text-brand underline']) }}>{{ $slot->isEmpty() ? 'Support' : $slot }}</a>
@endif
