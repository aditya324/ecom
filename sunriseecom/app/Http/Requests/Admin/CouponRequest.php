<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $this->string('code')->toString()));

        $services = collect($this->input('services', []))
            ->map(fn (mixed $id) => is_numeric($id) ? (int) $id : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->merge([
            'code' => $code,
            'type' => $this->string('type')->toString() ?: 'percent',
            'services' => $services,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Enter a coupon code.',
            'code.unique' => 'That coupon code is already used.',
            'services.required' => 'Choose at least one service.',
            'services.min' => 'Choose at least one service.',
            'amount.max' => 'A percent coupon cannot be more than 100.',
            'per_user_limit.required' => 'Enter how many times each customer can use this coupon.',
            'usage_limit.required' => 'Enter how many times this coupon can be used in total.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('coupons', 'code')->ignore($this->route('coupon')),
            ],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'amount' => [
                'required',
                'numeric',
                'min:1',
                Rule::when($this->input('type') === 'percent', ['max:100']),
                Rule::when($this->input('type') === 'fixed', ['max:99999999.99']),
            ],
            'per_user_limit' => ['required', 'integer', 'min:1', 'max:999'],
            'usage_limit' => ['required', 'integer', 'min:1', 'max:999999'],
            'services' => ['required', 'array', 'min:1'],
            'services.*' => ['integer', 'exists:services,id'],
            'is_active' => ['boolean'],
        ];
    }
}
