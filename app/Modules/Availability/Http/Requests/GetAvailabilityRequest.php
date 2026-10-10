<?php

namespace App\Modules\Availability\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}