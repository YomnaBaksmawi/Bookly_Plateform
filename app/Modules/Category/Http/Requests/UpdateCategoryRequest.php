<?php

namespace App\Modules\Category\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => [ 'sometimes', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category), ],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}