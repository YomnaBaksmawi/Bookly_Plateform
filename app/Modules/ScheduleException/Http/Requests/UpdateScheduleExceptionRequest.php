<?php

namespace App\Modules\ScheduleException\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'in:holiday,custom_hours'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}