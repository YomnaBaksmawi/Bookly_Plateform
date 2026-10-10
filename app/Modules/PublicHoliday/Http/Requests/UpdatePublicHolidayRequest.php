<?php

namespace App\Modules\PublicHoliday\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
            'country' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ];
    }
}