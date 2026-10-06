<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'razorpay_order_id' => ['nullable', 'string', 'max:40'],
            'razorpay_payment_id' => ['nullable', 'required_with:razorpay_order_id', 'string', 'max:40'],
            'razorpay_signature' => ['nullable', 'required_with:razorpay_order_id', 'string', 'max:128'],
            'razorpay_subscription_id' => ['nullable', 'string', 'max:40'],
            'subscriptions' => ['nullable', 'array'],
            'subscriptions.*.razorpay_subscription_id' => ['required', 'string', 'max:40'],
            'subscriptions.*.razorpay_payment_id' => ['required', 'string', 'max:40'],
            'subscriptions.*.razorpay_signature' => ['required', 'string', 'max:128'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('razorpay_order_id') || $this->filled('razorpay_subscription_id') || $this->filled('subscriptions')) {
                return;
            }

            $validator->errors()->add('payment', 'Payment could not be confirmed.');
        });
    }
}
