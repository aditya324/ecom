<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'billing_address' => $this->filled('billing_address') ? trim($this->string('billing_address')->toString()) : null,
            'billing_city' => $this->filled('billing_city') ? trim($this->string('billing_city')->toString()) : null,
            'billing_state' => $this->filled('billing_state') ? trim($this->string('billing_state')->toString()) : null,
            'billing_pin' => $this->filled('billing_pin') ? trim($this->string('billing_pin')->toString()) : null,
            'billing_gstin' => $this->filled('billing_gstin') ? strtoupper(trim($this->string('billing_gstin')->toString())) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id),
            ],
            'billing_state' => ['nullable', 'string', 'max:80', 'required_with:billing_gstin'],
            'billing_address' => ['nullable', 'string', 'max:255', 'required_with:billing_gstin'],
            'billing_city' => ['nullable', 'string', 'max:80', 'required_with:billing_gstin'],
            'billing_pin' => ['nullable', 'digits:6', 'required_with:billing_gstin'],
            'billing_gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
        ];
    }
}
