<?php

namespace App\Modules\ProviderProfile\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }


    public function rules(): array
    {
        return [
            'business_name' => [   'required', 'string', 'max:255', ],
            'bio' => ['nullable','string', ],
            'phone' => [ 'nullable', 'string', 'max:50',],

            'timezone' => [ 'nullable', 'string', 'max:100',  ],
        ];
    }
}