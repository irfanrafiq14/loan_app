<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCustomer() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'transaction_id' => preg_replace('/\s+/', '', (string) $this->input('transaction_id')),
        ]);
    }

    public function rules(): array
    {
        return [
            'loan_id' => ['required', 'integer', Rule::exists('loans', 'id')],
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')->where('is_active', 1)],
            'transaction_id' => ['required', 'string', 'min:12', 'max:120'],
            'screenshot' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Select a payment method.',
            'transaction_id.required' => 'Enter the transaction ID.',
            'transaction_id.min' => 'The transaction ID must be at least 12 characters.',
            'screenshot.required' => 'Upload a payment screenshot.',
            'screenshot.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'screenshot.max' => 'The payment screenshot may not be larger than 5 MB.',
        ];
    }
}
