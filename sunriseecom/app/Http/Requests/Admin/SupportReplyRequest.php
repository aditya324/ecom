<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SupportReplyRequest extends FormRequest
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
            'reply.required' => 'Write the reply. It is emailed to the customer.',
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:2000'],
        ];
    }
}
