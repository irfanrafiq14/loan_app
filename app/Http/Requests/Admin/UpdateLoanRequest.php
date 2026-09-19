<?php

namespace App\Http\Requests\Admin;

use App\Enums\LoanStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', UserRole::Customer->value)],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_amount' => ['nullable', 'numeric', 'min:0'],
            'loan_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:loan_date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(LoanStatus::class)],
        ];
    }
}
