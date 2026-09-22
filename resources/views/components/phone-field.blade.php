@props([
    'phone' => old('phone'),
])

<div class="country-picker">
    <div class="country-picker-row">
        <span class="country-picker-code">+91</span>
        <input type="hidden" name="country_code" value="91">
        <input
            type="tel"
            name="phone"
            value="{{ $phone }}"
            inputmode="numeric"
            autocomplete="tel-national"
            placeholder="98765 43210"
            class="country-picker-input"
        >
    </div>
</div>
