<?php

namespace App\Http\Requests\Customer;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccessPhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', Rule::in(array_keys(PhoneNumber::countryCodes()))],
            'phone' => ['required', 'string', 'max:20'],
        ];
    }
}
