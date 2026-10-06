<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
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

        $this->merge([
            'slug' => $slug,
            'parent_id' => $this->filled('parent_id') ? $this->integer('parent_id') : null,
            'tagline' => $this->filled('tagline') ? $this->string('tagline')->toString() : null,
            'show_in_nav' => $this->boolean('show_in_nav'),
            'show_on_home' => $this->boolean('show_on_home'),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->filled('sort_order') ? $this->integer('sort_order') : 0,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', Rule::unique('categories', 'slug')->ignore($this->route('category'))],
            'tagline' => ['nullable', 'string', 'max:180'],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), Rule::notIn(array_filter([$this->categoryId()]))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'show_in_nav' => ['boolean'],
            'show_on_home' => ['boolean'],
            'is_active' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = $this->route('category');
                $parentId = $this->input('parent_id');

                if (! $category instanceof Category || ! $parentId || $validator->errors()->has('parent_id')) {
                    return;
                }

                $parent = Category::query()->find($parentId);

                while ($parent) {
                    if ($parent->id === $category->id) {
                        $validator->errors()->add('parent_id', 'A category cannot be placed inside itself.');

                        return;
                    }

                    $parent = $parent->parent;
                }
            },
        ];
    }

    private function categoryId(): ?int
    {
        $category = $this->route('category');

        return $category instanceof Category ? $category->id : null;
    }
}
