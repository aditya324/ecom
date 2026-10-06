<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->filled('slug')
            ? Str::slug($this->string('slug')->toString())
            : Str::slug($this->string('name')->toString());

        $points = collect($this->input('points', []))
            ->map(function (mixed $point) {
                if (! is_array($point)) {
                    return null;
                }

                return [
                    'title' => trim((string) ($point['title'] ?? '')),
                    'body' => trim((string) ($point['body'] ?? '')),
                ];
            })
            ->filter(fn (?array $point) => $point !== null && ($point['title'] !== '' || $point['body'] !== ''))
            ->values()
            ->all();

        $this->merge([
            'slug' => $slug,
            'category_id' => $this->filled('category_id') ? $this->integer('category_id') : null,
            'short_description' => $this->filled('short_description') ? $this->string('short_description')->toString() : null,
            'points' => $points,
            'description' => collect($points)->map(fn (array $point) => $point['title'].': '.$point['body'])->implode("\n"),
            'compare_price' => $this->filled('compare_price') ? $this->input('compare_price') : null,
            'delivery_label' => $this->filled('delivery_label') ? $this->string('delivery_label')->toString() : null,
            'video_url' => $this->filled('video_url') ? $this->string('video_url')->toString() : null,
            'billing_type' => $this->filled('billing_type') ? $this->string('billing_type')->toString() : 'one-time',
            'is_listed' => $this->boolean('is_listed'),
            'is_active' => $this->boolean('is_active'),
            'is_best_seller' => $this->boolean('is_best_seller'),
            'is_deal' => $this->boolean('is_deal'),
            'deal_ends_at' => $this->boolean('is_deal') && $this->filled('deal_ends_at') ? $this->input('deal_ends_at') : null,
            'sort_order' => $this->filled('sort_order') ? $this->integer('sort_order') : 0,
            'duration_prices' => collect([3, 6, 12])->mapWithKeys(function (int $months) {
                $value = data_get($this->input('duration_prices'), $months);

                return [$months => ($value === null || $value === '') ? null : $value];
            })->all(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'points.required' => 'Add at least one reason.',
            'points.min' => 'Add at least one reason.',
            'points.*.title.required' => 'Add a title for each reason.',
            'points.*.body.required' => 'Add the sentence for each reason.',
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:180', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'short_description' => ['required', 'string', 'max:500'],
            'points' => ['required', 'array', 'min:1', 'max:12'],
            'points.*.title' => ['required', 'string', 'max:80'],
            'points.*.body' => ['required', 'string', 'max:400'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_prices' => ['nullable', 'array'],
            'duration_prices.3' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_prices.6' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_prices.12' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'compare_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'billing_type' => ['required', Rule::in(['one-time', 'monthly', 'hourly'])],
            'delivery_label' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'video_url' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                if (is_string($value) && $value !== '' && Service::youtubeIdFrom($value) === null) {
                    $fail('Enter a YouTube link.');
                }
            }],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_listed' => ['boolean'],
            'is_active' => ['boolean'],
            'is_best_seller' => ['boolean'],
            'is_deal' => ['boolean'],
            'deal_ends_at' => ['nullable', 'date'],
        ];
    }
}
