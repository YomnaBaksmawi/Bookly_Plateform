<?php

namespace App\Modules\Notification\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                'max:2000',
            ],
            'device_type' => [
                'nullable',
                'string',
                'in:web,android,ios',
            ],
        ];
    }
}