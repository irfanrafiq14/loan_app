<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserStatus;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => PhoneNumber::normalize($this->input('country_code', PhoneNumber::defaultCountryCode()), $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys(PhoneNumber::countryCodes()))],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($customer)],
            'status' => ['required', Rule::enum(UserStatus::class)],
            'available_credit' => ['nullable', 'numeric', 'min:0'],
            'credit_min' => ['nullable', 'numeric', 'min:0'],
            'credit_max' => ['nullable', 'numeric', 'min:0'],
            'eligible_offer' => ['nullable', 'numeric', 'min:0'],
            'app_name' => ['required', 'string', 'max:80'],
        ];
    }
}
