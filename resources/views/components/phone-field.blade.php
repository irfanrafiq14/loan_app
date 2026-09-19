@props([
    'phone' => old('phone'),
    'country' => old('country_code', \App\Support\PhoneNumber::defaultCountryCode()),
])

@php
    $codes = \App\Support\PhoneNumber::countryCodes();
    $country = (string) $country;

    if (! array_key_exists($country, $codes)) {
        $country = \App\Support\PhoneNumber::defaultCountryCode();
    }
@endphp

<div
    class="country-picker"
    x-data="{
        open: false,
        code: @js($country),
        countries: @js($codes),
        choose(next) {
            this.code = next;
            this.open = false;
        },
    }"
    @keydown.escape.window="open = false"
>
    <div class="country-picker-row" @click.outside="open = false">
        <button type="button" class="country-picker-code" @click="open = !open" :aria-expanded="open">
            <span x-text="'+' + code"></span>
            <svg class="country-picker-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <input type="hidden" name="country_code" :value="code" value="{{ $country }}">
        <input
            type="tel"
            name="phone"
            value="{{ $phone }}"
            inputmode="numeric"
            autocomplete="tel-national"
            placeholder="995 9591151"
            class="country-picker-input"
        >
    </div>

    <div class="country-picker-menu" x-cloak x-show="open" x-transition.opacity>
        @foreach ($codes as $code => $label)
            <button
                type="button"
                class="country-picker-option"
                @click="choose(@js((string) $code))"
                :class="code === @js((string) $code) ? 'is-active' : ''"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>
