<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'legal_name' => trim($this->string('legal_name')->toString()),
            'gstin' => $this->filled('gstin') ? strtoupper(trim($this->string('gstin')->toString())) : null,
            'address' => $this->filled('address') ? trim($this->string('address')->toString()) : null,
            'email' => $this->filled('email') ? trim($this->string('email')->toString()) : null,
            'instagram' => $this->filled('instagram') ? trim($this->string('instagram')->toString()) : null,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'legal_name' => ['required', 'string', 'max:160'],
            'gstin' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
        ];
    }
}
