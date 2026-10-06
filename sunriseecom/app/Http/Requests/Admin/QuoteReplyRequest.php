<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class QuoteReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reply' => trim($this->string('reply')->toString()),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quoted_price.required' => 'Enter the price. It is included in the email.',
            'reply.required' => 'Write the reply. It is emailed to the customer.',
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'quoted_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'reply' => ['required', 'string', 'max:2000'],
        ];
    }
}
